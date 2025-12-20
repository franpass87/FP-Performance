<?php

namespace FP\PerfSuite\Admin\Pages\AIConfig\Components;

use function esc_html;
use function esc_html_e;
use function date_i18n;
use function get_option;
use function array_reverse;

/**
 * Renderizza la storia delle analisi
 * 
 * @package FP\PerfSuite\Admin\Pages\AIConfig\Components
 * @author Francesco Passeri
 */
class AnalysisHistory
{
    /**
     * Renderizza la lista della storia
     */
    public function render(array $history): string
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
}
















