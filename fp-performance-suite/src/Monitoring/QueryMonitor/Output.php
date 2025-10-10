<?php

namespace FP\PerfSuite\Monitoring\QueryMonitor;

/**
 * Query Monitor Output for FP Performance Suite
 */
class Output extends \QM_Output_Html
{
    public function __construct(\QM_Collector $collector)
    {
        parent::__construct($collector);
        add_filter('qm/output/menus', [$this, 'adminMenu'], 60);
    }

    public function name(): string
    {
        return __('FP Performance', 'fp-performance-suite');
    }

    public function output(): void
    {
        $data = $this->collector->get_data();

        if (isset($data['error'])) {
            echo '<div class="qm qm-non-tabular">';
            echo '<p>' . esc_html__('Error loading FP Performance data:', 'fp-performance-suite') . ' ' . esc_html($data['error']) . '</p>';
            echo '</div>';
            return;
        }

        echo '<div class="qm qm-non-tabular">';

        // Cache Section
        if (isset($data['cache'])) {
            echo '<section>';
            echo '<h3>📦 ' . esc_html__('Page Cache', 'fp-performance-suite') . '</h3>';
            echo '<table class="qm-sortable">';
            echo '<thead><tr><th>' . esc_html__('Metric', 'fp-performance-suite') . '</th><th>' . esc_html__('Value', 'fp-performance-suite') . '</th></tr></thead>';
            echo '<tbody>';

            $cacheHit = $data['cache']['hit'] ? '✅ HIT' : '❌ MISS';
            $cacheStatus = $data['cache']['enabled'] ? '✅ Enabled' : '❌ Disabled';

            echo '<tr><td>Status</td><td>' . $cacheStatus . '</td></tr>';
            echo '<tr><td>Current Request</td><td>' . $cacheHit . '</td></tr>';
            echo '<tr><td>Cached Files</td><td>' . number_format($data['cache']['files']) . '</td></tr>';
            echo '</tbody></table>';
            echo '</section>';
        }

        // Assets Section
        if (isset($data['assets'])) {
            echo '<section>';
            echo '<h3>🎨 ' . esc_html__('Asset Optimization', 'fp-performance-suite') . '</h3>';
            echo '<table class="qm-sortable">';
            echo '<thead><tr><th>' . esc_html__('Optimization', 'fp-performance-suite') . '</th><th>' . esc_html__('Status', 'fp-performance-suite') . '</th></tr></thead>';
            echo '<tbody>';

            foreach ($data['assets'] as $key => $value) {
                $status = $value ? '✅ Enabled' : '❌ Disabled';
                $label = ucwords(str_replace('_', ' ', $key));
                echo '<tr><td>' . esc_html($label) . '</td><td>' . $status . '</td></tr>';
            }

            echo '</tbody></table>';
            echo '</section>';
        }

        // WebP Section
        if (isset($data['webp'])) {
            echo '<section>';
            echo '<h3>🖼️ ' . esc_html__('WebP Conversion', 'fp-performance-suite') . '</h3>';
            echo '<table class="qm-sortable">';
            echo '<thead><tr><th>' . esc_html__('Metric', 'fp-performance-suite') . '</th><th>' . esc_html__('Value', 'fp-performance-suite') . '</th></tr></thead>';
            echo '<tbody>';

            $webpStatus = $data['webp']['enabled'] ? '✅ Enabled' : '❌ Disabled';
            echo '<tr><td>Status</td><td>' . $webpStatus . '</td></tr>';
            echo '<tr><td>Coverage</td><td>' . $data['webp']['coverage'] . '%</td></tr>';

            echo '</tbody></table>';
            echo '</section>';
        }

        // Performance Section
        if (isset($data['performance'])) {
            echo '<section>';
            echo '<h3>⚡ ' . esc_html__('Performance', 'fp-performance-suite') . '</h3>';
            echo '<table class="qm-sortable">';
            echo '<thead><tr><th>' . esc_html__('Metric', 'fp-performance-suite') . '</th><th>' . esc_html__('Value', 'fp-performance-suite') . '</th></tr></thead>';
            echo '<tbody>';

            $memoryUsage = $data['performance']['memory_usage'] / 1024 / 1024;
            $memoryPeak = $data['performance']['memory_peak'] / 1024 / 1024;
            $memoryLimit = $data['performance']['memory_limit'] / 1024 / 1024;
            $memoryPercent = $memoryLimit > 0 ? ($memoryPeak / $memoryLimit) * 100 : 0;

            echo '<tr><td>Memory Usage</td><td>' . number_format($memoryUsage, 2) . ' MB</td></tr>';
            echo '<tr><td>Memory Peak</td><td>' . number_format($memoryPeak, 2) . ' MB</td></tr>';
            echo '<tr><td>Memory Limit</td><td>' . number_format($memoryLimit, 2) . ' MB</td></tr>';
            echo '<tr><td>Memory Usage %</td><td>' . number_format($memoryPercent, 1) . '%</td></tr>';

            echo '</tbody></table>';
            echo '</section>';
        }

        // Custom Metrics
        if (isset($data['custom_metrics']) && !empty($data['custom_metrics'])) {
            echo '<section>';
            echo '<h3>📊 ' . esc_html__('Custom Metrics', 'fp-performance-suite') . '</h3>';

            // Timers
            if (isset($data['custom_metrics']['timers'])) {
                echo '<h4>' . esc_html__('Timers', 'fp-performance-suite') . '</h4>';
                echo '<table class="qm-sortable">';
                echo '<thead><tr><th>' . esc_html__('Name', 'fp-performance-suite') . '</th><th>' . esc_html__('Duration', 'fp-performance-suite') . '</th></tr></thead>';
                echo '<tbody>';

                foreach ($data['custom_metrics']['timers'] as $name => $timer) {
                    if (isset($timer['duration'])) {
                        echo '<tr><td>' . esc_html($name) . '</td><td>' . number_format($timer['duration'] * 1000, 2) . ' ms</td></tr>';
                    }
                }

                echo '</tbody></table>';
            }

            // Other metrics
            $otherMetrics = array_diff_key($data['custom_metrics'], ['timers' => true]);
            if (!empty($otherMetrics)) {
                echo '<table class="qm-sortable">';
                echo '<thead><tr><th>' . esc_html__('Metric', 'fp-performance-suite') . '</th><th>' . esc_html__('Value', 'fp-performance-suite') . '</th></tr></thead>';
                echo '<tbody>';

                foreach ($otherMetrics as $key => $value) {
                    $formattedValue = is_scalar($value) ? $value : wp_json_encode($value, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
                    echo '<tr><td>' . esc_html($key) . '</td><td><pre style="margin:0;white-space:pre-wrap;">' . esc_html($formattedValue) . '</pre></td></tr>';
                }

                echo '</tbody></table>';
            }

            echo '</section>';
        }

        echo '</div>';
    }

    public function adminMenu(array $menu): array
    {
        $data = $this->collector->get_data();

        if (isset($data['error'])) {
            $label = __('FP Perf: Error', 'fp-performance-suite');
        } else {
            $cacheHit = isset($data['cache']['hit']) && $data['cache']['hit'] ? '✓' : '✗';
            $label = sprintf('FP Perf: %s', $cacheHit);
        }

        $menu[$this->collector->id()] = $this->menu([
            'title' => esc_html($label),
        ]);

        return $menu;
    }
}
