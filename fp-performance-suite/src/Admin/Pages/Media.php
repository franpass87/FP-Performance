<?php

namespace FP\PerfSuite\Admin\Pages;

use FP\PerfSuite\Services\Media\WebPConverter;

use function __;
use function checked;
use function esc_attr;
use function esc_html;
use function esc_html__;
use function esc_html_e;
use function wp_nonce_field;
use function wp_unslash;
use function wp_verify_nonce;
use function number_format_i18n;
use function printf;

class Media extends AbstractPage
{
    public function slug(): string
    {
        return 'fp-performance-suite-media';
    }

    public function title(): string
    {
        return __('Media Optimization', 'fp-performance-suite');
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
            'breadcrumbs' => [__('Optimization', 'fp-performance-suite'), __('Media', 'fp-performance-suite')],
        ];
    }

    protected function content(): string
    {
        $converter = $this->container->get(WebPConverter::class);
        $message = '';
        $bulkResult = null;
        if ('POST' === $_SERVER['REQUEST_METHOD'] && isset($_POST['fp_ps_media_nonce']) && wp_verify_nonce(wp_unslash($_POST['fp_ps_media_nonce']), 'fp-ps-media')) {
            if (isset($_POST['save_webp'])) {
                $converter->update([
                    'enabled' => !empty($_POST['webp_enabled']),
                    'quality' => (int) ($_POST['webp_quality'] ?? 82),
                    'keep_original' => !empty($_POST['keep_original']),
                    'lossy' => !empty($_POST['lossy']),
                    'auto_deliver' => !empty($_POST['auto_deliver']),
                ]);
                $message = __('WebP settings saved.', 'fp-performance-suite');
            }
            if (isset($_POST['bulk_convert'])) {
                $limit = (int) ($_POST['bulk_limit'] ?? 20);
                $offset = (int) ($_POST['bulk_offset'] ?? 0);
                $bulkResult = $converter->bulkConvert($limit, $offset);
                if (!empty($bulkResult['queued'])) {
                    $message = __('Bulk conversion queued in the background.', 'fp-performance-suite');
                } else {
                    $message = __('Bulk conversion completed.', 'fp-performance-suite');
                }
            }
        }
        $settings = $converter->settings();
        $status = $converter->status();
        ob_start();
        ?>
        <?php if ($message) : ?>
            <div class="notice notice-success"><p><?php echo esc_html($message); ?></p></div>
        <?php endif; ?>
        <section class="fp-ps-card">
            <h2><?php esc_html_e('WebP Conversion', 'fp-performance-suite'); ?></h2>
            <form method="post">
                <?php wp_nonce_field('fp-ps-media', 'fp_ps_media_nonce'); ?>
                <input type="hidden" name="save_webp" value="1" />
                <label class="fp-ps-toggle">
                    <span class="info">
                        <strong><?php esc_html_e('Enable WebP on upload', 'fp-performance-suite'); ?></strong>
                        <span class="fp-ps-risk-indicator green" title="<?php esc_attr_e('Rischio basso - Sicuro da attivare', 'fp-performance-suite'); ?>"></span>
                        <small><?php esc_html_e('Automatically convert uploaded images to WebP format', 'fp-performance-suite'); ?></small>
                    </span>
                    <input type="checkbox" name="webp_enabled" value="1" <?php checked($settings['enabled']); ?> data-risk="green" />
                </label>
                <label class="fp-ps-toggle">
                    <span class="info">
                        <strong><?php esc_html_e('Auto-deliver WebP images', 'fp-performance-suite'); ?></strong>
                        <span class="fp-ps-risk-indicator green" title="<?php esc_attr_e('Rischio basso - Sicuro da attivare', 'fp-performance-suite'); ?>"></span>
                        <small><?php esc_html_e('Automatically serve WebP to compatible browsers (30-40% smaller)', 'fp-performance-suite'); ?></small>
                    </span>
                    <input type="checkbox" name="auto_deliver" value="1" <?php checked($settings['auto_deliver']); ?> data-risk="green" />
                </label>
                <p>
                    <label for="webp_quality"><?php esc_html_e('Quality (0-100)', 'fp-performance-suite'); ?></label>
                    <input type="number" name="webp_quality" id="webp_quality" value="<?php echo esc_attr((string) $settings['quality']); ?>" min="10" max="100" />
                </p>
                <label class="fp-ps-toggle">
                    <span class="info">
                        <strong><?php esc_html_e('Keep original files', 'fp-performance-suite'); ?></strong>
                        <span class="fp-ps-risk-indicator amber" title="<?php esc_attr_e('Rischio medio - Testare prima di attivare', 'fp-performance-suite'); ?>"></span>
                    </span>
                    <input type="checkbox" name="keep_original" value="1" <?php checked($settings['keep_original']); ?> data-risk="amber" />
                </label>
                <label class="fp-ps-toggle">
                    <span class="info">
                        <strong><?php esc_html_e('Use lossy compression', 'fp-performance-suite'); ?></strong>
                        <span class="fp-ps-risk-indicator amber" title="<?php esc_attr_e('Rischio medio - Testare prima di attivare', 'fp-performance-suite'); ?>"></span>
                    </span>
                    <input type="checkbox" name="lossy" value="1" <?php checked($settings['lossy']); ?> data-risk="amber" />
                </label>
                <p>
                    <button type="submit" class="button button-primary"><?php esc_html_e('Save Media Settings', 'fp-performance-suite'); ?></button>
                </p>
            </form>
            <p class="description"><?php printf(esc_html__('Current WebP coverage: %s%%', 'fp-performance-suite'), number_format_i18n($status['coverage'], 2)); ?></p>
        </section>
        <section class="fp-ps-card">
            <h2><?php esc_html_e('Bulk Convert Library', 'fp-performance-suite'); ?></h2>
            <form method="post">
                <?php wp_nonce_field('fp-ps-media', 'fp_ps_media_nonce'); ?>
                <input type="hidden" name="bulk_convert" value="1" />
                <p>
                    <label for="bulk_limit"><?php esc_html_e('Items per batch', 'fp-performance-suite'); ?></label>
                    <input type="number" name="bulk_limit" id="bulk_limit" value="20" min="5" max="200" />
                </p>
                <p>
                    <label for="bulk_offset"><?php esc_html_e('Offset', 'fp-performance-suite'); ?></label>
                    <input type="number" name="bulk_offset" id="bulk_offset" value="0" min="0" />
                </p>
                <p>
                    <button type="submit" class="button" data-risk="amber"><?php esc_html_e('Run Bulk Conversion', 'fp-performance-suite'); ?></button>
                </p>
            </form>
            <?php if ($bulkResult) : ?>
                <?php if (!empty($bulkResult['queued'])) : ?>
                    <p><?php printf(esc_html__('%d items queued for background conversion.', 'fp-performance-suite'), (int) ($bulkResult['total'] ?? 0)); ?></p>
                <?php else : ?>
                    <p><?php printf(esc_html__('%1$d items processed out of %2$d.', 'fp-performance-suite'), (int) $bulkResult['converted'], (int) $bulkResult['total']); ?></p>
                <?php endif; ?>
            <?php endif; ?>
        </section>
        <?php
        return (string) ob_get_clean();
    }
}
