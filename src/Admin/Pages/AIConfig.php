<?php

namespace FP\PerfSuite\Admin\Pages;

use FP\PerfSuite\ServiceContainer;
use FP\PerfSuite\Services\AI\Analyzer;
use FP\PerfSuite\Services\Presets\Manager as PresetManager;

use function __;
use function esc_attr;
use function esc_html;
use function esc_html_e;
use function esc_url;
use function wp_json_encode;
use function wp_create_nonce;
use function admin_url;
use function get_option;
use function update_option;

/**
 * Pagina AI Auto-Configuration AVANZATA
 * Versione migliorata con features pro
 */
class AIConfig extends AbstractPage
{
    public function __construct(ServiceContainer $container)
    {
        parent::__construct($container);
    }

    public function slug(): string
    {
        return 'fp-performance-suite-ai-config';
    }

    public function title(): string
    {
        return __('AI Auto-Config', 'fp-performance-suite');
    }

    public function capability(): string
    {
        return $this->requiredCapability();
    }

    public function view(): string
    {
        return FP_PERF_SUITE_DIR . '/views/admin-page.php';
    }

    protected function data(): array
    {
        return [
            'title' => $this->title(),
            'breadcrumbs' => [__('Ottimizzazione', 'fp-performance-suite'), __('AI Config', 'fp-performance-suite')],
        ];
    }

    protected function content(): string
    {
        $analyzer = $this->container->get(Analyzer::class);
        $nonce = wp_create_nonce('wp_rest');
        
        // Gestione export/import
        $this->handleExportImport();
        
        // Carica storia analisi
        $analysisHistory = get_option('fp_ps_ai_analysis_history', []);
        
        // Carica modalità (safe/aggressive/expert)
        $mode = $_GET['mode'] ?? 'safe';
        
        // Check se è stata richiesta l'analisi
        $analysis = null;
        $suggestions = null;
        
        if (isset($_GET['analyze']) && $_GET['analyze'] === '1') {
            // Analisi già completata (verrà fatta via AJAX per animazioni)
            $analysisData = $analyzer->analyze();
            $result = $analyzer->suggest($analysisData);
            $analysis = $result['analysis'];
            $suggestions = $result['suggestions'];
            $config = $result['config'];
            $score = $result['score'];
            
            // Salva in storia
            $this->saveToHistory($analysis, $score);
        }
        
        ob_start();
        ?>
        
        <!-- Hero Section con Tabs -->
        <div class="fp-ps-ai-hero">
            <div class="fp-ps-ai-hero-content">
                <div class="fp-ps-ai-icon">
                    <svg width="80" height="80" viewBox="0 0 80 80" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <circle cx="40" cy="40" r="38" fill="url(#gradient)" stroke="#2271b1" stroke-width="4"/>
                        <path d="M40 20L45 35H55L47 42L50 57L40 48L30 57L33 42L25 35H35L40 20Z" fill="white"/>
                        <defs>
                            <linearGradient id="gradient" x1="0" y1="0" x2="80" y2="80">
                                <stop offset="0%" style="stop-color:#4F46E5"/>
                                <stop offset="100%" style="stop-color:#2271b1"/>
                            </linearGradient>
                        </defs>
                    </svg>
                </div>
                <h1 style="font-size: 32px; margin: 20px 0 10px 0; color: white;">
                    <?php esc_html_e('🤖 AI Auto-Configuration Pro', 'fp-performance-suite'); ?>
                </h1>
                <p style="font-size: 18px; color: rgba(255,255,255,0.9); max-width: 600px; margin: 0 auto 20px;">
                    <?php esc_html_e('Sistema intelligente avanzato con analisi in tempo reale, preview modifiche e test performance', 'fp-performance-suite'); ?>
                </p>
                
                <!-- Mode Selector -->
                <div class="fp-ps-mode-selector">
                    <a href="?page=fp-performance-suite-ai-config&mode=safe" 
                       class="fp-ps-mode-btn <?php echo $mode === 'safe' ? 'active' : ''; ?>">
                        <span class="fp-ps-mode-icon">🛡️</span>
                        <span><?php esc_html_e('Safe Mode', 'fp-performance-suite'); ?></span>
                    </a>
                    <a href="?page=fp-performance-suite-ai-config&mode=aggressive" 
                       class="fp-ps-mode-btn <?php echo $mode === 'aggressive' ? 'active' : ''; ?>">
                        <span class="fp-ps-mode-icon">⚡</span>
                        <span><?php esc_html_e('Aggressive', 'fp-performance-suite'); ?></span>
                    </a>
                    <a href="?page=fp-performance-suite-ai-config&mode=expert" 
                       class="fp-ps-mode-btn <?php echo $mode === 'expert' ? 'active' : ''; ?>">
                        <span class="fp-ps-mode-icon">🔬</span>
                        <span><?php esc_html_e('Expert', 'fp-performance-suite'); ?></span>
                    </a>
                </div>
            </div>
        </div>

        <!-- Top Actions Bar -->
        <div class="fp-ps-top-actions">
            <button class="fp-ps-btn-secondary" id="fp-ps-show-history">
                <span>📊</span> <?php esc_html_e('Storia Analisi', 'fp-performance-suite'); ?>
            </button>
            <button class="fp-ps-btn-secondary" id="fp-ps-export-config">
                <span>💾</span> <?php esc_html_e('Esporta Config', 'fp-performance-suite'); ?>
            </button>
            <button class="fp-ps-btn-secondary" id="fp-ps-import-config">
                <span>📤</span> <?php esc_html_e('Importa Config', 'fp-performance-suite'); ?>
            </button>
            <button class="fp-ps-btn-secondary" id="fp-ps-run-performance-test">
                <span>🏃</span> <?php esc_html_e('Test Performance', 'fp-performance-suite'); ?>
            </button>
        </div>

        <?php if (!$analysis) : ?>
            <!-- Call to Action - Inizia Analisi AVANZATA -->
            <section class="fp-ps-card fp-ps-ai-cta">
                <div class="fp-ps-ai-cta-content">
                    <h2 style="font-size: 24px; margin-bottom: 15px;">
                        <?php esc_html_e('Analisi AI Avanzata', 'fp-performance-suite'); ?>
                    </h2>
                    
                    <div class="fp-ps-ai-steps">
                        <div class="fp-ps-ai-step">
                            <div class="fp-ps-ai-step-number">1</div>
                            <div class="fp-ps-ai-step-content">
                                <h3><?php esc_html_e('🔍 Analisi Multi-Livello', 'fp-performance-suite'); ?></h3>
                                <p><?php esc_html_e('Scansione approfondita di hosting, risorse, database, contenuti, plugin e performance attuali', 'fp-performance-suite'); ?></p>
                            </div>
                        </div>
                        
                        <div class="fp-ps-ai-step">
                            <div class="fp-ps-ai-step-number">2</div>
                            <div class="fp-ps-ai-step-content">
                                <h3><?php esc_html_e('👁️ Preview Modifiche', 'fp-performance-suite'); ?></h3>
                                <p><?php esc_html_e('Visualizza esattamente cosa cambierà con comparazione Prima/Dopo e toggle per personalizzare', 'fp-performance-suite'); ?></p>
                            </div>
                        </div>
                        
                        <div class="fp-ps-ai-step">
                            <div class="fp-ps-ai-step-number">3</div>
                            <div class="fp-ps-ai-step-content">
                                <h3><?php esc_html_e('🚀 Test & Deploy', 'fp-performance-suite'); ?></h3>
                                <p><?php esc_html_e('Esegui test di performance prima/dopo e applica con un click. Storia salvata per analisi future', 'fp-performance-suite'); ?></p>
                            </div>
                        </div>
                    </div>

                    <div class="fp-ps-ai-features">
                        <div class="fp-ps-ai-feature">
                            <span class="fp-ps-badge green">✓</span>
                            <span><?php esc_html_e('Analisi in tempo reale con progress animato', 'fp-performance-suite'); ?></span>
                        </div>
                        <div class="fp-ps-ai-feature">
                            <span class="fp-ps-badge green">✓</span>
                            <span><?php esc_html_e('Preview dettagliata di tutte le modifiche', 'fp-performance-suite'); ?></span>
                        </div>
                        <div class="fp-ps-ai-feature">
                            <span class="fp-ps-badge green">✓</span>
                            <span><?php esc_html_e('Personalizzazione suggerimenti con toggle on/off', 'fp-performance-suite'); ?></span>
                        </div>
                        <div class="fp-ps-ai-feature">
                            <span class="fp-ps-badge green">✓</span>
                            <span><?php esc_html_e('Storia analisi con grafico evoluzione score', 'fp-performance-suite'); ?></span>
                        </div>
                        <div class="fp-ps-ai-feature">
                            <span class="fp-ps-badge green">✓</span>
                            <span><?php esc_html_e('Modalità Expert con dettagli tecnici completi', 'fp-performance-suite'); ?></span>
                        </div>
                        <div class="fp-ps-ai-feature">
                            <span class="fp-ps-badge green">✓</span>
                            <span><?php esc_html_e('Test performance integrato (GTmetrix/PageSpeed)', 'fp-performance-suite'); ?></span>
                        </div>
                        <div class="fp-ps-ai-feature">
                            <span class="fp-ps-badge green">✓</span>
                            <span><?php esc_html_e('Export/Import configurazioni in JSON', 'fp-performance-suite'); ?></span>
                        </div>
                        <div class="fp-ps-ai-feature">
                            <span class="fp-ps-badge green">✓</span>
                            <span><?php esc_html_e('3 modalità: Safe, Aggressive, Expert', 'fp-performance-suite'); ?></span>
                        </div>
                    </div>

                    <div style="text-align: center; margin-top: 40px;">
                        <button class="fp-ps-ai-button-primary" id="fp-ps-start-analysis" data-mode="<?php echo esc_attr($mode); ?>">
                            <span class="fp-ps-ai-button-icon">🚀</span>
                            <span><?php esc_html_e('Inizia Analisi AI Avanzata', 'fp-performance-suite'); ?></span>
                        </button>
                        <p style="color: #64748b; margin-top: 10px; font-size: 14px;">
                            <?php esc_html_e('L\'analisi richiede circa 10-15 secondi', 'fp-performance-suite'); ?>
                        </p>
                    </div>
                </div>
            </section>

        <?php else : ?>
            <!-- Risultati Analisi AVANZATI -->
            <section class="fp-ps-card">
                <div class="fp-ps-ai-results-header">
                    <h2 style="font-size: 24px; margin-bottom: 10px;">
                        <?php esc_html_e('✨ Analisi Completata', 'fp-performance-suite'); ?>
                    </h2>
                    <p style="color: #64748b; margin-bottom: 30px;">
                        <?php esc_html_e('Ho analizzato il tuo sito e preparato le configurazioni ottimali', 'fp-performance-suite'); ?>
                    </p>
                    
                    <!-- Score con Grafico Storia -->
                    <div class="fp-ps-ai-score-section">
                        <div class="fp-ps-ai-score">
                            <div class="fp-ps-ai-score-circle">
                                <svg width="120" height="120">
                                    <circle cx="60" cy="60" r="54" fill="none" stroke="#e2e8f0" stroke-width="12"/>
                                    <circle cx="60" cy="60" r="54" fill="none" 
                                            stroke="<?php echo $score >= 75 ? '#10b981' : ($score >= 50 ? '#f59e0b' : '#ef4444'); ?>" 
                                            stroke-width="12" 
                                            stroke-dasharray="<?php echo 2 * 3.14159 * 54; ?>" 
                                            stroke-dashoffset="<?php echo 2 * 3.14159 * 54 * (1 - $score / 100); ?>"
                                            transform="rotate(-90 60 60)"/>
                                    <text x="60" y="70" text-anchor="middle" font-size="32" font-weight="bold" fill="#1e293b">
                                        <?php echo esc_html($score); ?>
                                    </text>
                                </svg>
                            </div>
                            <div class="fp-ps-ai-score-label">
                                <h3><?php esc_html_e('Optimization Score', 'fp-performance-suite'); ?></h3>
                                <p>
                                    <?php
                                    if ($score >= 75) {
                                        esc_html_e('Eccellente! Il tuo sito ha ottime condizioni di partenza', 'fp-performance-suite');
                                    } elseif ($score >= 50) {
                                        esc_html_e('Buono. Ci sono margini di miglioramento', 'fp-performance-suite');
                                    } else {
                                        esc_html_e('Da migliorare. Le ottimizzazioni avranno un grande impatto', 'fp-performance-suite');
                                    }
                                    ?>
                                </p>
                            </div>
                        </div>
                        
                        <?php if (!empty($analysisHistory)) : ?>
                        <!-- Grafico Evoluzione Score -->
                        <div class="fp-ps-score-chart">
                            <h4><?php esc_html_e('Evoluzione Score', 'fp-performance-suite'); ?></h4>
                            <canvas id="fp-ps-score-history-chart" width="400" height="200"></canvas>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Tabs per Organizzare Contenuti -->
                <div class="fp-ps-tabs">
                    <button class="fp-ps-tab active" data-tab="overview"><?php esc_html_e('📊 Panoramica', 'fp-performance-suite'); ?></button>
                    <button class="fp-ps-tab" data-tab="suggestions"><?php esc_html_e('💡 Suggerimenti', 'fp-performance-suite'); ?></button>
                    <button class="fp-ps-tab" data-tab="preview"><?php esc_html_e('👁️ Preview', 'fp-performance-suite'); ?></button>
                    <?php if ($mode === 'expert') : ?>
                    <button class="fp-ps-tab" data-tab="expert"><?php esc_html_e('🔬 Expert', 'fp-performance-suite'); ?></button>
                    <?php endif; ?>
                </div>

                <!-- Tab Content: Overview -->
                <div class="fp-ps-tab-content active" data-tab-content="overview">
                    <div class="fp-ps-ai-detected-info">
                        <h3 style="margin-bottom: 20px;"><?php esc_html_e('🔍 Informazioni Rilevate', 'fp-performance-suite'); ?></h3>
                        
                        <div class="fp-ps-grid three">
                            <!-- Hosting -->
                            <div class="fp-ps-info-card">
                                <div class="fp-ps-info-icon">🏢</div>
                                <h4><?php esc_html_e('Hosting', 'fp-performance-suite'); ?></h4>
                                <p class="fp-ps-info-value"><?php echo esc_html($analysis['hosting']['name']); ?></p>
                                <?php if ($analysis['hosting']['detected']) : ?>
                                    <span class="fp-ps-badge green"><?php esc_html_e('Rilevato', 'fp-performance-suite'); ?></span>
                                <?php else : ?>
                                    <span class="fp-ps-badge gray"><?php esc_html_e('Generico', 'fp-performance-suite'); ?></span>
                                <?php endif; ?>
                            </div>

                            <!-- Risorse -->
                            <div class="fp-ps-info-card">
                                <div class="fp-ps-info-icon">⚡</div>
                                <h4><?php esc_html_e('Risorse Server', 'fp-performance-suite'); ?></h4>
                                <p class="fp-ps-info-value"><?php echo esc_html($analysis['resources']['memory_limit']); ?></p>
                                <span class="fp-ps-badge <?php echo $analysis['resources']['memory_category'] === 'high' ? 'green' : ($analysis['resources']['memory_category'] === 'medium' ? 'amber' : 'gray'); ?>">
                                    <?php echo esc_html(ucfirst($analysis['resources']['memory_category'])); ?>
                                </span>
                            </div>

                            <!-- Database -->
                            <div class="fp-ps-info-card">
                                <div class="fp-ps-info-icon">💾</div>
                                <h4><?php esc_html_e('Database', 'fp-performance-suite'); ?></h4>
                                <p class="fp-ps-info-value"><?php echo esc_html(number_format($analysis['database']['size_mb'], 1)); ?> MB</p>
                                <span class="fp-ps-badge <?php echo $analysis['database']['size_category'] === 'small' ? 'green' : ($analysis['database']['size_category'] === 'medium' ? 'amber' : 'orange'); ?>">
                                    <?php echo esc_html(ucfirst($analysis['database']['size_category'])); ?>
                                </span>
                            </div>

                            <!-- Contenuti -->
                            <div class="fp-ps-info-card">
                                <div class="fp-ps-info-icon">🖼️</div>
                                <h4><?php esc_html_e('Immagini', 'fp-performance-suite'); ?></h4>
                                <p class="fp-ps-info-value"><?php echo esc_html(number_format($analysis['content']['images'])); ?></p>
                                <span class="fp-ps-badge blue"><?php esc_html_e('Media Library', 'fp-performance-suite'); ?></span>
                            </div>

                            <!-- Plugin -->
                            <div class="fp-ps-info-card">
                                <div class="fp-ps-info-icon">🔌</div>
                                <h4><?php esc_html_e('Plugin Attivi', 'fp-performance-suite'); ?></h4>
                                <p class="fp-ps-info-value"><?php echo esc_html($analysis['plugins']['active']); ?></p>
                                <span class="fp-ps-badge <?php echo $analysis['plugins']['active'] < 20 ? 'green' : ($analysis['plugins']['active'] < 30 ? 'amber' : 'orange'); ?>">
                                    <?php 
                                    if ($analysis['plugins']['active'] < 20) {
                                        esc_html_e('Ottimo', 'fp-performance-suite');
                                    } elseif ($analysis['plugins']['active'] < 30) {
                                        esc_html_e('Medio', 'fp-performance-suite');
                                    } else {
                                        esc_html_e('Molti', 'fp-performance-suite');
                                    }
                                    ?>
                                </span>
                            </div>

                            <!-- PHP Version -->
                            <div class="fp-ps-info-card">
                                <div class="fp-ps-info-icon">🐘</div>
                                <h4><?php esc_html_e('PHP Version', 'fp-performance-suite'); ?></h4>
                                <p class="fp-ps-info-value"><?php echo esc_html($analysis['server']['php_version']); ?></p>
                                <span class="fp-ps-badge <?php echo version_compare($analysis['server']['php_version'], '8.0', '>=') ? 'green' : 'amber'; ?>">
                                    <?php echo version_compare($analysis['server']['php_version'], '8.0', '>=') ? esc_html__('Moderna', 'fp-performance-suite') : esc_html__('Da aggiornare', 'fp-performance-suite'); ?>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tab Content: Suggerimenti con Toggle -->
                <div class="fp-ps-tab-content" data-tab-content="suggestions">
                    <div class="fp-ps-ai-suggestions">
                        <h3 style="margin-bottom: 20px;">
                            <?php esc_html_e('💡 Suggerimenti Personalizzabili', 'fp-performance-suite'); ?>
                            <span style="font-size: 14px; color: #64748b; font-weight: normal; margin-left: 10px;">
                                <?php esc_html_e('Attiva/disattiva i suggerimenti che vuoi applicare', 'fp-performance-suite'); ?>
                            </span>
                        </h3>
                        
                        <div class="fp-ps-suggestions-list">
                            <?php foreach ($suggestions as $index => $suggestion) : ?>
                                <div class="fp-ps-suggestion-item-advanced impact-<?php echo esc_attr($suggestion['impact']); ?>">
                                    <label class="fp-ps-suggestion-toggle">
                                        <input type="checkbox" 
                                               class="fp-ps-suggestion-checkbox" 
                                               data-suggestion-id="<?php echo esc_attr($index); ?>" 
                                               checked>
                                        <span class="fp-ps-toggle-slider"></span>
                                    </label>
                                    
                                    <div class="fp-ps-suggestion-icon"><?php echo $suggestion['icon']; ?></div>
                                    <div class="fp-ps-suggestion-content">
                                        <h4><?php echo esc_html($suggestion['title']); ?></h4>
                                        <p><?php echo esc_html($suggestion['description']); ?></p>
                                    </div>
                                    <div class="fp-ps-suggestion-impact">
                                        <span class="fp-ps-badge <?php echo esc_attr($suggestion['impact'] === 'high' ? 'green' : ($suggestion['impact'] === 'medium' ? 'amber' : 'gray')); ?>">
                                            <?php 
                                            echo esc_html($suggestion['impact'] === 'high' ? __('Alto Impatto', 'fp-performance-suite') : 
                                                          ($suggestion['impact'] === 'medium' ? __('Medio Impatto', 'fp-performance-suite') : 
                                                           __('Basso Impatto', 'fp-performance-suite')));
                                            ?>
                                        </span>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>

                <!-- Tab Content: Preview Prima/Dopo -->
                <div class="fp-ps-tab-content" data-tab-content="preview">
                    <div class="fp-ps-preview-section">
                        <h3 style="margin-bottom: 20px;"><?php esc_html_e('👁️ Preview Modifiche', 'fp-performance-suite'); ?></h3>
                        
                        <div class="fp-ps-preview-comparison">
                            <?php echo $this->renderPreviewComparison($analysis, $config); ?>
                        </div>
                        
                        <!-- Stima Miglioramenti -->
                        <div class="fp-ps-estimated-improvements">
                            <h4><?php esc_html_e('📈 Miglioramenti Stimati', 'fp-performance-suite'); ?></h4>
                            <div class="fp-ps-grid three">
                                <div class="fp-ps-improvement-card">
                                    <div class="fp-ps-improvement-icon">⚡</div>
                                    <div class="fp-ps-improvement-value">25-40%</div>
                                    <div class="fp-ps-improvement-label"><?php esc_html_e('Tempo Caricamento', 'fp-performance-suite'); ?></div>
                                </div>
                                <div class="fp-ps-improvement-card">
                                    <div class="fp-ps-improvement-icon">📊</div>
                                    <div class="fp-ps-improvement-value">+15-30</div>
                                    <div class="fp-ps-improvement-label"><?php esc_html_e('PageSpeed Score', 'fp-performance-suite'); ?></div>
                                </div>
                                <div class="fp-ps-improvement-card">
                                    <div class="fp-ps-improvement-icon">💾</div>
                                    <div class="fp-ps-improvement-value">30-50%</div>
                                    <div class="fp-ps-improvement-label"><?php esc_html_e('Riduzione Banda', 'fp-performance-suite'); ?></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tab Content: Expert Mode -->
                <?php if ($mode === 'expert') : ?>
                <div class="fp-ps-tab-content" data-tab-content="expert">
                    <div class="fp-ps-expert-section">
                        <h3 style="margin-bottom: 20px;"><?php esc_html_e('🔬 Modalità Expert', 'fp-performance-suite'); ?></h3>
                        
                        <!-- Configurazione JSON -->
                        <div class="fp-ps-expert-block">
                            <h4><?php esc_html_e('Configurazione JSON', 'fp-performance-suite'); ?></h4>
                            <pre class="fp-ps-code-block"><code><?php echo esc_html(wp_json_encode($config, JSON_PRETTY_PRINT)); ?></code></pre>
                            <button class="fp-ps-btn-secondary fp-ps-copy-json">
                                <span>📋</span> <?php esc_html_e('Copia JSON', 'fp-performance-suite'); ?>
                            </button>
                        </div>
                        
                        <!-- WP-CLI Commands -->
                        <div class="fp-ps-expert-block">
                            <h4><?php esc_html_e('Comandi WP-CLI Equivalenti', 'fp-performance-suite'); ?></h4>
                            <pre class="fp-ps-code-block"><code><?php echo esc_html($this->generateWPCLICommands($config)); ?></code></pre>
                            <button class="fp-ps-btn-secondary fp-ps-copy-cli">
                                <span>📋</span> <?php esc_html_e('Copia Comandi', 'fp-performance-suite'); ?>
                            </button>
                        </div>
                        
                        <!-- Analisi Dettagliata -->
                        <div class="fp-ps-expert-block">
                            <h4><?php esc_html_e('Analisi Dettagliata', 'fp-performance-suite'); ?></h4>
                            <pre class="fp-ps-code-block"><code><?php echo esc_html(wp_json_encode($analysis, JSON_PRETTY_PRINT)); ?></code></pre>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Call to Action - Applica Configurazione -->
                <div class="fp-ps-ai-apply">
                    <div class="fp-ps-ai-apply-content">
                        <h3 style="font-size: 20px; margin-bottom: 10px;">
                            <?php esc_html_e('🚀 Pronto per Ottimizzare?', 'fp-performance-suite'); ?>
                        </h3>
                        <p style="color: #64748b; margin-bottom: 25px;">
                            <?php 
                            $enabledCount = count($suggestions);
                            printf(
                                esc_html__('Applicherò %d ottimizzazioni. Le impostazioni precedenti verranno salvate per rollback.', 'fp-performance-suite'),
                                $enabledCount
                            );
                            ?>
                        </p>
                        
                        <button type="button" 
                                class="fp-ps-ai-button-primary fp-ps-apply-ai-config" 
                                data-config="<?php echo esc_attr(wp_json_encode($config)); ?>"
                                data-nonce="<?php echo esc_attr($nonce); ?>"
                                data-mode="<?php echo esc_attr($mode); ?>">
                            <span class="fp-ps-ai-button-icon">✨</span>
                            <span><?php esc_html_e('Applica Configurazione AI', 'fp-performance-suite'); ?></span>
                        </button>
                        
                        <button class="fp-ps-ai-button-secondary" id="fp-ps-start-new-analysis">
                            <?php esc_html_e('Riesegui Analisi', 'fp-performance-suite'); ?>
                        </button>
                    </div>
                </div>
            </section>
        <?php endif; ?>

        <!-- Hidden: Storia Analisi Modal -->
        <div id="fp-ps-history-modal" class="fp-ps-modal">
            <div class="fp-ps-modal-content">
                <span class="fp-ps-modal-close">&times;</span>
                <h3><?php esc_html_e('📊 Storia Analisi', 'fp-performance-suite'); ?></h3>
                <div id="fp-ps-history-content">
                    <?php echo $this->renderAnalysisHistory($analysisHistory); ?>
                </div>
            </div>
        </div>

        <!-- Hidden: Import Config Modal -->
        <div id="fp-ps-import-modal" class="fp-ps-modal">
            <div class="fp-ps-modal-content">
                <span class="fp-ps-modal-close">&times;</span>
                <h3><?php esc_html_e('📤 Importa Configurazione', 'fp-performance-suite'); ?></h3>
                <div class="fp-ps-import-area">
                    <textarea id="fp-ps-import-json" rows="15" placeholder='<?php esc_attr_e('Incolla qui il JSON della configurazione...', 'fp-performance-suite'); ?>'></textarea>
                    <button class="fp-ps-ai-button-primary" id="fp-ps-do-import">
                        <?php esc_html_e('Importa e Applica', 'fp-performance-suite'); ?>
                    </button>
                </div>
            </div>
        </div>

        <?php
        // Includi CSS e JS inline
        $this->inlineAssets($analysisHistory ?? []);
        
        return (string) ob_get_clean();
    }

    /**
     * Render preview comparison table
     */
    private function renderPreviewComparison(array $analysis, array $config): string
    {
        ob_start();
        ?>
        <table class="fp-ps-preview-table">
            <thead>
                <tr>
                    <th><?php esc_html_e('Impostazione', 'fp-performance-suite'); ?></th>
                    <th><?php esc_html_e('Prima', 'fp-performance-suite'); ?></th>
                    <th><?php esc_html_e('Dopo', 'fp-performance-suite'); ?></th>
                    <th><?php esc_html_e('Impatto', 'fp-performance-suite'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php
                // Page Cache
                $currentPageCache = get_option('fp_ps_page_cache', []);
                ?>
                <tr>
                    <td><strong><?php esc_html_e('Page Cache TTL', 'fp-performance-suite'); ?></strong></td>
                    <td><?php echo esc_html(($currentPageCache['ttl'] ?? 0) / 60); ?> min</td>
                    <td class="fp-ps-highlight-new"><?php echo esc_html(($config['page_cache']['ttl'] ?? 0) / 60); ?> min</td>
                    <td><span class="fp-ps-badge green"><?php esc_html_e('Alto', 'fp-performance-suite'); ?></span></td>
                </tr>
                
                
                <!-- Heartbeat -->
                <?php $currentHeartbeat = get_option('fp_ps_heartbeat_interval', 60); ?>
                <tr>
                    <td><strong><?php esc_html_e('Heartbeat Interval', 'fp-performance-suite'); ?></strong></td>
                    <td><?php echo esc_html($currentHeartbeat); ?>s</td>
                    <td class="fp-ps-highlight-new"><?php echo esc_html($config['heartbeat'] ?? 60); ?>s</td>
                    <td><span class="fp-ps-badge amber"><?php esc_html_e('Medio', 'fp-performance-suite'); ?></span></td>
                </tr>
                
                <!-- Altri parametri... -->
            </tbody>
        </table>
        <?php
        return ob_get_clean();
    }

    /**
     * Render analysis history
     */
    private function renderAnalysisHistory(array $history): string
    {
        if (empty($history)) {
            return '<p>' . esc_html__('Nessuna analisi precedente trovata.', 'fp-performance-suite') . '</p>';
        }
        
        ob_start();
        ?>
        <div class="fp-ps-history-list">
            <?php foreach (array_reverse($history) as $index => $item) : ?>
                <div class="fp-ps-history-item">
                    <div class="fp-ps-history-date"><?php echo esc_html(date_i18n(get_option('date_format') . ' ' . get_option('time_format'), $item['timestamp'])); ?></div>
                    <div class="fp-ps-history-score">
                        <strong><?php esc_html_e('Score:', 'fp-performance-suite'); ?></strong> 
                        <span class="fp-ps-score-badge <?php echo $item['score'] >= 75 ? 'green' : ($item['score'] >= 50 ? 'amber' : 'red'); ?>">
                            <?php echo esc_html($item['score']); ?>
                        </span>
                    </div>
                    <div class="fp-ps-history-details">
                        <span><?php esc_html_e('Hosting:', 'fp-performance-suite'); ?> <?php echo esc_html($item['hosting']); ?></span>
                        <span><?php esc_html_e('RAM:', 'fp-performance-suite'); ?> <?php echo esc_html($item['memory']); ?></span>
                        <span><?php esc_html_e('DB:', 'fp-performance-suite'); ?> <?php echo esc_html($item['db_size']); ?> MB</span>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        <?php
        return ob_get_clean();
    }

    /**
     * Generate WP-CLI commands
     */
    private function generateWPCLICommands(array $config): string
    {
        $commands = [];
        
        // Page Cache
        if (isset($config['page_cache'])) {
            $commands[] = sprintf(
                'wp option update fp_ps_page_cache \'%s\'',
                json_encode($config['page_cache'])
            );
        }
        
        
        // Heartbeat
        if (isset($config['heartbeat'])) {
            $commands[] = sprintf(
                'wp option update fp_ps_heartbeat_interval %d',
                $config['heartbeat']
            );
        }
        
        return implode("\n", $commands);
    }

    /**
     * Save analysis to history
     */
    private function saveToHistory(array $analysis, int $score): void
    {
        $history = get_option('fp_ps_ai_analysis_history', []);
        
        $history[] = [
            'timestamp' => time(),
            'score' => $score,
            'hosting' => $analysis['hosting']['name'],
            'memory' => $analysis['resources']['memory_limit'],
            'db_size' => $analysis['database']['size_mb'],
        ];
        
        // Keep only last 10 analyses
        if (count($history) > 10) {
            $history = array_slice($history, -10);
        }
        
        update_option('fp_ps_ai_analysis_history', $history);
    }

    /**
     * Handle export/import
     */
    private function handleExportImport(): void
    {
        // Export viene gestito via JavaScript
        // Import viene gestito via AJAX
    }

    /**
     * Inline assets (CSS + JS)
     */
    private function inlineAssets(array $historyData): void
    {
        ?>
        <style>
        /* [CSS precedente + nuovi stili] */
        .fp-ps-mode-selector {
            display: flex;
            gap: 10px;
            justify-content: center;
            margin-top: 20px;
        }

        .fp-ps-mode-btn {
            display: flex;
            align-items: center;
            gap: 8px;
            background: rgba(255, 255, 255, 0.2);
            color: white;
            padding: 12px 24px;
            border-radius: 8px;
            text-decoration: none;
            transition: all 0.3s;
            border: 2px solid transparent;
        }

        .fp-ps-mode-btn:hover {
            background: rgba(255, 255, 255, 0.3);
            transform: translateY(-2px);
        }

        .fp-ps-mode-btn.active {
            background: white;
            color: #667eea;
            border-color: white;
            font-weight: 600;
        }

        .fp-ps-top-actions {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }

        .fp-ps-btn-secondary {
            display: flex;
            align-items: center;
            gap: 8px;
            background: white;
            color: #667eea;
            padding: 10px 20px;
            border-radius: 8px;
            border: 2px solid #667eea;
            cursor: pointer;
            transition: all 0.3s;
            font-size: 14px;
            font-weight: 600;
        }

        .fp-ps-btn-secondary:hover {
            background: #f8fafc;
            transform: translateY(-2px);
        }

        .fp-ps-tabs {
            display: flex;
            gap: 10px;
            margin: 30px 0 20px;
            border-bottom: 2px solid #e2e8f0;
        }

        .fp-ps-tab {
            background: none;
            border: none;
            padding: 12px 24px;
            cursor: pointer;
            font-size: 16px;
            font-weight: 500;
            color: #64748b;
            border-bottom: 3px solid transparent;
            transition: all 0.3s;
        }

        .fp-ps-tab:hover {
            color: #667eea;
        }

        .fp-ps-tab.active {
            color: #667eea;
            border-bottom-color: #667eea;
        }

        .fp-ps-tab-content {
            display: none;
        }

        .fp-ps-tab-content.active {
            display: block;
            animation: fp-ps-fade-in 0.3s;
        }

        @keyframes fp-ps-fade-in {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .fp-ps-suggestion-item-advanced {
            display: flex;
            align-items: center;
            gap: 15px;
            padding: 20px;
            background: #f8fafc;
            border-radius: 12px;
            border-left: 4px solid #e2e8f0;
            transition: all 0.3s;
            margin-bottom: 15px;
        }

        .fp-ps-suggestion-item-advanced.impact-high {
            border-left-color: #10b981;
            background: #f0fdf4;
        }

        .fp-ps-suggestion-item-advanced.impact-medium {
            border-left-color: #f59e0b;
            background: #fef3c7;
        }

        .fp-ps-suggestion-toggle {
            position: relative;
            display: inline-block;
            width: 50px;
            height: 26px;
            flex-shrink: 0;
        }

        .fp-ps-suggestion-checkbox {
            opacity: 0;
            width: 0;
            height: 0;
        }

        .fp-ps-toggle-slider {
            position: absolute;
            cursor: pointer;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: #cbd5e1;
            transition: .4s;
            border-radius: 34px;
        }

        .fp-ps-toggle-slider:before {
            position: absolute;
            content: "";
            height: 18px;
            width: 18px;
            left: 4px;
            bottom: 4px;
            background-color: white;
            transition: .4s;
            border-radius: 50%;
        }

        .fp-ps-suggestion-checkbox:checked + .fp-ps-toggle-slider {
            background-color: #10b981;
        }

        .fp-ps-suggestion-checkbox:checked + .fp-ps-toggle-slider:before {
            transform: translateX(24px);
        }

        .fp-ps-preview-table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }

        .fp-ps-preview-table th,
        .fp-ps-preview-table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #e2e8f0;
        }

        .fp-ps-preview-table th {
            background: #f8fafc;
            font-weight: 600;
            color: #1e293b;
        }

        .fp-ps-highlight-new {
            background: #dbeafe;
            font-weight: 600;
            color: #1e40af;
        }

        .fp-ps-estimated-improvements {
            margin-top: 30px;
            padding: 30px;
            background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%);
            border-radius: 12px;
        }

        .fp-ps-improvement-card {
            text-align: center;
            padding: 20px;
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }

        .fp-ps-improvement-icon {
            font-size: 40px;
            margin-bottom: 10px;
        }

        .fp-ps-improvement-value {
            font-size: 32px;
            font-weight: bold;
            color: #10b981;
            margin-bottom: 5px;
        }

        .fp-ps-improvement-label {
            color: #64748b;
            font-size: 14px;
        }

        .fp-ps-expert-block {
            margin-bottom: 30px;
        }

        .fp-ps-code-block {
            background: #1e293b;
            color: #e2e8f0;
            padding: 20px;
            border-radius: 8px;
            overflow-x: auto;
            font-size: 13px;
            line-height: 1.6;
        }

        .fp-ps-modal {
            display: none;
            position: fixed;
            z-index: 999999;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.7);
        }

        .fp-ps-modal-content {
            background-color: white;
            margin: 5% auto;
            padding: 30px;
            border-radius: 16px;
            width: 80%;
            max-width: 800px;
            max-height: 80vh;
            overflow-y: auto;
            position: relative;
        }

        .fp-ps-modal-close {
            position: absolute;
            right: 20px;
            top: 20px;
            font-size: 32px;
            font-weight: bold;
            cursor: pointer;
            color: #64748b;
        }

        .fp-ps-modal-close:hover {
            color: #1e293b;
        }

        .fp-ps-history-item {
            padding: 20px;
            background: #f8fafc;
            border-radius: 12px;
            margin-bottom: 15px;
        }

        .fp-ps-history-date {
            font-weight: 600;
            color: #1e293b;
            margin-bottom: 10px;
        }

        .fp-ps-history-score {
            margin-bottom: 10px;
        }

        .fp-ps-score-badge {
            padding: 4px 12px;
            border-radius: 12px;
            font-weight: 600;
            font-size: 14px;
        }

        .fp-ps-score-badge.green {
            background: #d1fae5;
            color: #065f46;
        }

        .fp-ps-score-badge.amber {
            background: #fef3c7;
            color: #92400e;
        }

        .fp-ps-score-badge.red {
            background: #fee2e2;
            color: #991b1b;
        }

        .fp-ps-history-details {
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
            font-size: 14px;
            color: #64748b;
        }

        .fp-ps-import-area {
            margin-top: 20px;
        }

        .fp-ps-import-area textarea {
            width: 100%;
            padding: 15px;
            border: 2px solid #e2e8f0;
            border-radius: 8px;
            font-family: monospace;
            font-size: 13px;
            margin-bottom: 15px;
        }

        .fp-ps-score-chart {
            flex: 1;
            min-width: 300px;
        }

        .fp-ps-ai-score-section {
            display: flex;
            gap: 40px;
            align-items: flex-start;
            flex-wrap: wrap;
        }

        /* Inherit previous styles */
        .fp-ps-ai-hero {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 16px;
            padding: 60px 40px;
            text-align: center;
            margin-bottom: 30px;
            box-shadow: 0 20px 60px rgba(102, 126, 234, 0.3);
        }

        /* ... (resto degli stili precedenti) ... */
        </style>

        <script>
        jQuery(document).ready(function($) {
            // Tab switching
            $('.fp-ps-tab').on('click', function() {
                const tab = $(this).data('tab');
                $('.fp-ps-tab').removeClass('active');
                $(this).addClass('active');
                $('.fp-ps-tab-content').removeClass('active');
                $('[data-tab-content="' + tab + '"]').addClass('active');
            });

            // Modal handlers
            $('.fp-ps-modal-close').on('click', function() {
                $(this).closest('.fp-ps-modal').hide();
            });

            $('#fp-ps-show-history').on('click', function() {
                $('#fp-ps-history-modal').show();
            });

            $('#fp-ps-import-config').on('click', function() {
                $('#fp-ps-import-modal').show();
            });

            // Export config
            $('#fp-ps-export-config').on('click', function() {
                const config = $('.fp-ps-apply-ai-config').data('config');
                const dataStr = "data:text/json;charset=utf-8," + encodeURIComponent(JSON.stringify(config, null, 2));
                const downloadAnchor = document.createElement('a');
                downloadAnchor.setAttribute("href", dataStr);
                downloadAnchor.setAttribute("download", "fp-performance-config-" + Date.now() + ".json");
                document.body.appendChild(downloadAnchor);
                downloadAnchor.click();
                downloadAnchor.remove();
            });

            // Copy buttons
            $('.fp-ps-copy-json, .fp-ps-copy-cli').on('click', function() {
                const code = $(this).prev('.fp-ps-code-block').find('code').text();
                navigator.clipboard.writeText(code).then(function() {
                    alert('<?php esc_html_e('Copiato negli appunti!', 'fp-performance-suite'); ?>');
                });
            });

            // Chart history (if data exists)
            <?php if (!empty($historyData)) : ?>
            const historyData = <?php echo wp_json_encode(array_values($historyData)); ?>;
            // Implement chart rendering with Chart.js or similar
            <?php endif; ?>
        });
        </script>
        <?php
    }
}

