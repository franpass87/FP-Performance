<?php

namespace FP\PerfSuite\Services\Reports;

use FP\PerfSuite\Core\Logging\LoggerInterface;
use FP\PerfSuite\Core\Options\OptionsRepositoryInterface;
use FP\PerfSuite\Plugin;
use FP\PerfSuite\Services\Score\Scorer;
use FP\PerfSuite\Utils\Logger as StaticLogger;

/**
 * Scheduled Performance Reports
 *
 * Sends periodic email reports about site performance
 *
 * @author Francesco Passeri
 * @link https://francescopasseri.com
 */
class ScheduledReports
{
    private const OPTION = 'fp_ps_reports';
    private const CRON_HOOK = 'fp_ps_send_report';
    private ?OptionsRepositoryInterface $optionsRepo = null;
    private ?LoggerInterface $logger = null;
    
    /**
     * Costruttore
     * 
     * @param OptionsRepositoryInterface|null $optionsRepo Repository opzionale per gestione opzioni
     * @param LoggerInterface|null $logger Logger opzionale per logging
     */
    public function __construct(?OptionsRepositoryInterface $optionsRepo = null, ?LoggerInterface $logger = null)
    {
        $this->optionsRepo = $optionsRepo;
        $this->logger = $logger;
    }
    
    /**
     * Helper per logging con fallback
     * 
     * @param string $level Log level
     * @param string $message Message
     * @param array $context Context
     * @param \Throwable|null $exception Optional exception
     */
    private function log(string $level, string $message, array $context = [], ?\Throwable $exception = null): void
    {
        if ($this->logger !== null) {
            if ($exception !== null && method_exists($this->logger, $level)) {
                $this->logger->$level($message, $context, $exception);
            } else {
                $this->logger->$level($message, $context);
            }
        } else {
            StaticLogger::$level($message, $context);
        }
    }
    
    /**
     * Helper per ottenere opzioni con fallback
     * 
     * @param string $key Chiave opzione
     * @param mixed $default Valore di default
     * @return mixed Valore opzione
     */
    private function getOption(string $key, $default = null)
    {
        if ($this->optionsRepo !== null) {
            return $this->optionsRepo->get($key, $default);
        }
        return get_option($key, $default);
    }
    
    /**
     * Helper per salvare opzioni con fallback
     * 
     * @param string $key Chiave opzione
     * @param mixed $value Valore opzione
     * @param bool $autoload Se autoload
     * @return bool True se salvato con successo
     */
    private function setOption(string $key, $value, bool $autoload = true): bool
    {
        if ($this->optionsRepo !== null) {
            return $this->optionsRepo->set($key, $value, $autoload);
        }
        return update_option($key, $value, $autoload);
    }

    /**
     * Register hooks
     */
    public function register(): void
    {
        add_action(self::CRON_HOOK, [$this, 'sendReport']);
        add_filter('cron_schedules', [$this, 'addSchedules']);

        $this->maybeSchedule();
    }

    /**
     * Add custom cron schedules
     */
    public function addSchedules(array $schedules): array
    {
        $schedules['fp_ps_weekly'] = [
            'interval' => WEEK_IN_SECONDS,
            'display' => __('Once Weekly (FP Performance)', 'fp-performance-suite'),
        ];

        $schedules['fp_ps_monthly'] = [
            'interval' => 30 * DAY_IN_SECONDS,
            'display' => __('Once Monthly (FP Performance)', 'fp-performance-suite'),
        ];

        return $schedules;
    }

    /**
     * Get settings
     */
    public function settings(): array
    {
        $defaults = [
            'enabled' => false,
            'frequency' => 'weekly', // daily, weekly, monthly
            'recipient' => get_option('admin_email'), // WordPress core option
            'include_suggestions' => true,
            'include_optimizations' => true,
            'include_metrics' => true,
        ];

        return wp_parse_args($this->getOption(self::OPTION, []), $defaults);
    }

    /**
     * Update settings
     */
    public function update(array $settings): void
    {
        $current = $this->settings();

        $new = [
            'enabled' => !empty($settings['enabled']),
            'frequency' => in_array($settings['frequency'] ?? '', ['daily', 'weekly', 'monthly'])
                ? $settings['frequency']
                : $current['frequency'],
            'recipient' => sanitize_email($settings['recipient'] ?? $current['recipient']),
            'include_suggestions' => !empty($settings['include_suggestions']),
            'include_optimizations' => !empty($settings['include_optimizations']),
            'include_metrics' => !empty($settings['include_metrics']),
        ];

        $this->setOption(self::OPTION, $new);

        // Reschedule
        $this->maybeSchedule(true);

        $this->log('info', 'Scheduled reports settings updated', $new);
        
        // FIX: Reinizializza il servizio per applicare immediatamente le modifiche
        $this->forceInit();
    }
    
    /**
     * Forza l'inizializzazione del servizio
     * FIX: Ricarica le impostazioni e reinizializza il servizio
     */
    public function forceInit(): void
    {
        // Rimuovi hook esistenti
        remove_action(self::CRON_HOOK, [$this, 'sendReport']);
        remove_filter('cron_schedules', [$this, 'addSchedules']);
        
        // Reinizializza
        $this->register();
    }

    /**
     * Schedule reports if enabled
     */
    public function maybeSchedule(bool $force = false): void
    {
        if ($force) {
            wp_clear_scheduled_hook(self::CRON_HOOK);
        }

        $settings = $this->settings();

        if (!$settings['enabled']) {
            wp_clear_scheduled_hook(self::CRON_HOOK);
            return;
        }

        if (wp_next_scheduled(self::CRON_HOOK)) {
            return; // Already scheduled
        }

        $recurrence = $this->getRecurrence($settings['frequency']);
        wp_schedule_event(time() + HOUR_IN_SECONDS, $recurrence, self::CRON_HOOK);

        $this->log('info', 'Scheduled performance reports', [
            'frequency' => $settings['frequency'],
            'recipient' => $settings['recipient'],
        ]);
    }

    /**
     * Get WordPress cron recurrence name
     */
    private function getRecurrence(string $frequency): string
    {
        switch ($frequency) {
            case 'daily':
                return 'daily';
            case 'monthly':
                return 'fp_ps_monthly';
            case 'weekly':
            default:
                return 'fp_ps_weekly';
        }
    }

    /**
     * Send performance report
     */
    public function sendReport(): void
    {
        $settings = $this->settings();

        if (!$settings['enabled']) {
            return;
        }

        try {
            $report = $this->generateReport($settings);

            $sent = wp_mail(
                $settings['recipient'],
                $report['subject'],
                $report['body'],
                $report['headers']
            );

            if ($sent) {
                $this->log('info', 'Performance report sent', [
                    'recipient' => $settings['recipient'],
                ]);
                $this->setOption('fp_ps_last_report', [
                    'time' => time(),
                    'recipient' => $settings['recipient'],
                ]);
            } else {
                $this->log('error', 'Failed to send performance report');
            }
        } catch (\Throwable $e) {
            $this->log('error', 'Failed to generate performance report', [], $e);
        }
    }

    /**
     * Generate report content
     */
    private function generateReport(array $settings): array
    {
        $container = Plugin::container();
        $scorer = $container->get(Scorer::class);
        $score = $scorer->calculate();
        $optimizations = $scorer->activeOptimizations();

        $siteName = get_bloginfo('name');
        $siteUrl = home_url();
        $date = date_i18n(get_option('date_format'));

        $subject = sprintf(
            __('[%s] Performance Report - %s', 'fp-performance-suite'),
            $siteName,
            $date
        );

        // Build HTML email
        $body = $this->renderEmailTemplate([
            'site_name' => $siteName,
            'site_url' => $siteUrl,
            'date' => $date,
            'score' => $score,
            'optimizations' => $optimizations,
            'settings' => $settings,
        ]);

        $headers = [
            'Content-Type: text/html; charset=UTF-8',
            sprintf('From: %s <%s>', $siteName, get_option('admin_email')),
        ];

        return [
            'subject' => $subject,
            'body' => $body,
            'headers' => $headers,
        ];
    }

    /**
     * Render email template
     */
    private function renderEmailTemplate(array $data): string
    {
        $score = $data['score'];
        $scoreColor = $score['total'] >= 80 ? '#10b981' : ($score['total'] >= 60 ? '#f59e0b' : '#ef4444');

        ob_start();
        ?>
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title><?php echo esc_html($data['site_name']); ?> - Performance Report</title>
        </head>
        <body style="margin: 0; padding: 0; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif; background-color: #f3f4f6;">
            <table width="100%" cellpadding="0" cellspacing="0" style="background-color: #f3f4f6; padding: 40px 20px;">
                <tr>
                    <td align="center">
                        <table width="600" cellpadding="0" cellspacing="0" style="background-color: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
                            <!-- Header -->
                            <tr>
                                <td style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 30px; text-align: center;">
                                    <h1 style="color: #ffffff; margin: 0; font-size: 24px;">
                                        📊 Performance Report
                                    </h1>
                                    <p style="color: #e0e7ff; margin: 10px 0 0 0; font-size: 14px;">
                                        <?php echo esc_html($data['site_name']); ?>
                                    </p>
                                </td>
                            </tr>
                            
                            <!-- Date -->
                            <tr>
                                <td style="padding: 20px 30px; border-bottom: 1px solid #e5e7eb;">
                                    <p style="margin: 0; color: #6b7280; font-size: 14px;">
                                        <?php echo esc_html($data['date']); ?>
                                    </p>
                                </td>
                            </tr>
                            
                            <!-- Performance Score -->
                            <tr>
                                <td style="padding: 30px;">
                                    <h2 style="margin: 0 0 20px 0; color: #111827; font-size: 20px;">
                                        Performance Score
                                    </h2>
                                    <div style="text-align: center; margin: 20px 0;">
                                        <div style="display: inline-block; background-color: <?php echo $scoreColor; ?>; color: #ffffff; font-size: 48px; font-weight: bold; width: 120px; height: 120px; line-height: 120px; border-radius: 60px;">
                                            <?php echo $score['total']; ?>
                                        </div>
                                        <p style="margin: 10px 0 0 0; color: #6b7280; font-size: 14px;">
                                            <?php
                                            if ($score['total'] >= 80) {
                                                echo '✅ Excellent performance';
                                            } elseif ($score['total'] >= 60) {
                                                echo '⚠️ Good, but could be better';
                                            } else {
                                                echo '🔴 Needs improvement';
                                            }
                                            ?>
                                        </p>
                                    </div>
                                </td>
                            </tr>
                            
                            <?php if ($data['settings']['include_metrics']) : ?>
                            <!-- Breakdown -->
                            <tr>
                                <td style="padding: 0 30px 30px 30px;">
                                    <h3 style="margin: 0 0 15px 0; color: #111827; font-size: 16px;">
                                        Score Breakdown
                                    </h3>
                                    <table width="100%" cellpadding="8" cellspacing="0" style="border-collapse: collapse;">
                                        <?php foreach ($score['breakdown'] as $label => $points) : ?>
                                        <tr>
                                            <td style="border-bottom: 1px solid #e5e7eb; color: #374151; font-size: 14px;">
                                                <?php echo esc_html($label); ?>
                                            </td>
                                            <td align="right" style="border-bottom: 1px solid #e5e7eb; color: #6b7280; font-size: 14px; font-weight: 600;">
                                                <?php echo $points; ?>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </table>
                                </td>
                            </tr>
                            <?php endif; ?>
                            
                            <?php if ($data['settings']['include_optimizations'] && !empty($data['optimizations'])) : ?>
                            <!-- Active Optimizations -->
                            <tr>
                                <td style="padding: 0 30px 30px 30px;">
                                    <h3 style="margin: 0 0 15px 0; color: #111827; font-size: 16px;">
                                        Active Optimizations
                                    </h3>
                                    <ul style="margin: 0; padding: 0 0 0 20px;">
                                        <?php foreach ($data['optimizations'] as $opt) : ?>
                                        <li style="color: #374151; font-size: 14px; margin-bottom: 8px;">
                                            <?php echo esc_html($opt); ?>
                                        </li>
                                        <?php endforeach; ?>
                                    </ul>
                                </td>
                            </tr>
                            <?php endif; ?>
                            
                            <?php if ($data['settings']['include_suggestions'] && !empty($score['suggestions'])) : ?>
                            <!-- Suggestions -->
                            <tr>
                                <td style="padding: 0 30px 30px 30px;">
                                    <h3 style="margin: 0 0 15px 0; color: #111827; font-size: 16px;">
                                        💡 Suggestions for Improvement
                                    </h3>
                                    <div style="background-color: #fef3c7; border-left: 4px solid #f59e0b; padding: 15px; border-radius: 4px;">
                                        <ul style="margin: 0; padding: 0 0 0 20px;">
                                            <?php foreach ($score['suggestions'] as $suggestion) : ?>
                                            <li style="color: #92400e; font-size: 14px; margin-bottom: 8px;">
                                                <?php echo esc_html($suggestion); ?>
                                            </li>
                                            <?php endforeach; ?>
                                        </ul>
                                    </div>
                                </td>
                            </tr>
                            <?php endif; ?>
                            
                            <!-- CTA -->
                            <tr>
                                <td style="padding: 0 30px 30px 30px; text-align: center;">
                                    <a href="<?php echo esc_url(admin_url('admin.php?page=fp-performance-suite')); ?>" style="display: inline-block; background-color: #667eea; color: #ffffff; text-decoration: none; padding: 12px 30px; border-radius: 6px; font-weight: 600; font-size: 14px;">
                                        Visualizza Overview Completa
                                    </a>
                                </td>
                            </tr>
                            
                            <!-- Footer -->
                            <tr>
                                <td style="padding: 20px 30px; background-color: #f9fafb; border-top: 1px solid #e5e7eb; text-align: center;">
                                    <p style="margin: 0; color: #6b7280; font-size: 12px;">
                                        Generated by <strong>FP Performance Suite</strong><br>
                                        <a href="<?php echo esc_url($data['site_url']); ?>" style="color: #667eea; text-decoration: none;">
                                            <?php echo esc_html($data['site_name']); ?>
                                        </a>
                                    </p>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
        </body>
        </html>
        <?php
        return ob_get_clean();
    }

    /**
     * Send test report
     */
    public function sendTestReport(string $recipient): array
    {
        try {
            $testSettings = array_merge($this->settings(), [
                'recipient' => $recipient,
                'enabled' => true,
            ]);

            $report = $this->generateReport($testSettings);

            $sent = wp_mail(
                $recipient,
                '[TEST] ' . $report['subject'],
                $report['body'],
                $report['headers']
            );

            if ($sent) {
                return [
                    'success' => true,
                    'message' => __('Test report sent successfully', 'fp-performance-suite'),
                ];
            }

            return [
                'success' => false,
                'error' => __('Failed to send test report', 'fp-performance-suite'),
            ];
        } catch (\Throwable $e) {
            $this->log('error', 'Failed to send test report', [], $e);
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }
}
