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
use function sanitize_text_field;
use function sanitize_textarea_field;
use function wp_verify_nonce;
use function wp_unslash;
use function get_option;
use function update_option;

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
            'breadcrumbs' => [__('Ottimizzazione', 'fp-performance-suite'), __('Preset', 'fp-performance-suite')],
        ];
    }

    protected function content(): string
    {
        $manager = $this->container->get(PresetManager::class);
        $presets = $manager->presets();
        $active = $manager->getActivePreset();
        $nonce = wp_create_nonce('wp_rest');
        $customPresets = get_option('fp_ps_custom_presets', []);
        $scheduledPresets = get_option('fp_ps_scheduled_presets', []);
        $message = '';
        
        // Handle custom preset creation
        if ('POST' === $_SERVER['REQUEST_METHOD'] && isset($_POST['fp_ps_preset_nonce']) && wp_verify_nonce(wp_unslash($_POST['fp_ps_preset_nonce']), 'fp-ps-custom-preset')) {
            if (isset($_POST['create_preset'])) {
                $presetName = sanitize_text_field($_POST['preset_name'] ?? '');
                $presetDescription = sanitize_textarea_field(wp_unslash($_POST['preset_description'] ?? ''));
                
                if (!empty($presetName)) {
                    // Capture current settings as a preset
                    $currentSettings = [
                        'page_cache' => get_option('fp_ps_page_cache', []),
                        'browser_cache' => get_option('fp_ps_browser_cache', []),
                        'asset_optimizer' => get_option('fp_ps_asset_optimizer', []),
                        'webp' => get_option('fp_ps_webp', []),
                        'lazy_load' => get_option('fp_ps_lazy_load', []),
                    ];
                    
                    $customPresets[sanitize_key($presetName)] = [
                        'label' => $presetName,
                        'description' => $presetDescription,
                        'config' => $currentSettings,
                        'created' => time(),
                    ];
                    
                    update_option('fp_ps_custom_presets', $customPresets);
                    $message = __('Custom preset created successfully!', 'fp-performance-suite');
                }
            } elseif (isset($_POST['delete_preset'])) {
                $presetKey = sanitize_text_field($_POST['preset_key'] ?? '');
                if (isset($customPresets[$presetKey])) {
                    unset($customPresets[$presetKey]);
                    update_option('fp_ps_custom_presets', $customPresets);
                    $message = __('Custom preset deleted.', 'fp-performance-suite');
                }
            } elseif (isset($_POST['schedule_preset'])) {
                $scheduleData = [
                    'enabled' => !empty($_POST['schedule_enabled']),
                    'preset_morning' => sanitize_text_field($_POST['preset_morning'] ?? ''),
                    'preset_evening' => sanitize_text_field($_POST['preset_evening'] ?? ''),
                    'time_morning' => sanitize_text_field($_POST['time_morning'] ?? '09:00'),
                    'time_evening' => sanitize_text_field($_POST['time_evening'] ?? '18:00'),
                    'days' => array_map('sanitize_text_field', $_POST['schedule_days'] ?? []),
                ];
                update_option('fp_ps_scheduled_presets', $scheduleData);
                
                // Schedule/unschedule cron
                $this->manageScheduledPresets($scheduleData);
                
                $message = __('Preset schedule saved.', 'fp-performance-suite');
            }
        }
        
        ob_start();
        ?>
        <?php if ($message) : ?>
            <div class="notice notice-success"><p><?php echo esc_html($message); ?></p></div>
        <?php endif; ?>
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
        
        <!-- Custom Presets Section -->
        <section class="fp-ps-card" style="margin-top: 30px;">
            <h2><?php esc_html_e('Custom Presets', 'fp-performance-suite'); ?></h2>
            <p><?php esc_html_e('Create and manage your own custom preset configurations. Save current settings as a preset to quickly switch between different configurations.', 'fp-performance-suite'); ?></p>
            
            <?php if (!empty($customPresets)) : ?>
                <h3><?php esc_html_e('Your Custom Presets', 'fp-performance-suite'); ?></h3>
                <div class="fp-ps-grid three" style="margin-bottom: 30px;">
                    <?php foreach ($customPresets as $key => $preset) : ?>
                        <article class="fp-ps-card" style="border: 2px solid #2271b1;">
                            <header>
                                <h3><?php echo esc_html($preset['label']); ?></h3>
                                <span class="fp-ps-badge blue"><?php esc_html_e('Custom', 'fp-performance-suite'); ?></span>
                            </header>
                            <p><?php echo esc_html($preset['description'] ?: __('No description provided.', 'fp-performance-suite')); ?></p>
                            <p style="color: #666; font-size: 0.85em;">
                                <?php 
                                printf(
                                    esc_html__('Created: %s', 'fp-performance-suite'),
                                    esc_html(date_i18n(get_option('date_format'), $preset['created']))
                                );
                                ?>
                            </p>
                            <div class="fp-ps-actions">
                                <button type="button" class="button button-primary" data-fp-preset="custom_<?php echo esc_attr($key); ?>" data-fp-nonce="<?php echo esc_attr($nonce); ?>"><?php esc_html_e('Apply Preset', 'fp-performance-suite'); ?></button>
                                <form method="post" style="display: inline;">
                                    <?php wp_nonce_field('fp-ps-custom-preset', 'fp_ps_preset_nonce'); ?>
                                    <input type="hidden" name="preset_key" value="<?php echo esc_attr($key); ?>" />
                                    <button type="submit" name="delete_preset" class="button" onclick="return confirm('<?php esc_attr_e('Are you sure you want to delete this preset?', 'fp-performance-suite'); ?>');"><?php esc_html_e('Delete', 'fp-performance-suite'); ?></button>
                                </form>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
            
            <h3><?php esc_html_e('Create New Custom Preset', 'fp-performance-suite'); ?></h3>
            <form method="post">
                <?php wp_nonce_field('fp-ps-custom-preset', 'fp_ps_preset_nonce'); ?>
                <table class="form-table">
                    <tr>
                        <th scope="row">
                            <label for="preset_name"><?php esc_html_e('Preset Name', 'fp-performance-suite'); ?></label>
                        </th>
                        <td>
                            <input type="text" name="preset_name" id="preset_name" class="regular-text" required placeholder="<?php esc_attr_e('e.g., My Production Config', 'fp-performance-suite'); ?>">
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="preset_description"><?php esc_html_e('Description', 'fp-performance-suite'); ?></label>
                        </th>
                        <td>
                            <textarea name="preset_description" id="preset_description" rows="3" class="large-text" placeholder="<?php esc_attr_e('Optional: Describe when to use this preset...', 'fp-performance-suite'); ?>"></textarea>
                        </td>
                    </tr>
                </table>
                
                <div style="background: #e7f5ff; border-left: 4px solid #2271b1; padding: 15px; margin: 15px 0;">
                    <p style="margin: 0; font-weight: 600; color: #2271b1;"><?php esc_html_e('💡 How it works:', 'fp-performance-suite'); ?></p>
                    <ul style="margin: 10px 0 0 20px; color: #555;">
                        <li><?php esc_html_e('This will save ALL current plugin settings as a custom preset', 'fp-performance-suite'); ?></li>
                        <li><?php esc_html_e('You can then switch back to this configuration anytime', 'fp-performance-suite'); ?></li>
                        <li><?php esc_html_e('Perfect for testing different configurations or switching between dev/prod', 'fp-performance-suite'); ?></li>
                        <li><?php esc_html_e('Custom presets can be exported and shared with other sites', 'fp-performance-suite'); ?></li>
                    </ul>
                </div>
                
                <p>
                    <button type="submit" name="create_preset" class="button button-primary button-large">
                        <?php esc_html_e('Create Preset from Current Settings', 'fp-performance-suite'); ?>
                    </button>
                </p>
            </form>
        </section>
        
        <!-- Scheduled Preset Switching -->
        <section class="fp-ps-card" style="margin-top: 30px;">
            <h2><?php esc_html_e('⏰ Scheduled Preset Switching', 'fp-performance-suite'); ?></h2>
            <p><?php esc_html_e('Automatically switch between presets at specific times. Perfect for different configurations during business hours vs. off-hours.', 'fp-performance-suite'); ?></p>
            
            <form method="post">
                <?php wp_nonce_field('fp-ps-custom-preset', 'fp_ps_preset_nonce'); ?>
                
                <table class="form-table">
                    <tr>
                        <th scope="row">
                            <label for="schedule_enabled"><?php esc_html_e('Enable Scheduled Switching', 'fp-performance-suite'); ?></label>
                        </th>
                        <td>
                            <label>
                                <input type="checkbox" name="schedule_enabled" id="schedule_enabled" value="1" <?php checked($scheduledPresets['enabled'] ?? false); ?>>
                                <?php esc_html_e('Automatically switch presets based on schedule', 'fp-performance-suite'); ?>
                            </label>
                        </td>
                    </tr>
                </table>
                
                <h3><?php esc_html_e('Morning Configuration', 'fp-performance-suite'); ?></h3>
                <table class="form-table">
                    <tr>
                        <th scope="row">
                            <label for="preset_morning"><?php esc_html_e('Morning Preset', 'fp-performance-suite'); ?></label>
                        </th>
                        <td>
                            <select name="preset_morning" id="preset_morning" class="regular-text">
                                <option value=""><?php esc_html_e('-- Select Preset --', 'fp-performance-suite'); ?></option>
                                <?php foreach ($presets as $key => $preset) : ?>
                                    <option value="<?php echo esc_attr($key); ?>" <?php selected($scheduledPresets['preset_morning'] ?? '', $key); ?>>
                                        <?php echo esc_html($preset['label']); ?>
                                    </option>
                                <?php endforeach; ?>
                                <?php foreach ($customPresets as $key => $preset) : ?>
                                    <option value="custom_<?php echo esc_attr($key); ?>" <?php selected($scheduledPresets['preset_morning'] ?? '', 'custom_' . $key); ?>>
                                        <?php echo esc_html($preset['label']); ?> (Custom)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="time_morning"><?php esc_html_e('Switch Time', 'fp-performance-suite'); ?></label>
                        </th>
                        <td>
                            <input type="time" name="time_morning" id="time_morning" value="<?php echo esc_attr($scheduledPresets['time_morning'] ?? '09:00'); ?>">
                            <span class="description"><?php esc_html_e('Time to apply morning preset (24h format)', 'fp-performance-suite'); ?></span>
                        </td>
                    </tr>
                </table>
                
                <h3><?php esc_html_e('Evening Configuration', 'fp-performance-suite'); ?></h3>
                <table class="form-table">
                    <tr>
                        <th scope="row">
                            <label for="preset_evening"><?php esc_html_e('Evening Preset', 'fp-performance-suite'); ?></label>
                        </th>
                        <td>
                            <select name="preset_evening" id="preset_evening" class="regular-text">
                                <option value=""><?php esc_html_e('-- Select Preset --', 'fp-performance-suite'); ?></option>
                                <?php foreach ($presets as $key => $preset) : ?>
                                    <option value="<?php echo esc_attr($key); ?>" <?php selected($scheduledPresets['preset_evening'] ?? '', $key); ?>>
                                        <?php echo esc_html($preset['label']); ?>
                                    </option>
                                <?php endforeach; ?>
                                <?php foreach ($customPresets as $key => $preset) : ?>
                                    <option value="custom_<?php echo esc_attr($key); ?>" <?php selected($scheduledPresets['preset_evening'] ?? '', 'custom_' . $key); ?>>
                                        <?php echo esc_html($preset['label']); ?> (Custom)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="time_evening"><?php esc_html_e('Switch Time', 'fp-performance-suite'); ?></label>
                        </th>
                        <td>
                            <input type="time" name="time_evening" id="time_evening" value="<?php echo esc_attr($scheduledPresets['time_evening'] ?? '18:00'); ?>">
                            <span class="description"><?php esc_html_e('Time to apply evening preset (24h format)', 'fp-performance-suite'); ?></span>
                        </td>
                    </tr>
                </table>
                
                <h3><?php esc_html_e('Active Days', 'fp-performance-suite'); ?></h3>
                <table class="form-table">
                    <tr>
                        <th scope="row"><?php esc_html_e('Days of Week', 'fp-performance-suite'); ?></th>
                        <td>
                            <fieldset>
                                <?php
                                $days = [
                                    'monday' => __('Monday', 'fp-performance-suite'),
                                    'tuesday' => __('Tuesday', 'fp-performance-suite'),
                                    'wednesday' => __('Wednesday', 'fp-performance-suite'),
                                    'thursday' => __('Thursday', 'fp-performance-suite'),
                                    'friday' => __('Friday', 'fp-performance-suite'),
                                    'saturday' => __('Saturday', 'fp-performance-suite'),
                                    'sunday' => __('Sunday', 'fp-performance-suite'),
                                ];
                                $selectedDays = $scheduledPresets['days'] ?? array_keys($days);
                                foreach ($days as $key => $label) :
                                ?>
                                    <label style="display: inline-block; margin-right: 15px;">
                                        <input type="checkbox" name="schedule_days[]" value="<?php echo esc_attr($key); ?>" <?php checked(in_array($key, $selectedDays, true)); ?>>
                                        <?php echo esc_html($label); ?>
                                    </label>
                                <?php endforeach; ?>
                            </fieldset>
                            <p class="description"><?php esc_html_e('Select days when scheduled switching should be active', 'fp-performance-suite'); ?></p>
                        </td>
                    </tr>
                </table>
                
                <div style="background: #fff3cd; border-left: 4px solid #ffc107; padding: 15px; margin: 20px 0;">
                    <p style="margin: 0; font-weight: 600; color: #856404;"><?php esc_html_e('💡 Example Use Cases:', 'fp-performance-suite'); ?></p>
                    <ul style="margin: 10px 0 0 20px; color: #856404;">
                        <li><?php esc_html_e('Aggressive caching during business hours (9-18), lighter during off-hours', 'fp-performance-suite'); ?></li>
                        <li><?php esc_html_e('Heavy optimization for peak traffic times', 'fp-performance-suite'); ?></li>
                        <li><?php esc_html_e('Development preset during maintenance windows', 'fp-performance-suite'); ?></li>
                        <li><?php esc_html_e('Different settings for weekdays vs weekends', 'fp-performance-suite'); ?></li>
                    </ul>
                </div>
                
                <p>
                    <button type="submit" name="schedule_preset" class="button button-primary button-large">
                        <?php esc_html_e('Save Schedule', 'fp-performance-suite'); ?>
                    </button>
                </p>
            </form>
        </section>
        <?php
        return (string) ob_get_clean();
    }
    
    /**
     * Manage scheduled preset cron jobs
     */
    private function manageScheduledPresets(array $scheduleData): void
    {
        $hook_morning = 'fp_ps_switch_preset_morning';
        $hook_evening = 'fp_ps_switch_preset_evening';
        
        // Clear existing schedules
        wp_clear_scheduled_hook($hook_morning);
        wp_clear_scheduled_hook($hook_evening);
        
        if (!$scheduleData['enabled']) {
            return;
        }
        
        // Schedule morning switch
        if (!empty($scheduleData['preset_morning']) && !empty($scheduleData['time_morning'])) {
            list($hour, $minute) = explode(':', $scheduleData['time_morning']);
            $timestamp = strtotime("today {$hour}:{$minute}");
            if ($timestamp < time()) {
                $timestamp = strtotime("tomorrow {$hour}:{$minute}");
            }
            wp_schedule_event($timestamp, 'daily', $hook_morning, [$scheduleData['preset_morning']]);
        }
        
        // Schedule evening switch
        if (!empty($scheduleData['preset_evening']) && !empty($scheduleData['time_evening'])) {
            list($hour, $minute) = explode(':', $scheduleData['time_evening']);
            $timestamp = strtotime("today {$hour}:{$minute}");
            if ($timestamp < time()) {
                $timestamp = strtotime("tomorrow {$hour}:{$minute}");
            }
            wp_schedule_event($timestamp, 'daily', $hook_evening, [$scheduleData['preset_evening']]);
        }
    }
}
