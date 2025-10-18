<?php

namespace FP\PerfSuite\Admin\Pages;

use FP\PerfSuite\ServiceContainer;
use FP\PerfSuite\Utils\Capabilities;

abstract class AbstractPage
{
    protected ServiceContainer $container;

    public function __construct(ServiceContainer $container)
    {
        $this->container = $container;
    }

    abstract public function slug(): string;

    abstract public function title(): string;

    public function capability(): string
    {
        return $this->requiredCapability();
    }

    abstract public function view(): string;

    abstract protected function content(): string;

    public function render(): void
    {
        if (!current_user_can($this->capability())) {
            wp_die(esc_html__('You do not have permission to access this page.', 'fp-performance-suite'));
        }

        try {
            $view = $this->view();
            $data = $this->data();
            $data['content'] = $this->content();
            if (is_readable($view)) {
                $pageData = $data;
                include $view;
            }
        } catch (\Throwable $e) {
            // Log the error
            error_log('[FP Performance Suite] Error rendering page ' . $this->slug() . ': ' . $e->getMessage());
            error_log('[FP Performance Suite] Stack trace: ' . $e->getTraceAsString());
            
            // Display user-friendly error message
            ?>
            <div class="wrap">
                <h1><?php echo esc_html($this->title()); ?></h1>
                <div class="notice notice-error">
                    <p>
                        <strong><?php esc_html_e('Errore:', 'fp-performance-suite'); ?></strong>
                        <?php esc_html_e('Si è verificato un errore durante il caricamento di questa pagina.', 'fp-performance-suite'); ?>
                    </p>
                    <details>
                        <summary style="cursor: pointer;"><?php esc_html_e('Dettagli tecnici', 'fp-performance-suite'); ?></summary>
                        <pre style="background: #f0f0f1; padding: 10px; overflow: auto;"><?php echo esc_html($e->getMessage()); ?></pre>
                        <p><strong><?php esc_html_e('File:', 'fp-performance-suite'); ?></strong> <?php echo esc_html($e->getFile()); ?></p>
                        <p><strong><?php esc_html_e('Linea:', 'fp-performance-suite'); ?></strong> <?php echo esc_html($e->getLine()); ?></p>
                    </details>
                </div>
            </div>
            <?php
        }
    }

    /**
     * @return array<string, mixed>
     */
    protected function data(): array
    {
        return [];
    }

    protected function requiredCapability(): string
    {
        return Capabilities::required();
    }
}
