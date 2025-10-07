<?php

namespace FP\PerfSuite\Services\Assets;

use function add_filter;
use function max;
use function remove_action;
use function remove_filter;

/**
 * WordPress Core Optimizations
 * 
 * Handles WordPress-specific optimizations like removing emoji scripts
 * and controlling the heartbeat API
 * 
 * @author Francesco Passeri
 * @link https://francescopasseri.com
 */
class WordPressOptimizer
{
    /**
     * Disable WordPress emoji scripts and styles
     */
    public function disableEmojis(): void
    {
        remove_action('wp_head', 'print_emoji_detection_script', 7);
        remove_action('wp_print_styles', 'print_emoji_styles');
        remove_action('admin_print_scripts', 'print_emoji_detection_script');
        remove_action('admin_print_styles', 'print_emoji_styles');
        remove_filter('the_content_feed', 'wp_staticize_emoji');
        remove_filter('comment_text_rss', 'wp_staticize_emoji');
        remove_filter('wp_mail', 'wp_staticize_emoji_for_email');
    }

    /**
     * Configure WordPress heartbeat interval
     * 
     * @param array<string, mixed> $settings Current heartbeat settings
     * @param int $interval Desired interval in seconds
     * @return array<string, mixed> Modified settings
     */
    public function configureHeartbeat(array $settings, int $interval): array
    {
        $settings['interval'] = max(15, $interval);
        return $settings;
    }

    /**
     * Register heartbeat filter with given interval
     * 
     * @param int $interval Heartbeat interval in seconds
     */
    public function registerHeartbeat(int $interval): void
    {
        add_filter('heartbeat_settings', function(array $settings) use ($interval) {
            return $this->configureHeartbeat($settings, $interval);
        });
    }
}