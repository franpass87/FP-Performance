<?php

namespace FP\PerfSuite\Admin\Pages\AIConfig\Components;

use function esc_html;
use function esc_html_e;
use function get_option;

/**
 * Renderizza il confronto Prima/Dopo delle modifiche
 * 
 * @package FP\PerfSuite\Admin\Pages\AIConfig\Components
 * @author Francesco Passeri
 */
class PreviewComparison
{
    /**
     * Renderizza la tabella di confronto
     */
    public function render(array $analysis, array $config): string
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
}
















