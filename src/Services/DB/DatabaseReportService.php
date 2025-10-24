<?php

namespace FP\PerfSuite\Services\DB;

use FP\PerfSuite\Utils\Logger;

/**
 * Database Report Service
 * 
 * Gestisce reportistica avanzata e analisi trend:
 * - Storico ottimizzazioni
 * - Trend dimensioni database
 * - ROI ottimizzazioni
 * - Export report (JSON, CSV)
 * - Grafici e statistiche
 * 
 * @package FP\PerfSuite\Services\DB
 * @author Francesco Passeri
 */
class DatabaseReportService
{
    private const REPORT_HISTORY_KEY = 'fp_ps_db_report_history';
    private const SNAPSHOT_KEY = 'fp_ps_db_snapshots';
    private const MAX_SNAPSHOTS = 30; // Mantieni 30 snapshot (circa 1 mese se giornalieri)
    
    /**
     * Crea snapshot corrente del database
     */
    public function createSnapshot(string $label = ''): array
    {
        global $wpdb;
        
        // Ottieni dimensione database
        $sizeQuery = $wpdb->prepare(
            "SELECT 
                SUM(data_length + index_length) as total_size,
                SUM(data_free) as free_size,
                COUNT(*) as table_count
             FROM information_schema.TABLES
             WHERE table_schema = %s",
            DB_NAME
        );
        
        $sizeData = $wpdb->get_row($sizeQuery, ARRAY_A);
        
        // Conta elementi comuni
        $counts = [
            'posts' => (int) $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->posts}"),
            'revisions' => (int) $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type = 'revision'"),
            'postmeta' => (int) $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->postmeta}"),
            'comments' => (int) $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->comments}"),
            'options' => (int) $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->options}"),
            'transients' => (int) $wpdb->get_var(
                "SELECT COUNT(*) FROM {$wpdb->options} WHERE option_name LIKE '_transient_%'"
            ),
        ];
        
        $snapshot = [
            'timestamp' => time(),
            'label' => $label ?: date('Y-m-d H:i:s'),
            'size' => [
                'total_bytes' => (int) ($sizeData['total_size'] ?? 0),
                'total_mb' => round(($sizeData['total_size'] ?? 0) / 1024 / 1024, 2),
                'free_bytes' => (int) ($sizeData['free_size'] ?? 0),
                'free_mb' => round(($sizeData['free_size'] ?? 0) / 1024 / 1024, 2),
                'table_count' => (int) ($sizeData['table_count'] ?? 0),
            ],
            'counts' => $counts,
        ];
        
        // Salva snapshot
        $snapshots = get_option(self::SNAPSHOT_KEY, []);
        
        // Mantieni solo ultimi MAX_SNAPSHOTS
        if (count($snapshots) >= self::MAX_SNAPSHOTS) {
            array_shift($snapshots);
        }
        
        $snapshots[] = $snapshot;
        update_option(self::SNAPSHOT_KEY, $snapshots);
        
        Logger::info('Database snapshot created', ['label' => $label]);
        
        return $snapshot;
    }
    
    /**
     * Ottieni tutti gli snapshot
     */
    public function getSnapshots(int $limit = 0): array
    {
        $snapshots = get_option(self::SNAPSHOT_KEY, []);
        
        if ($limit > 0) {
            return array_slice($snapshots, -$limit);
        }
        
        return $snapshots;
    }
    
    /**
     * Analizza trend dimensione database
     */
    public function analyzeTrends(): array
    {
        $snapshots = $this->getSnapshots();
        
        if (count($snapshots) < 2) {
            return [
                'status' => 'insufficient_data',
                'message' => 'Servono almeno 2 snapshot per analizzare i trend',
                'snapshots_count' => count($snapshots),
            ];
        }
        
        $first = reset($snapshots);
        $last = end($snapshots);
        
        $sizeDiff = $last['size']['total_mb'] - $first['size']['total_mb'];
        $daysDiff = ($last['timestamp'] - $first['timestamp']) / DAY_IN_SECONDS;
        
        $growthPerDay = $daysDiff > 0 ? $sizeDiff / $daysDiff : 0;
        
        // Calcola trend per vari elementi
        $trends = [];
        foreach (['posts', 'revisions', 'postmeta', 'comments', 'options', 'transients'] as $key) {
            $diff = $last['counts'][$key] - $first['counts'][$key];
            $trends[$key] = [
                'first' => $first['counts'][$key],
                'last' => $last['counts'][$key],
                'difference' => $diff,
                'growth_pct' => $first['counts'][$key] > 0 
                    ? round(($diff / $first['counts'][$key]) * 100, 2) 
                    : 0,
            ];
        }
        
        return [
            'period' => [
                'from' => date('Y-m-d', $first['timestamp']),
                'to' => date('Y-m-d', $last['timestamp']),
                'days' => round($daysDiff, 1),
            ],
            'size' => [
                'first_mb' => $first['size']['total_mb'],
                'last_mb' => $last['size']['total_mb'],
                'difference_mb' => round($sizeDiff, 2),
                'growth_pct' => $first['size']['total_mb'] > 0 
                    ? round(($sizeDiff / $first['size']['total_mb']) * 100, 2) 
                    : 0,
                'growth_per_day_mb' => round($growthPerDay, 2),
            ],
            'trends' => $trends,
            'projection_30_days' => round($last['size']['total_mb'] + ($growthPerDay * 30), 2),
            'projection_90_days' => round($last['size']['total_mb'] + ($growthPerDay * 90), 2),
        ];
    }
    
    /**
     * Calcola ROI delle ottimizzazioni
     */
    public function calculateROI(): array
    {
        $history = get_option(DatabaseOptimizer::class . '_history', []);
        
        if (empty($history)) {
            return [
                'status' => 'no_data',
                'message' => 'Nessuna ottimizzazione eseguita finora',
            ];
        }
        
        $totalOperations = count($history);
        $byType = [];
        $totalSpaceSaved = 0;
        
        // Analizza operazioni
        foreach ($history as $operation) {
            $type = $operation['operation'] ?? 'unknown';
            
            if (!isset($byType[$type])) {
                $byType[$type] = [
                    'count' => 0,
                    'tables' => [],
                ];
            }
            
            $byType[$type]['count']++;
            if (isset($operation['table'])) {
                $byType[$type]['tables'][] = $operation['table'];
            }
        }
        
        // Stima risparmio dai snapshot
        $snapshots = $this->getSnapshots();
        if (count($snapshots) >= 2) {
            $beforeOptimization = null;
            $afterOptimization = null;
            
            // Cerca snapshot prima/dopo ottimizzazioni
            foreach ($snapshots as $snapshot) {
                if (strpos($snapshot['label'], 'Before') !== false) {
                    $beforeOptimization = $snapshot;
                } elseif (strpos($snapshot['label'], 'After') !== false) {
                    $afterOptimization = $snapshot;
                }
            }
            
            if ($beforeOptimization && $afterOptimization) {
                $totalSpaceSaved = $beforeOptimization['size']['total_mb'] - $afterOptimization['size']['total_mb'];
            }
        }
        
        return [
            'total_operations' => $totalOperations,
            'by_type' => $byType,
            'estimated_space_saved_mb' => round($totalSpaceSaved, 2),
            'latest_operations' => array_slice(array_reverse($history), 0, 10),
        ];
    }
    
    /**
     * Genera report completo
     */
    public function generateCompleteReport(): array
    {
        $optimizer = new DatabaseOptimizer();
        $pluginOptimizer = new PluginSpecificOptimizer();
        
        return [
            'generated_at' => time(),
            'generated_at_formatted' => date('Y-m-d H:i:s'),
            'database_analysis' => $optimizer->getCompleteAnalysis(),
            'plugin_opportunities' => $pluginOptimizer->analyzeInstalledPlugins(),
            'trends' => $this->analyzeTrends(),
            'roi' => $this->calculateROI(),
            'current_snapshot' => $this->createSnapshot('Report Snapshot'),
        ];
    }
    
    /**
     * Export report in JSON
     */
    public function exportJSON(?array $report = null): string
    {
        if ($report === null) {
            $report = $this->generateCompleteReport();
        }
        
        return wp_json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }
    
    /**
     * Export report in CSV (tabelle principali)
     */
    public function exportCSV(?array $report = null): string
    {
        if ($report === null) {
            $report = $this->generateCompleteReport();
        }
        
        $csv = [];
        
        // Header
        $csv[] = "FP Performance Suite - Database Report";
        $csv[] = "Generated: " . $report['generated_at_formatted'];
        $csv[] = "";
        
        // Dimensione database
        $csv[] = "DATABASE SIZE";
        $csv[] = "Total Size (MB)," . ($report['database_analysis']['basic']['database_size']['total_mb'] ?? 0);
        $csv[] = "Overhead (MB)," . ($report['database_analysis']['fragmentation']['total_wasted_mb'] ?? 0);
        $csv[] = "";
        
        // Tabelle frammentate
        if (!empty($report['database_analysis']['fragmentation']['fragmented_tables'])) {
            $csv[] = "FRAGMENTED TABLES";
            $csv[] = "Table,Fragmentation %,Wasted MB";
            
            foreach ($report['database_analysis']['fragmentation']['fragmented_tables'] as $table) {
                $csv[] = sprintf(
                    "%s,%s,%s",
                    $table['table'],
                    $table['fragmentation_pct'],
                    $table['wasted_mb']
                );
            }
            $csv[] = "";
        }
        
        // Plugin opportunities
        if (!empty($report['plugin_opportunities']['opportunities'])) {
            $csv[] = "PLUGIN CLEANUP OPPORTUNITIES";
            $csv[] = "Plugin,Items to Clean,Potential Savings (MB)";
            
            foreach ($report['plugin_opportunities']['opportunities'] as $plugin => $data) {
                $csv[] = sprintf(
                    "%s,%s,%s",
                    $data['plugin_name'],
                    $data['total_items'],
                    $data['potential_savings_mb']
                );
            }
            $csv[] = "";
        }
        
        // Trend
        if (isset($report['trends']['size'])) {
            $csv[] = "GROWTH TREND";
            $csv[] = "Metric,Value";
            $csv[] = "Growth per day (MB)," . $report['trends']['size']['growth_per_day_mb'];
            $csv[] = "30-day projection (MB)," . $report['trends']['projection_30_days'];
            $csv[] = "90-day projection (MB)," . $report['trends']['projection_90_days'];
        }
        
        return implode("\n", $csv);
    }
    
    /**
     * Genera report di salute database
     */
    public function getHealthScore(): array
    {
        $optimizer = new DatabaseOptimizer();
        $analysis = $optimizer->getCompleteAnalysis();
        
        $score = 100;
        $issues = [];
        
        // Penalità per frammentazione
        $fragmentationPct = $analysis['fragmentation']['total_wasted_mb'] ?? 0;
        if ($fragmentationPct > 100) {
            $score -= 20;
            $issues[] = [
                'severity' => 'high',
                'issue' => 'Frammentazione elevata (>' . round($fragmentationPct) . ' MB)',
                'penalty' => -20,
            ];
        } elseif ($fragmentationPct > 50) {
            $score -= 10;
            $issues[] = [
                'severity' => 'medium',
                'issue' => 'Frammentazione moderata (' . round($fragmentationPct) . ' MB)',
                'penalty' => -10,
            ];
        }
        
        // Penalità per tabelle MyISAM
        $myisamCount = count($analysis['storage_engines']['myisam_tables'] ?? []);
        if ($myisamCount > 0) {
            $score -= 15;
            $issues[] = [
                'severity' => 'medium',
                'issue' => $myisamCount . ' tabelle MyISAM (raccomandato InnoDB)',
                'penalty' => -15,
            ];
        }
        
        // Penalità per charset obsoleti
        $outdatedCharset = count($analysis['charset']['outdated_tables'] ?? []);
        if ($outdatedCharset > 0) {
            $score -= 10;
            $issues[] = [
                'severity' => 'low',
                'issue' => $outdatedCharset . ' tabelle con charset obsoleto',
                'penalty' => -10,
            ];
        }
        
        // Penalità per autoload eccessivo
        $autoloadMB = $analysis['autoload']['total_size_mb'] ?? 0;
        if ($autoloadMB > 2) {
            $score -= 15;
            $issues[] = [
                'severity' => 'high',
                'issue' => 'Autoload eccessivo (' . $autoloadMB . ' MB)',
                'penalty' => -15,
            ];
        } elseif ($autoloadMB > 1) {
            $score -= 5;
            $issues[] = [
                'severity' => 'medium',
                'issue' => 'Autoload elevato (' . $autoloadMB . ' MB)',
                'penalty' => -5,
            ];
        }
        
        // Penalità per indici mancanti
        $missingIndexes = $analysis['missing_indexes']['high_priority'] ?? 0;
        if ($missingIndexes > 0) {
            $score -= 10;
            $issues[] = [
                'severity' => 'medium',
                'issue' => $missingIndexes . ' indici ad alta priorità mancanti',
                'penalty' => -10,
            ];
        }
        
        // Assicura score tra 0 e 100
        $score = max(0, min(100, $score));
        
        // Determina grado
        $grade = 'F';
        if ($score >= 90) $grade = 'A';
        elseif ($score >= 80) $grade = 'B';
        elseif ($score >= 70) $grade = 'C';
        elseif ($score >= 60) $grade = 'D';
        
        return [
            'score' => $score,
            'grade' => $grade,
            'status' => $score >= 80 ? 'good' : ($score >= 60 ? 'fair' : 'poor'),
            'issues' => $issues,
            'recommendations' => $this->getHealthRecommendations($issues),
        ];
    }
    
    /**
     * Genera raccomandazioni basate su problemi di salute
     */
    private function getHealthRecommendations(array $issues): array
    {
        $recommendations = [];
        
        foreach ($issues as $issue) {
            switch ($issue['severity']) {
                case 'high':
                    $recommendations[] = '🔴 PRIORITÀ ALTA: ' . $issue['issue'];
                    break;
                case 'medium':
                    $recommendations[] = '🟡 Priorità Media: ' . $issue['issue'];
                    break;
                case 'low':
                    $recommendations[] = '🟢 Priorità Bassa: ' . $issue['issue'];
                    break;
            }
        }
        
        if (empty($recommendations)) {
            $recommendations[] = '✅ Database in ottime condizioni!';
        }
        
        return $recommendations;
    }
    
    /**
     * Programmazione report automatici
     */
    public function scheduleAutomaticReports(string $frequency = 'weekly'): void
    {
        // Rimuovi schedule esistente
        $timestamp = wp_next_scheduled('fp_ps_db_auto_report');
        if ($timestamp) {
            wp_unschedule_event($timestamp, 'fp_ps_db_auto_report');
        }
        
        // Schedule nuovo
        if ($frequency !== 'manual') {
            $interval = $frequency === 'daily' ? 'daily' : 'weekly';
            wp_schedule_event(time() + HOUR_IN_SECONDS, $interval, 'fp_ps_db_auto_report');
        }
        
        Logger::info('Automatic database reports scheduled', ['frequency' => $frequency]);
    }
    
    /**
     * Genera report automatico (chiamato da cron)
     */
    public function generateAutomaticReport(): void
    {
        $report = $this->generateCompleteReport();
        $health = $this->getHealthScore();
        
        // Salva report
        $reports = get_option(self::REPORT_HISTORY_KEY, []);
        
        // Mantieni solo ultimi 10 report
        if (count($reports) >= 10) {
            array_shift($reports);
        }
        
        $reports[] = [
            'timestamp' => time(),
            'health_score' => $health['score'],
            'health_grade' => $health['grade'],
            'db_size_mb' => $report['current_snapshot']['size']['total_mb'],
            'issues_count' => count($health['issues']),
        ];
        
        update_option(self::REPORT_HISTORY_KEY, $reports);
        
        Logger::info('Automatic database report generated', [
            'score' => $health['score'],
            'grade' => $health['grade'],
        ]);
        
        // Invia email se ci sono problemi critici
        if ($health['score'] < 60) {
            $this->sendHealthAlert($health);
        }
    }
    
    /**
     * Invia alert via email per problemi critici
     */
    private function sendHealthAlert(array $health): void
    {
        $adminEmail = get_option('admin_email');
        $siteName = get_bloginfo('name');
        
        $subject = sprintf('[%s] Database Health Alert - Score: %d%%', $siteName, $health['score']);
        
        $message = "Il database del tuo sito WordPress necessita attenzione.\n\n";
        $message .= "Punteggio Salute: {$health['score']}% (Voto: {$health['grade']})\n\n";
        $message .= "Problemi rilevati:\n";
        
        foreach ($health['issues'] as $issue) {
            $message .= "- {$issue['issue']}\n";
        }
        
        $message .= "\nAccedi alla dashboard FP Performance Suite per ottimizzare il database.";
        
        wp_mail($adminEmail, $subject, $message);
        
        Logger::info('Health alert email sent', ['score' => $health['score']]);
    }
    
    /**
     * Registra il servizio
     */
    public function register(): void
    {
        // DatabaseReportService non ha hook specifici da registrare
        // È utilizzato principalmente per reportistica e analisi on-demand
    }
}

