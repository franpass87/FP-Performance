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

        $view = $this->view();
        $data = $this->data();
        $data['content'] = $this->content();
        if (is_readable($view)) {
            $pageData = $data;
            include $view;
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
