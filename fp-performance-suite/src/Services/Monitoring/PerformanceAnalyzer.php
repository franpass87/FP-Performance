<?php

namespace FP\PerfSuite\Services\Monitoring;

use FP\PerfSuite\Services\Assets\Optimizer;
use FP\PerfSuite\Services\Cache\Headers;
use FP\PerfSuite\Services\Cache\PageCache;
use FP\PerfSuite\Services\DB\Cleaner;
use FP\PerfSuite\Services\Media\WebPConverter;

use function __;
use function apache_get_modules;
use function function_exists;
use function get_num_queries;
use function get_option;
use function ini_get;
use function is_array;
use function memory_get_peak_usage;
use function size_format;
use function wp_doing_ajax;
use function wp_doing_cron;

/**
 * Analizzatore di performance per identificare problemi e suggerimenti di ottimizzazione
 *
 * @author Francesco Passeri
 * @link https://francescopasseri.com
 */
class PerformanceAnalyzer
{
    private PageCache $pageCache;
    private Headers $headers;
    private Optimizer $optimizer;
    private WebPConverter $webp;
    private Cleaner $cleaner;
    private PerformanceMonitor $monitor;

    public function __construct(
        PageCache $pageCache,
        Headers $headers,
        Optimizer $optimizer,
        WebPConverter $webp,
        Cleaner $cleaner,
        PerformanceMonitor $monitor
    ) {
        $this->pageCache = $pageCache;
        $this->headers = $headers;
        $this->optimizer = $optimizer;
        $this->webp = $webp;
        $this->cleaner = $cleaner;
        $this->monitor = $monitor;
    }

    /**
     * Analizza i problemi di performance e restituisce raccomandazioni
     *
     * @return array{
     *     critical: array<int, array{issue: string, impact: string, solution: string, priority: int}>,
     *     warnings: array<int, array{issue: string, impact: string, solution: string, priority: int}>,
     *     recommendations: array<int, array{issue: string, impact: string, solution: string, priority: int}>,
     *     score: int,
     *     summary: string
     * }
     */
    public function analyze(): array
    {
        $issues = [
            'critical' => [],
            'warnings' => [],
            'recommendations' => [],
        ];

        // Analisi cache
        $this->analyzeCaching($issues);

        // Analisi asset
        $this->analyzeAssets($issues);

        // Analisi database
        $this->analyzeDatabase($issues);

        // Analisi immagini
        $this->analyzeImages($issues);

        // Analisi configurazione server
        $this->analyzeServerConfig($issues);

        // Analisi metriche storiche
        $this->analyzeHistoricalMetrics($issues);

        // Calcola score basato sui problemi
        $score = $this->calculateHealthScore($issues);

        // Genera riepilogo
        $summary = $this->generateSummary($issues, $score);

        return [
            'critical' => $issues['critical'],
            'warnings' => $issues['warnings'],
            'recommendations' => $issues['recommendations'],
            'score' => $score,
            'summary' => $summary,
        ];
    }

    private function analyzeCaching(array &$issues): void
    {
        // Page Cache
        if (!$this->pageCache->isEnabled()) {
            $issues['critical'][] = [
                'issue' => __('Cache delle pagine disabilitata', 'fp-performance-suite'),
                'impact' => __('Ogni richiesta rigenera l\'HTML completo, causando carico elevato sul server e tempi di risposta lunghi (300-1000ms vs 10-50ms con cache).', 'fp-performance-suite'),
                'solution' => __('Vai su FP Performance > Cache e attiva "Abilita page cache". Questo memorizzerà l\'HTML su disco riducendo il carico del server del 70-90%.', 'fp-performance-suite'),
                'priority' => 100,
                'action_id' => 'enable_page_cache',
            ];
        }

        // Browser Cache Headers
        $headerStatus = $this->headers->status();
        if (empty($headerStatus['enabled'])) {
            $issues['warnings'][] = [
                'issue' => __('Headers di cache del browser non configurati', 'fp-performance-suite'),
                'impact' => __('I browser ricaricano asset statici (CSS, JS, immagini) ad ogni visita, sprecando banda e rallentando il caricamento delle pagine.', 'fp-performance-suite'),
                'solution' => __('Vai su FP Performance > Cache > Browser Cache e attiva gli headers. Questo dirà ai browser di mantenere in cache gli asset per 30 giorni.', 'fp-performance-suite'),
                'priority' => 85,
                'action_id' => 'enable_browser_cache',
            ];
        }
    }

    private function analyzeAssets(array &$issues): void
    {
        $status = $this->optimizer->status();

        // Minificazione HTML
        if (empty($status['minify_html'])) {
            $issues['recommendations'][] = [
                'issue' => __('Minificazione HTML disabilitata', 'fp-performance-suite'),
                'impact' => __('L\'HTML contiene spazi bianchi e commenti non necessari, aumentando la dimensione delle pagine del 10-20%.', 'fp-performance-suite'),
                'solution' => __('Vai su FP Performance > Assets e attiva "Minify HTML". Rimuove spazi bianchi e commenti, riducendo il peso delle pagine.', 'fp-performance-suite'),
                'priority' => 60,
                'action_id' => 'enable_minify_html',
            ];
        }

        // Defer JavaScript
        if (empty($status['defer_js'])) {
            $issues['warnings'][] = [
                'issue' => __('JavaScript non differito', 'fp-performance-suite'),
                'impact' => __('Gli script bloccano il rendering della pagina, causando un ritardo visibile (200-500ms) prima che il contenuto appaia.', 'fp-performance-suite'),
                'solution' => __('Vai su FP Performance > Assets e attiva "Defer JavaScript". Gli script verranno caricati dopo il contenuto principale, migliorando il First Contentful Paint.', 'fp-performance-suite'),
                'priority' => 80,
                'action_id' => 'enable_defer_js',
            ];
        }

        // Emojis e embeds
        if (empty($status['remove_emojis'])) {
            $issues['recommendations'][] = [
                'issue' => __('Script emoji WordPress attivi', 'fp-performance-suite'),
                'impact' => __('WordPress carica script emoji non necessari (70KB), aggiungendo richieste HTTP e rallentando il caricamento di 50-100ms.', 'fp-performance-suite'),
                'solution' => __('Vai su FP Performance > Assets e attiva "Remove Emoji Scripts". Elimina script non necessari su siti moderni.', 'fp-performance-suite'),
                'priority' => 50,
                'action_id' => 'enable_remove_emojis',
            ];
        }

        // Heartbeat
        $heartbeatInterval = (int) ($status['heartbeat_admin'] ?? 15);
        if ($heartbeatInterval < 60) {
            $issues['recommendations'][] = [
                'issue' => __('Heartbeat API troppo frequente', 'fp-performance-suite'),
                'impact' => __('L\'API heartbeat di WordPress invia richieste ogni ' . $heartbeatInterval . ' secondi, consumando risorse server inutilmente.', 'fp-performance-suite'),
                'solution' => __('Vai su FP Performance > Assets e imposta l\'intervallo heartbeat su 60+ secondi. Su hosting condiviso è consigliato 120 secondi.', 'fp-performance-suite'),
                'priority' => 55,
                'action_id' => 'optimize_heartbeat',
            ];
        }

        // Critical CSS
        $criticalCss = get_option('fp_ps_critical_css', '');
        if (empty(trim($criticalCss))) {
            $issues['recommendations'][] = [
                'issue' => __('Critical CSS non configurato', 'fp-performance-suite'),
                'impact' => __('Il CSS critico above-the-fold non è inline, causando un flash di contenuto non stilizzato (FOUC) e rallentando il First Contentful Paint.', 'fp-performance-suite'),
                'solution' => __('Vai su FP Performance > Assets > Critical CSS e inserisci il CSS necessario per il rendering iniziale. Usa strumenti come Critical CSS Generator.', 'fp-performance-suite'),
                'priority' => 65,
            ];
        }
    }

    private function analyzeDatabase(array &$issues): void
    {
        $status = $this->cleaner->status();
        $overhead = $status['overhead_mb'];

        if ($overhead >= 20) {
            $issues['critical'][] = [
                'issue' => sprintf(__('Database con overhead elevato: %.2f MB', 'fp-performance-suite'), $overhead),
                'impact' => __('Le tabelle frammentate rallentano le query del 30-50%, causando tempi di caricamento più lunghi e maggior uso di memoria.', 'fp-performance-suite'),
                'solution' => __('Vai su FP Performance > Database e esegui "Optimize Tables". Considera anche di pulire revisioni vecchie, transient scaduti e spam.', 'fp-performance-suite'),
                'priority' => 90,
                'action_id' => 'optimize_database',
            ];
        } elseif ($overhead >= 5) {
            $issues['warnings'][] = [
                'issue' => sprintf(__('Database con overhead moderato: %.2f MB', 'fp-performance-suite'), $overhead),
                'impact' => __('La frammentazione delle tabelle inizia a impattare le performance. Le query potrebbero essere 10-20% più lente del necessario.', 'fp-performance-suite'),
                'solution' => __('Vai su FP Performance > Database e pianifica una pulizia. Ottimizza le tabelle e rimuovi dati non necessari.', 'fp-performance-suite'),
                'priority' => 70,
                'action_id' => 'optimize_database',
            ];
        }

        // Analizza numero di query se disponibile
        if (function_exists('get_num_queries') && !wp_doing_ajax() && !wp_doing_cron()) {
            $queries = get_num_queries();
            if ($queries > 100) {
                $issues['warnings'][] = [
                    'issue' => sprintf(__('Numero elevato di query database: %d', 'fp-performance-suite'), $queries),
                    'impact' => __('Troppe query rallentano il caricamento delle pagine. Ogni query aggiunge 1-5ms di latenza. Su hosting condiviso l\'impatto può essere ancora maggiore.', 'fp-performance-suite'),
                    'solution' => __('Analizza i plugin attivi con Query Monitor. Disabilita plugin non necessari. Considera l\'uso di un plugin di object caching come Redis Object Cache.', 'fp-performance-suite'),
                    'priority' => 75,
                ];
            }
        }
    }

    private function analyzeImages(array &$issues): void
    {
        $status = $this->webp->status();
        $coverage = $status['coverage'];

        if ($coverage < 40) {
            $issues['warnings'][] = [
                'issue' => sprintf(__('Bassa copertura WebP: %d%%', 'fp-performance-suite'), $coverage),
                'impact' => __('Le immagini JPEG/PNG pesano 25-35% più delle versioni WebP. Questo aumenta i tempi di caricamento, specialmente su mobile, e consuma più banda.', 'fp-performance-suite'),
                'solution' => __('Vai su FP Performance > Media e avvia la conversione bulk WebP. Il formato WebP riduce le dimensioni delle immagini mantenendo la qualità visiva.', 'fp-performance-suite'),
                'priority' => 80,
                'action_id' => 'enable_webp',
            ];
        } elseif ($coverage < 80) {
            $issues['recommendations'][] = [
                'issue' => sprintf(__('Copertura WebP parziale: %d%%', 'fp-performance-suite'), $coverage),
                'impact' => __('Alcune immagini non sono ancora in formato WebP, perdendo l\'opportunità di ridurre il peso delle pagine del 15-25%.', 'fp-performance-suite'),
                'solution' => __('Completa la conversione WebP delle immagini rimanenti su FP Performance > Media. Considera di abilitare la conversione automatica per i nuovi upload.', 'fp-performance-suite'),
                'priority' => 60,
                'action_id' => 'enable_webp',
            ];
        }
    }

    private function analyzeServerConfig(array &$issues): void
    {
        // Controlla compressione GZIP/Brotli
        $hasCompression = false;
        $hasEvidence = false;
        
        if (function_exists('ini_get')) {
            $zlibCompression = ini_get('zlib.output_compression');
            if ($zlibCompression && (int) $zlibCompression === 1) {
                $hasCompression = true;
                $hasEvidence = true;
            }
        }

        if (!$hasCompression && function_exists('apache_get_modules')) {
            $modules = apache_get_modules();
            if (is_array($modules)) {
                $hasEvidence = true; // Abbiamo evidenza dalla chiamata apache_get_modules
                if (in_array('mod_deflate', $modules, true) || in_array('mod_brotli', $modules, true)) {
                    $hasCompression = true;
                }
            }
        }

        // Solo se abbiamo evidenza chiara che la compressione NON è attiva, mostra l'errore
        // Se non possiamo verificare ($hasEvidence = false), non mostrare falsi positivi
        if (!$hasCompression && $hasEvidence) {
            $issues['critical'][] = [
                'issue' => __('Compressione GZIP/Brotli non rilevata', 'fp-performance-suite'),
                'impact' => __('Senza compressione, HTML/CSS/JS vengono trasferiti in dimensioni 3-5x maggiori (es. 300KB invece di 60KB), rallentando drasticamente il caricamento.', 'fp-performance-suite'),
                'solution' => __('Contatta il tuo hosting provider per abilitare mod_deflate o mod_brotli. Oppure aggiungi regole di compressione nel file .htaccess.', 'fp-performance-suite'),
                'priority' => 95,
            ];
        }

        // Controlla memoria PHP
        $memoryLimit = ini_get('memory_limit');
        if ($memoryLimit) {
            $memoryLimitBytes = $this->parseSize($memoryLimit);
            if ($memoryLimitBytes > 0 && $memoryLimitBytes < 256 * 1024 * 1024) {
                $issues['recommendations'][] = [
                    'issue' => sprintf(__('Limite memoria PHP basso: %s', 'fp-performance-suite'), $memoryLimit),
                    'impact' => __('Un limite di memoria troppo basso può causare errori "out of memory" durante operazioni intensive come conversioni immagini bulk o import di contenuti.', 'fp-performance-suite'),
                    'solution' => __('Aumenta memory_limit a 256M o più tramite wp-config.php: define(\'WP_MEMORY_LIMIT\', \'256M\'); oppure contatta l\'hosting.', 'fp-performance-suite'),
                    'priority' => 45,
                ];
            }
        }
    }

    private function analyzeHistoricalMetrics(array &$issues): void
    {
        $stats7days = $this->monitor->getStats(7);
        
        if ($stats7days['samples'] < 10) {
            // Non abbastanza dati per analisi
            return;
        }

        // Analizza tempo di caricamento
        $avgLoadTime = $stats7days['avg_load_time'];
        if ($avgLoadTime > 2.0) {
            $issues['critical'][] = [
                'issue' => sprintf(__('Tempo di caricamento molto lento: %.2f secondi', 'fp-performance-suite'), $avgLoadTime),
                'impact' => __('Tempi superiori a 2 secondi causano abbandono del 50%+ dei visitatori. Google penalizza siti lenti nei risultati di ricerca.', 'fp-performance-suite'),
                'solution' => __('Questo è un problema critico. Segui tutte le raccomandazioni sopra, in particolare: abilita page cache, ottimizza database, attiva compressione.', 'fp-performance-suite'),
                'priority' => 100,
            ];
        } elseif ($avgLoadTime > 1.0) {
            $issues['warnings'][] = [
                'issue' => sprintf(__('Tempo di caricamento lento: %.2f secondi', 'fp-performance-suite'), $avgLoadTime),
                'impact' => __('Tempi tra 1-2 secondi sono accettabili ma migliorabili. Gli utenti preferiscono siti che si caricano in meno di 1 secondo.', 'fp-performance-suite'),
                'solution' => __('Implementa le ottimizzazioni raccomandate: cache, minificazione, defer JS, WebP. Verifica anche la velocità del tuo hosting.', 'fp-performance-suite'),
                'priority' => 75,
            ];
        }

        // Analizza numero query
        $avgQueries = $stats7days['avg_queries'];
        if ($avgQueries > 50) {
            $severity = $avgQueries > 100 ? 'warnings' : 'recommendations';
            $issues[$severity][] = [
                'issue' => sprintf(__('Numero medio di query elevato: %.1f', 'fp-performance-suite'), $avgQueries),
                'impact' => __('Troppe query database rallentano le pagine e aumentano il carico sul server. Su hosting condiviso questo può causare throttling.', 'fp-performance-suite'),
                'solution' => __('Usa Query Monitor per identificare plugin problematici. Valuta l\'installazione di un plugin di object caching (Redis/Memcached).', 'fp-performance-suite'),
                'priority' => $avgQueries > 100 ? 70 : 55,
            ];
        }

        // Analizza memoria
        $avgMemory = $stats7days['avg_memory'];
        if ($avgMemory > 100) {
            $issues['recommendations'][] = [
                'issue' => sprintf(__('Uso memoria elevato: %.1f MB', 'fp-performance-suite'), $avgMemory),
                'impact' => __('Alto consumo di memoria può causare errori su hosting condiviso e rallentamenti quando si avvicina al limite.', 'fp-performance-suite'),
                'solution' => __('Disattiva plugin non necessari. Alcuni plugin (es. page builder) consumano molta memoria. Considera un hosting con più risorse.', 'fp-performance-suite'),
                'priority' => 50,
            ];
        }
    }

    private function calculateHealthScore(array $issues): int
    {
        $score = 100;
        
        foreach ($issues['critical'] as $issue) {
            $score -= 15;
        }
        
        foreach ($issues['warnings'] as $issue) {
            $score -= 8;
        }
        
        foreach ($issues['recommendations'] as $issue) {
            $score -= 3;
        }

        return max(0, min(100, $score));
    }

    private function generateSummary(array $issues, int $score): string
    {
        $critical = count($issues['critical']);
        $warnings = count($issues['warnings']);
        $recommendations = count($issues['recommendations']);

        if ($score >= 90) {
            return __('Ottimo! Il tuo sito ha una configurazione di performance eccellente. Continua a monitorare le metriche.', 'fp-performance-suite');
        }

        if ($score >= 70) {
            return sprintf(
                __('Buona configurazione, ma ci sono margini di miglioramento. Risolvi %d warning e considera %d raccomandazioni.', 'fp-performance-suite'),
                $warnings,
                $recommendations
            );
        }

        if ($score >= 50) {
            return sprintf(
                __('Configurazione base. Attenzione: %d problemi critici e %d warning richiedono intervento. Segui le soluzioni proposte.', 'fp-performance-suite'),
                $critical,
                $warnings
            );
        }

        return sprintf(
            __('URGENTE: il sito ha problemi di performance gravi. %d problemi critici devono essere risolti immediatamente per evitare impatti su SEO e conversioni.', 'fp-performance-suite'),
            $critical
        );
    }

    private function parseSize(string $size): int
    {
        $size = trim($size);
        $last = strtolower($size[strlen($size) - 1] ?? '');
        $value = (int) $size;

        switch ($last) {
            case 'g':
                $value *= 1024;
                // no break
            case 'm':
                $value *= 1024;
                // no break
            case 'k':
                $value *= 1024;
        }

        return $value;
    }
}
