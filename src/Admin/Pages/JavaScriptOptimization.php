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

}
