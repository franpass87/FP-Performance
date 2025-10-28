<?php

namespace FP\PerfSuite\Admin\Pages;

use FP\PerfSuite\ServiceContainer;
use FP\PerfSuite\Services\Assets\UnusedJavaScriptOptimizer;
use FP\PerfSuite\Services\Assets\CodeSplittingManager;
use FP\PerfSuite\Services\Assets\JavaScriptTreeShaker;

use function add_action;
use function wp_verify_nonce;
use function esc_html;
use function esc_attr;
use function checked;
use function selected;

/**
 * JavaScript Optimization Admin Page
 *
 * Manages advanced JavaScript optimizations including:
 * - Unused JavaScript reduction
 * - Code splitting
 * - Tree shaking
 * - Dynamic imports
 * - Conditional loading
 *
 * @package FP\PerfSuite\Admin\Pages
 * @author Francesco Passeri
 */
class JavaScriptOptimization extends AbstractPage
{
    private UnusedJavaScriptOptimizer $unusedOptimizer;
    private CodeSplittingManager $codeSplittingManager;
    private JavaScriptTreeShaker $treeShaker;

    public function __construct(ServiceContainer $container)
    {
        parent::__construct($container);
        $this->unusedOptimizer = new UnusedJavaScriptOptimizer();
        $this->codeSplittingManager = new CodeSplittingManager();
        $this->treeShaker = new JavaScriptTreeShaker();
    }

    public function slug(): string
    {
        return 'fp-performance-suite-js-optimization';
    }

    public function title(): string
    {
        return __('JavaScript Optimization', 'fp-performance-suite');
    }

    public function view(): string
    {
        return FP_PERF_SUITE_DIR . '/views/admin-page.php';
    }

    public function register(): void
    {
        add_action('admin_post_fp_ps_save_js_optimization', [$this, 'handleSave']);
    }

    /**
     * Handle form submission with proper error handling
     */
    public function handleSave(): void
    {
        // Verifica che sia una richiesta POST
        if (!isset($_POST['fp_ps_js_optimization_nonce']) || !wp_verify_nonce($_POST['fp_ps_js_optimization_nonce'], 'fp_ps_js_optimization')) {
            wp_die(__('Security check failed', 'fp-performance-suite'));
        }

        // Gestione errori per prevenire pagine vuote
        try {
            // Save unused optimization settings
            if (isset($_POST['unused_optimization'])) {
                $this->unusedOptimizer->update($_POST['unused_optimization']);
            }

            // Save code splitting settings
            if (isset($_POST['code_splitting'])) {
                $this->codeSplittingManager->update($_POST['code_splitting']);
            }

            // Save tree shaking settings
            if (isset($_POST['tree_shaking'])) {
                $this->treeShaker->update($_POST['tree_shaking']);
            }

            // Redirect with success message instead of returning message
            wp_redirect(add_query_arg(['page' => 'fp-performance-suite-js-optimization', 'saved' => '1'], admin_url('admin.php')));
            exit;
            
        } catch (\Exception $e) {
            // Log dell'errore per debug
            error_log('FP Performance Suite - JavaScript Optimization Error: ' . $e->getMessage());
            
            // Redirect with error message
            wp_redirect(add_query_arg([
                'page' => 'fp-performance-suite-js-optimization', 
                'error' => urlencode($e->getMessage())
            ], admin_url('admin.php')));
            exit;
        }
    }

    /**
     * Render the page
     */
    public function render(): void
    {
        parent::render();
    }

    /**
     * Render page content
     */
    protected function content(): string
    {
        $unusedSettings = $this->unusedOptimizer->settings();
        $codeSplittingSettings = $this->codeSplittingManager->settings();
        $treeShakingSettings = $this->treeShaker->settings();
        
        ob_start();
        ?>
        
        <?php
        // Gestione messaggi di successo e errore
        if (isset($_GET['saved']) && $_GET['saved'] === '1') {
            echo '<div class="notice notice-success is-dismissible"><p><strong>' . esc_html__('Impostazioni salvate con successo!', 'fp-performance-suite') . '</strong></p></div>';
        }
        
        if (isset($_GET['error'])) {
            $error_message = sanitize_text_field(wp_unslash($_GET['error']));
            echo '<div class="notice notice-error is-dismissible"><p><strong>' . esc_html__('Errore:', 'fp-performance-suite') . '</strong> ' . esc_html($error_message) . '</p></div>';
        }
        ?>
        
        <!-- Info Notice -->
        <div class="fp-ps-notice info fp-ps-mb-lg">
            <span class="fp-ps-notice-icon">⚡</span>
            <div class="fp-ps-notice-content">
                <p><strong><?php esc_html_e('Ottimizzazione JavaScript Avanzata', 'fp-performance-suite'); ?></strong></p>
                <p><?php esc_html_e('Tecniche avanzate per ridurre il JavaScript non utilizzato e ottimizzare il caricamento degli script per migliorare le prestazioni e i Core Web Vitals.', 'fp-performance-suite'); ?></p>
            </div>
        </div>
        
        <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
            <?php wp_nonce_field('fp_ps_js_optimization', 'fp_ps_js_optimization_nonce'); ?>
            <input type="hidden" name="action" value="fp_ps_save_js_optimization">
            
            <!-- Unused JavaScript Optimization -->
            <section class="fp-ps-card fp-ps-mb-lg">
                <h2><?php esc_html_e('🔍 Rimozione JavaScript Non Utilizzato', 'fp-performance-suite'); ?></h2>
                <p class="description">
                    <?php esc_html_e('Identifica e rimuove automaticamente il codice JavaScript che non viene eseguito sulla pagina, riducendo il peso dei bundle e migliorando il First Contentful Paint (FCP).', 'fp-performance-suite'); ?>
                </p>
                
                <div class="fp-ps-mt-md">
                    <label class="fp-ps-toggle">
                        <span class="info">
                            <strong><?php esc_html_e('Abilita Ottimizzazione', 'fp-performance-suite'); ?></strong>
                            <span class="fp-ps-risk-indicator amber">
                                <div class="fp-ps-risk-tooltip amber">
                                    <div class="fp-ps-risk-tooltip-title">
                                        <span class="icon">⚠</span>
                                        <?php esc_html_e('Rischio Medio', 'fp-performance-suite'); ?>
                                    </div>
                                    <div class="fp-ps-risk-tooltip-section">
                                        <div class="fp-ps-risk-tooltip-label"><?php esc_html_e('Descrizione', 'fp-performance-suite'); ?></div>
                                        <div class="fp-ps-risk-tooltip-text"><?php esc_html_e('Attiva la rimozione automatica del codice JavaScript non utilizzato dalle pagine.', 'fp-performance-suite'); ?></div>
                                    </div>
                                    <div class="fp-ps-risk-tooltip-section">
                                        <div class="fp-ps-risk-tooltip-label"><?php esc_html_e('Benefici', 'fp-performance-suite'); ?></div>
                                        <div class="fp-ps-risk-tooltip-text"><?php esc_html_e('Riduce il peso JavaScript del 30-50%, migliora FCP e Time to Interactive (TTI).', 'fp-performance-suite'); ?></div>
                                    </div>
                                    <div class="fp-ps-risk-tooltip-section">
                                        <div class="fp-ps-risk-tooltip-label"><?php esc_html_e('Consiglio', 'fp-performance-suite'); ?></div>
                                        <div class="fp-ps-risk-tooltip-text"><?php esc_html_e('⚠️ Testa accuratamente: può rompere funzionalità dinamiche. Usa le esclusioni per proteggere script critici.', 'fp-performance-suite'); ?></div>
                                    </div>
                                </div>
                            </span>
                            <small><?php esc_html_e('Attiva la rimozione automatica del codice JavaScript non utilizzato', 'fp-performance-suite'); ?></small>
                        </span>
                        <input type="checkbox" 
                               name="unused_optimization[enabled]" 
                               value="1"
                               <?php checked($unusedSettings['enabled']); ?>
                               data-risk="amber" />
                    </label>
                </div>
            </section>
            
            <!-- Code Splitting -->
            <section class="fp-ps-card fp-ps-mb-lg">
                <h2><?php esc_html_e('📦 Code Splitting', 'fp-performance-suite'); ?></h2>
                <p class="description">
                    <?php esc_html_e('Divide il codice JavaScript in chunks più piccoli caricati on-demand, riducendo il payload iniziale e migliorando i tempi di caricamento percepiti.', 'fp-performance-suite'); ?>
                </p>
                
                <div class="fp-ps-mt-md">
                    <label class="fp-ps-toggle">
                        <span class="info">
                            <strong><?php esc_html_e('Abilita Code Splitting', 'fp-performance-suite'); ?></strong>
                            <span class="fp-ps-risk-indicator amber">
                                <div class="fp-ps-risk-tooltip amber">
                                    <div class="fp-ps-risk-tooltip-title">
                                        <span class="icon">⚠</span>
                                        <?php esc_html_e('Rischio Medio', 'fp-performance-suite'); ?>
                                    </div>
                                    <div class="fp-ps-risk-tooltip-section">
                                        <div class="fp-ps-risk-tooltip-label"><?php esc_html_e('Descrizione', 'fp-performance-suite'); ?></div>
                                        <div class="fp-ps-risk-tooltip-text"><?php esc_html_e('Divide il codice JavaScript in chunks separati caricati solo quando necessario.', 'fp-performance-suite'); ?></div>
                                    </div>
                                    <div class="fp-ps-risk-tooltip-section">
                                        <div class="fp-ps-risk-tooltip-label"><?php esc_html_e('Benefici', 'fp-performance-suite'); ?></div>
                                        <div class="fp-ps-risk-tooltip-text"><?php esc_html_e('Riduce il bundle iniziale, migliora FCP e LCP caricando solo il codice necessario.', 'fp-performance-suite'); ?></div>
                                    </div>
                                    <div class="fp-ps-risk-tooltip-section">
                                        <div class="fp-ps-risk-tooltip-label"><?php esc_html_e('Consiglio', 'fp-performance-suite'); ?></div>
                                        <div class="fp-ps-risk-tooltip-text"><?php esc_html_e('⚠️ Utile per siti complessi. Potrebbe aumentare leggermente le richieste HTTP.', 'fp-performance-suite'); ?></div>
                                    </div>
                                </div>
                            </span>
                            <small><?php esc_html_e('Divide il codice JavaScript in chunks più piccoli per un caricamento più efficiente', 'fp-performance-suite'); ?></small>
                        </span>
                        <input type="checkbox" 
                               name="code_splitting[enabled]" 
                               value="1"
                               <?php checked($codeSplittingSettings['enabled']); ?>
                               data-risk="amber" />
                    </label>
                </div>
            </section>
            
            <!-- Tree Shaking -->
            <section class="fp-ps-card fp-ps-mb-lg">
                <h2><?php esc_html_e('🌳 Tree Shaking', 'fp-performance-suite'); ?></h2>
                <p class="description">
                    <?php esc_html_e('Elimina il "dead code" e le funzioni non utilizzate dai bundle JavaScript, mantenendo solo il codice effettivamente eseguito.', 'fp-performance-suite'); ?>
                </p>
                
                <div class="fp-ps-mt-md">
                    <label class="fp-ps-toggle">
                        <span class="info">
                            <strong><?php esc_html_e('Abilita Tree Shaking', 'fp-performance-suite'); ?></strong>
                            <span class="fp-ps-risk-indicator green">
                                <div class="fp-ps-risk-tooltip green">
                                    <div class="fp-ps-risk-tooltip-title">
                                        <span class="icon">✓</span>
                                        <?php esc_html_e('Rischio Basso', 'fp-performance-suite'); ?>
                                    </div>
                                    <div class="fp-ps-risk-tooltip-section">
                                        <div class="fp-ps-risk-tooltip-label"><?php esc_html_e('Descrizione', 'fp-performance-suite'); ?></div>
                                        <div class="fp-ps-risk-tooltip-text"><?php esc_html_e('Rimuove automaticamente il codice morto e le funzioni non utilizzate dai bundle JavaScript.', 'fp-performance-suite'); ?></div>
                                    </div>
                                    <div class="fp-ps-risk-tooltip-section">
                                        <div class="fp-ps-risk-tooltip-label"><?php esc_html_e('Benefici', 'fp-performance-suite'); ?></div>
                                        <div class="fp-ps-risk-tooltip-text"><?php esc_html_e('Riduce il peso dei bundle del 10-30% eliminando codice non raggiungibile.', 'fp-performance-suite'); ?></div>
                                    </div>
                                    <div class="fp-ps-risk-tooltip-section">
                                        <div class="fp-ps-risk-tooltip-label"><?php esc_html_e('Consiglio', 'fp-performance-suite'); ?></div>
                                        <div class="fp-ps-risk-tooltip-text"><?php esc_html_e('✅ Consigliato: sicuro e efficace, rimuove solo codice oggettivamente non utilizzato.', 'fp-performance-suite'); ?></div>
                                    </div>
                                </div>
                            </span>
                            <small><?php esc_html_e('Rimuove il codice morto e le funzioni non utilizzate dai bundle JavaScript', 'fp-performance-suite'); ?></small>
                        </span>
                        <input type="checkbox" 
                               name="tree_shaking[enabled]" 
                               value="1"
                               <?php checked($treeShakingSettings['enabled']); ?>
                               data-risk="green" />
                    </label>
                </div>
            </section>
            
            <!-- Stats Overview -->
            <section class="fp-ps-card">
                <h2><?php esc_html_e('📊 Impatto Ottimizzazioni', 'fp-performance-suite'); ?></h2>
                <div class="fp-ps-grid three fp-ps-mt-md">
                    <div class="fp-ps-stat-box">
                        <div class="stat-value success"><?php echo $unusedSettings['enabled'] ? '✓' : '—'; ?></div>
                        <div class="stat-label"><?php esc_html_e('Unused JS', 'fp-performance-suite'); ?></div>
                    </div>
                    <div class="fp-ps-stat-box">
                        <div class="stat-value success"><?php echo $codeSplittingSettings['enabled'] ? '✓' : '—'; ?></div>
                        <div class="stat-label"><?php esc_html_e('Code Splitting', 'fp-performance-suite'); ?></div>
                    </div>
                    <div class="fp-ps-stat-box">
                        <div class="stat-value success"><?php echo $treeShakingSettings['enabled'] ? '✓' : '—'; ?></div>
                        <div class="stat-label"><?php esc_html_e('Tree Shaking', 'fp-performance-suite'); ?></div>
                    </div>
                </div>
                
                <?php 
                $activeCount = (int)$unusedSettings['enabled'] + (int)$codeSplittingSettings['enabled'] + (int)$treeShakingSettings['enabled'];
                ?>
                
                <div class="fp-ps-mt-lg">
                    <?php if ($activeCount === 0) : ?>
                        <div class="fp-ps-notice warning">
                            <span class="fp-ps-notice-icon">⚠️</span>
                            <div class="fp-ps-notice-content">
                                <p><strong><?php esc_html_e('Nessuna ottimizzazione attiva', 'fp-performance-suite'); ?></strong></p>
                                <p><?php esc_html_e('Abilita almeno un\'ottimizzazione per migliorare le performance JavaScript del tuo sito.', 'fp-performance-suite'); ?></p>
                            </div>
                        </div>
                    <?php elseif ($activeCount === 3) : ?>
                        <div class="fp-ps-notice success">
                            <span class="fp-ps-notice-icon">✅</span>
                            <div class="fp-ps-notice-content">
                                <p><strong><?php esc_html_e('Tutte le ottimizzazioni attive!', 'fp-performance-suite'); ?></strong></p>
                                <p><?php esc_html_e('Il tuo sito sta utilizzando tutte le tecniche avanzate di ottimizzazione JavaScript disponibili.', 'fp-performance-suite'); ?></p>
                            </div>
                        </div>
                    <?php else : ?>
                        <div class="fp-ps-notice info">
                            <span class="fp-ps-notice-icon">ℹ️</span>
                            <div class="fp-ps-notice-content">
                                <p><strong><?php printf(esc_html__('%d ottimizzazioni attive su 3', 'fp-performance-suite'), $activeCount); ?></strong></p>
                                <p><?php esc_html_e('Considera di attivare le altre ottimizzazioni per massimizzare le performance.', 'fp-performance-suite'); ?></p>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </section>
            
            <!-- Submit Button -->
            <div class="fp-ps-mt-lg">
                <button type="submit" class="button button-primary button-hero">
                    <?php esc_html_e('Salva Impostazioni', 'fp-performance-suite'); ?>
                </button>
            </div>
        </form>
        
        <?php
        return (string) ob_get_clean();
    }

}
