<?php

define('ABSPATH', __DIR__ . '/../');

define('WP_CONTENT_DIR', __DIR__ . '/../wp-content');

if (!defined('HOUR_IN_SECONDS')) {
    define('HOUR_IN_SECONDS', 3600);
}
if (!defined('DAY_IN_SECONDS')) {
    define('DAY_IN_SECONDS', 86400);
}
if (!defined('WEEK_IN_SECONDS')) {
    define('WEEK_IN_SECONDS', 604800);
}

if (!function_exists('__')) {
    function __($text, $domain = null)
    {
        return $text;
    }
}

if (!function_exists('add_action')) {
    function add_action($hook, $callback, $priority = 10, $accepted_args = 1)
    {
        $GLOBALS['__hooks'][$hook][] = $callback;
    }
}

if (!function_exists('add_filter')) {
    function add_filter($hook, $callback, $priority = 10, $accepted_args = 1)
    {
        add_action($hook, $callback, $priority, $accepted_args);
    }
}

if (!function_exists('has_filter')) {
    function has_filter($hook, $callback = false)
    {
        if (empty($GLOBALS['__hooks'][$hook])) {
            return false;
        }
        if (false === $callback) {
            return true;
        }
        foreach ($GLOBALS['__hooks'][$hook] as $registered) {
            if ($registered === $callback) {
                return true;
            }
        }
        return false;
    }
}

if (!function_exists('apply_filters')) {
    function apply_filters($hook, $value)
    {
        if (!empty($GLOBALS['__hooks'][$hook])) {
            foreach ($GLOBALS['__hooks'][$hook] as $callback) {
                $value = call_user_func($callback, $value);
            }
        }
        return $value;
    }
}

if (!function_exists('esc_html__')) {
    function esc_html__($text, $domain = null)
    {
        return $text;
    }
}

if (!function_exists('wp_parse_args')) {
    function wp_parse_args($args, $defaults = [])
    {
        return array_merge($defaults, $args);
    }
}

if (!function_exists('sanitize_key')) {
    function sanitize_key($key)
    {
        return preg_replace('/[^a-z0-9_\-]/', '', strtolower($key));
    }
}

if (!function_exists('sanitize_text_field')) {
    function sanitize_text_field($text)
    {
        return trim((string) $text);
    }
}

if (!function_exists('update_option')) {
    function update_option($name, $value)
    {
        $GLOBALS['__wp_options'][$name] = $value;
        return true;
    }
}

if (!function_exists('get_option')) {
    function get_option($name, $default = false)
    {
        return $GLOBALS['__wp_options'][$name] ?? $default;
    }
}

if (!function_exists('has_action')) {
    function has_action($hook, $callback = false)
    {
        return has_filter($hook, $callback);
    }
}

if (!function_exists('wp_json_encode')) {
    function wp_json_encode($data, $flags = 0)
    {
        return json_encode($data, $flags);
    }
}

if (!function_exists('delete_transient')) {
    function delete_transient($name)
    {
        unset($GLOBALS['__transients'][$name]);
    }
}

if (!function_exists('wp_schedule_event')) {
    function wp_schedule_event($timestamp, $recurrence, $hook)
    {
        $GLOBALS['__cron'][$hook] = ['timestamp' => $timestamp, 'recurrence' => $recurrence];
        return true;
    }
}

if (!function_exists('wp_next_scheduled')) {
    function wp_next_scheduled($hook)
    {
        return isset($GLOBALS['__cron'][$hook]) ? $GLOBALS['__cron'][$hook]['timestamp'] : false;
    }
}

if (!function_exists('wp_clear_scheduled_hook')) {
    function wp_clear_scheduled_hook($hook)
    {
        unset($GLOBALS['__cron'][$hook]);
    }
}

if (!function_exists('number_format_i18n')) {
    function number_format_i18n($number, $decimals = 0)
    {
        return number_format($number, $decimals);
    }
}

require_once __DIR__ . '/../src/Services/Cache/PageCache.php';
require_once __DIR__ . '/../src/Services/Cache/Headers.php';
require_once __DIR__ . '/../src/Services/Assets/Optimizer.php';
require_once __DIR__ . '/../src/Services/Media/WebPConverter.php';
require_once __DIR__ . '/../src/Services/DB/Cleaner.php';
require_once __DIR__ . '/../src/Services/Score/Scorer.php';
require_once __DIR__ . '/../src/Utils/Fs.php';
require_once __DIR__ . '/../src/Utils/Htaccess.php';
require_once __DIR__ . '/../src/Utils/Env.php';
require_once __DIR__ . '/../src/Utils/Semaphore.php';
