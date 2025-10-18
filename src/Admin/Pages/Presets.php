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
                            <li>
                                <strong><?php echo esc_html(ucfirst(str_replace('_', ' ', $section))); ?>:</strong>
                                <?php if (is_array($config)) : ?>
                                    <ul style="margin-left: 20px; font-size: 0.9em;">
                                        <?php foreach ($config as $key => $value) : ?>
                                            <li>
                                                <?php echo esc_html(ucfirst(str_replace('_', ' ', $key))); ?>: 
                                                <?php if (is_bool($value)) : ?>
                                                    <span class="fp-ps-badge <?php echo $value ? 'green' : 'gray'; ?>" style="font-size: 0.85em;">
                                                        <?php echo $value ? esc_html__('Yes', 'fp-performance-suite') : esc_html__('No', 'fp-performance-suite'); ?>
                                                    </span>
                                                <?php elseif (is_array($value)) : ?>
                                                    <?php if (empty($value)) : ?>
                                                        <span style="color: #999; font-style: italic;"><?php esc_html_e('None', 'fp-performance-suite'); ?></span>
                                                    <?php else : ?>
                                                        <ul style="margin-left: 20px; font-size: 0.85em; list-style: disc;">
                                                            <?php foreach ($value as $subKey => $subValue) : ?>
                                                                <li>
                                                                    <?php if (!is_numeric($subKey)) : ?>
                                                                        <?php echo esc_html(ucfirst(str_replace('_', ' ', (string) $subKey))); ?>: 
                                                                    <?php endif; ?>
                                                                    <?php if (is_bool($subValue)) : ?>
                                                                        <span class="fp-ps-badge <?php echo $subValue ? 'green' : 'gray'; ?>" style="font-size: 0.8em;">
                                                                            <?php echo $subValue ? esc_html__('Yes', 'fp-performance-suite') : esc_html__('No', 'fp-performance-suite'); ?>
                                                                        </span>
                                                                    <?php elseif (is_array($subValue)) : ?>
                                                                        <span style="color: #666;"><?php echo esc_html(count($subValue) . ' ' . __('items', 'fp-performance-suite')); ?></span>
                                                                    <?php else : ?>
                                                                        <span style="color: #2271b1; font-weight: 500;"><?php echo esc_html($subValue); ?></span>
                                                                    <?php endif; ?>
                                                                </li>
                                                            <?php endforeach; ?>
                                                        </ul>
                                                    <?php endif; ?>
                                                <?php elseif (is_numeric($value)) : ?>
                                                    <span style="color: #2271b1; font-weight: 500;"><?php echo esc_html($value); ?></span>
                                                <?php else : ?>
                                                    <span style="color: #2271b1; font-weight: 500;"><?php echo esc_html($value); ?></span>
                                                <?php endif; ?>
                                            </li>
                                        <?php endforeach; ?>
                                    </ul>
                                <?php elseif (is_numeric($config)) : ?>
                                    <span style="color: #2271b1; font-weight: 500;"><?php echo esc_html($config); ?></span>
                                <?php else : ?>
                                    <span style="color: #2271b1; font-weight: 500;"><?php echo esc_html($config); ?></span>
                                <?php endif; ?>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                    <div class="fp-ps-actions">
                        <button type="button" class="button button-primary" data-fp-preset="<?php echo esc_attr($key); ?>" data-fp-nonce="<?php echo esc_attr($nonce); ?>" data-risk="amber"><?php esc_html_e('Apply Preset', 'fp-performance-suite'); ?></button>
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
