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

if (!function_exists('do_action')) {
    function do_action($hook, ...$args)
    {
        if (!empty($GLOBALS['__hooks'][$hook])) {
            foreach ($GLOBALS['__hooks'][$hook] as $callback) {
                call_user_func_array($callback, $args);
            }
        }
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

if (!function_exists('wp_unslash')) {
    function wp_unslash($value)
    {
        if (is_array($value)) {
            return array_map('wp_unslash', $value);
        }

        return stripslashes((string) $value);
    }
}

if (!function_exists('esc_url_raw')) {
    function esc_url_raw($url)
    {
        return trim((string) $url);
    }
}

if (!function_exists('is_admin')) {
    function is_admin()
    {
        return false;
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

if (!function_exists('delete_option')) {
    function delete_option($name)
    {
        unset($GLOBALS['__wp_options'][$name]);
        return true;
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

if (!function_exists('is_main_query')) {
    function is_main_query()
    {
        return true;
    }
}

if (!function_exists('is_user_logged_in')) {
    function is_user_logged_in()
    {
        return false;
    }
}

if (!function_exists('wp_cache_get_cookies_values')) {
    function wp_cache_get_cookies_values()
    {
        return '';
    }
}

if (!function_exists('home_url')) {
    function home_url($path = '')
    {
        $base = $GLOBALS['__home_url'] ?? 'https://example.com';
        $base = rtrim((string) $base, '/');

        if ('' === $path) {
            return $base;
        }

        return $base . '/' . ltrim((string) $path, '/');
    }
}

if (!function_exists('wp_parse_url')) {
    function wp_parse_url($url, $component = -1)
    {
        return parse_url($url, $component);
    }
}

if (!function_exists('trailingslashit')) {
    function trailingslashit($string)
    {
        return rtrim($string, "/\\") . '/';
    }
}

if (!function_exists('wp_register_style')) {
    function wp_register_style($handle, $src, $deps = [], $ver = false, $media = 'all')
    {
        $GLOBALS['__registered_styles'][$handle] = [
            'handle' => $handle,
            'src' => $src,
            'deps' => $deps,
            'ver' => $ver,
            'media' => $media,
        ];

        return true;
    }
}

if (!function_exists('wp_enqueue_style')) {
    function wp_enqueue_style($handle)
    {
        $GLOBALS['__enqueued_styles'][] = $handle;
    }
}

if (!function_exists('wp_dequeue_style')) {
    function wp_dequeue_style($handle)
    {
        $GLOBALS['__dequeued_styles'][] = $handle;
        return true;
    }
}

if (!function_exists('wp_register_script')) {
    function wp_register_script($handle, $src, $deps = [], $ver = false, $in_footer = false)
    {
        $GLOBALS['__registered_scripts'][$handle] = [
            'handle' => $handle,
            'src' => $src,
            'deps' => $deps,
            'ver' => $ver,
            'in_footer' => $in_footer,
        ];

        return true;
    }
}

if (!function_exists('wp_enqueue_script')) {
    function wp_enqueue_script($handle)
    {
        $GLOBALS['__enqueued_scripts'][] = $handle;
    }
}

if (!function_exists('wp_dequeue_script')) {
    function wp_dequeue_script($handle)
    {
        $GLOBALS['__dequeued_scripts'][] = $handle;
        return true;
    }
}

if (!function_exists('wp_upload_dir')) {
    function wp_upload_dir()
    {
        $base = sys_get_temp_dir() . '/fp-performance-suite-uploads';

        if (!is_dir($base)) {
            mkdir($base, 0777, true);
        }

        return [
            'basedir' => $base,
            'baseurl' => 'https://example.com/wp-content/uploads',
        ];
    }
}

if (!function_exists('wp_mkdir_p')) {
    function wp_mkdir_p($dir)
    {
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }
        return true;
    }
}

if (!class_exists('WP_Dependencies')) {
    class WP_Dependencies
    {
        /** @var array<int,string> */
        public $queue = [];

        /** @var array<string,object> */
        public $registered = [];

        /** @var string */
        public $base_url = '';

        /** @var string */
        public $default_version = '';

        /** @var string */
        public $content_url = '';
    }
}

if (!class_exists('_WP_Dependency')) {
    class _WP_Dependency
    {
        /** @var string */
        public $handle;

        /** @var string */
        public $src;

        /** @var array<int,string> */
        public $deps;

        /** @var mixed */
        public $ver;

        /** @var array<string,mixed> */
        public $extra = [];

        public function __construct(string $handle, string $src = '', array $deps = [], $ver = false)
        {
            $this->handle = $handle;
            $this->src = $src;
            $this->deps = $deps;
            $this->ver = $ver;
        }
    }
}

if (!class_exists('WP_Styles')) {
    class WP_Styles extends WP_Dependencies
    {
    }
}

if (!class_exists('WP_Scripts')) {
    class WP_Scripts extends WP_Dependencies
    {
    }
}

if (!function_exists('get_transient')) {
    function get_transient($name)
    {
        return $GLOBALS['__transients'][$name] ?? false;
    }
}

if (!function_exists('set_transient')) {
    function set_transient($name, $value, $expiration = 0)
    {
        $GLOBALS['__transients'][$name] = $value;
        return true;
    }
}

if (!function_exists('delete_transient')) {
    function delete_transient($name)
    {
        unset($GLOBALS['__transients'][$name]);
        return true;
    }
}

if (!function_exists('wp_delete_post')) {
    function wp_delete_post($post_id, $force_delete = false)
    {
        $GLOBALS['__deleted_posts'][] = ['id' => (int) $post_id, 'force' => (bool) $force_delete];
        return true;
    }
}

if (!function_exists('wp_delete_comment')) {
    function wp_delete_comment($comment_id, $force_delete = false)
    {
        $GLOBALS['__deleted_comments'][] = ['id' => (int) $comment_id, 'force' => (bool) $force_delete];
        return true;
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
