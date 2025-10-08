<?php

namespace FP\PerfSuite\Admin\Pages;

use FP\PerfSuite\Services\Presets\Manager as PresetManager;

use function __;
use function esc_attr;
use function esc_html;
use function esc_html_e;
use function esc_url;
use function rest_url;
use function str_replace;
use function ucfirst;
use function wp_json_encode;
use function wp_create_nonce;

class Presets extends AbstractPage
{
    public function slug(): string
    {
        return 'fp-performance-suite-presets';
    }

    public function title(): string
    {
        return __('Preset Bundles', 'fp-performance-suite');
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
            'breadcrumbs' => [__('Optimization', 'fp-performance-suite'), __('Presets', 'fp-performance-suite')],
        ];
    }

    protected function content(): string
    {
        $manager = $this->container->get(PresetManager::class);
        $presets = $manager->presets();
        $active = $manager->getActivePreset();
        $nonce = wp_create_nonce('wp_rest');
        ob_start();
        ?>
        <section class="fp-ps-grid three">
            <?php foreach ($presets as $key => $preset) : ?>
                <article class="fp-ps-card">
                    <header>
                        <h2><?php echo esc_html($preset['label']); ?></h2>
                        <?php if ($active === $key) : ?>
                            <span class="fp-ps-badge green"><?php esc_html_e('Active', 'fp-performance-suite'); ?></span>
                        <?php endif; ?>
                    </header>
                    <p><?php esc_html_e('Safe defaults crafted for shared hosting providers.', 'fp-performance-suite'); ?></p>
                    <ul>
                        <?php foreach ($preset['config'] as $section => $config) : ?>
                            <li><strong><?php echo esc_html(ucfirst(str_replace('_', ' ', $section))); ?>:</strong> <?php echo esc_html(wp_json_encode($config)); ?></li>
                        <?php endforeach; ?>
                    </ul>
                    <div class="fp-ps-actions">
                        <button class="button button-primary" data-fp-preset="<?php echo esc_attr($key); ?>" data-fp-nonce="<?php echo esc_attr($nonce); ?>" data-risk="amber"><?php esc_html_e('Apply Preset', 'fp-performance-suite'); ?></button>
                        <?php if ($active === $key) : ?>
                            <form method="post" action="<?php echo esc_url(rest_url('fp-ps/v1/preset/rollback')); ?>">
                                <input type="hidden" name="_wpnonce" value="<?php echo esc_attr($nonce); ?>" />
                                <button type="submit" class="button" data-risk="amber"><?php esc_html_e('Rollback', 'fp-performance-suite'); ?></button>
                            </form>
                        <?php endif; ?>
                    </div>
                </article>
            <?php endforeach; ?>
        </section>
        <?php
        return (string) ob_get_clean();
    }
}
