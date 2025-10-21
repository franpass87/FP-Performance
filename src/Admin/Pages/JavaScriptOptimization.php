<?php

namespace FP\PerfSuite\Admin\Pages;

use FP\PerfSuite\ServiceContainer;
use FP\PerfSuite\Services\Assets\UnusedJavaScriptOptimizer;
use FP\PerfSuite\Services\Assets\CodeSplittingManager;
use FP\PerfSuite\Services\Assets\JavaScriptTreeShaker;

use function add_action;
use function wp_verify_nonce;
use function wp_redirect;
use function admin_url;
use function add_query_arg;
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
        return __DIR__ . '/../../Views/admin-page.php';
    }


    public function register(): void
    {
        add_action('admin_post_fp_ps_save_js_optimization', [$this, 'handleSave']);
    }

    /**
     * Handle form submission
     */
    public function handleSave(): void
    {
        if (!wp_verify_nonce($_POST['fp_ps_js_optimization_nonce'] ?? '', 'fp_ps_js_optimization')) {
            wp_die(__('Security check failed', 'fp-performance-suite'));
        }

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

        wp_redirect(add_query_arg(['page' => 'fp-performance-suite-js-optimization', 'saved' => '1'], admin_url('admin.php')));
        exit;
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
        <div class="fp-ps-page-content">
            <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" class="fp-ps-settings-form">
                <?php wp_nonce_field('fp_ps_js_optimization', 'fp_ps_js_optimization_nonce'); ?>
                <input type="hidden" name="action" value="fp_ps_save_js_optimization">
                
                <div class="fp-ps-card">
                    <h2><?php esc_html_e('Ottimizzazione JavaScript Avanzata', 'fp-performance-suite'); ?></h2>
                    <p><?php esc_html_e('Riduci il JavaScript non utilizzato e ottimizza il caricamento degli script per migliorare le prestazioni.', 'fp-performance-suite'); ?></p>
                    
                    <!-- Unused JavaScript Optimization -->
                    <div class="fp-ps-section">
                        <h3><?php esc_html_e('Rimozione JavaScript Non Utilizzato', 'fp-performance-suite'); ?></h3>
                        <table class="form-table">
                            <tr>
                                <th scope="row">
                                    <label for="unused_optimization_enabled">
                                        <?php esc_html_e('Abilita Ottimizzazione', 'fp-performance-suite'); ?>
                                    </label>
                                </th>
                                <td>
                                    <input type="checkbox" 
                                           id="unused_optimization_enabled" 
                                           name="unused_optimization[enabled]" 
                                           value="1"
                                           <?php checked($unusedSettings['enabled']); ?>>
                                    <p class="description">
                                        <?php esc_html_e('Attiva la rimozione automatica del codice JavaScript non utilizzato.', 'fp-performance-suite'); ?>
                                    </p>
                                </td>
                            </tr>
                        </table>
                    </div>
                    
                    <!-- Code Splitting -->
                    <div class="fp-ps-section">
                        <h3><?php esc_html_e('Code Splitting', 'fp-performance-suite'); ?></h3>
                        <table class="form-table">
                            <tr>
                                <th scope="row">
                                    <label for="code_splitting_enabled">
                                        <?php esc_html_e('Abilita Code Splitting', 'fp-performance-suite'); ?>
                                    </label>
                                </th>
                                <td>
                                    <input type="checkbox" 
                                           id="code_splitting_enabled" 
                                           name="code_splitting[enabled]" 
                                           value="1"
                                           <?php checked($codeSplittingSettings['enabled']); ?>>
                                    <p class="description">
                                        <?php esc_html_e('Divide il codice JavaScript in chunks più piccoli per un caricamento più efficiente.', 'fp-performance-suite'); ?>
                                    </p>
                                </td>
                            </tr>
                        </table>
                    </div>
                    
                    <!-- Tree Shaking -->
                    <div class="fp-ps-section">
                        <h3><?php esc_html_e('Tree Shaking', 'fp-performance-suite'); ?></h3>
                        <table class="form-table">
                            <tr>
                                <th scope="row">
                                    <label for="tree_shaking_enabled">
                                        <?php esc_html_e('Abilita Tree Shaking', 'fp-performance-suite'); ?>
                                    </label>
                                </th>
                                <td>
                                    <input type="checkbox" 
                                           id="tree_shaking_enabled" 
                                           name="tree_shaking[enabled]" 
                                           value="1"
                                           <?php checked($treeShakingSettings['enabled']); ?>>
                                    <p class="description">
                                        <?php esc_html_e('Rimuove il codice morto e le funzioni non utilizzate dai bundle JavaScript.', 'fp-performance-suite'); ?>
                                    </p>
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>
                
                <p class="submit">
                    <button type="submit" class="button button-primary">
                        <?php esc_html_e('Salva Impostazioni', 'fp-performance-suite'); ?>
                    </button>
                </p>
            </form>
        </div>
        <?php
        return ob_get_clean();
    }

}
