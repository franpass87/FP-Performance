<?php

namespace FP\PerfSuite\Services\Assets;

use FP\PerfSuite\Utils\Logger;

/**
 * Lazy Load Manager
 *
 * Adds native lazy loading attributes to images and iframes
 * for improved performance and PageSpeed scores.
 *
 * @author Francesco Passeri
 * @link https://francescopasseri.com
 */
class LazyLoadManager
{
    private const OPTION = 'fp_ps_lazy_load';

    /**
     * Register hooks
     */
    public function register(): void
    {
        if (!is_admin() && $this->isEnabled()) {
            // CRITICAL: Skip lazy loading on checkout (payment logos must be visible!)
            if ($this->shouldSkipPage()) {
                return;
            }

            // Image lazy loading
            if ($this->getSetting('images', true)) {
                add_filter('wp_get_attachment_image_attributes', [$this, 'addLazyLoadToImage'], 10, 3);
                add_filter('get_avatar', [$this, 'addLazyLoadToAvatar'], 10, 5);
            }

            // Iframe lazy loading
            if ($this->getSetting('iframes', true)) {
                add_filter('the_content', [$this, 'addLazyLoadToIframes'], 999);
                add_filter('widget_text', [$this, 'addLazyLoadToIframes'], 999);
            }

            // Exclude above-the-fold images if configured
            if ($this->getSetting('skip_first', 0) > 0) {
                add_action('wp_head', [$this, 'addSkipFirstScript']);
            }

            Logger::debug('LazyLoadManager registered');
        }
    }

    /**
     * Check if lazy loading should be skipped on this page
     *
     * @return bool
     */
    private function shouldSkipPage(): bool
    {
        // Skip on WooCommerce checkout (payment logos critical!)
        if (function_exists('is_checkout') && is_checkout()) {
            return true;
        }

        // Skip on cart (coupon images, etc)
        if (function_exists('is_cart') && is_cart()) {
            return true;
        }

        // Check URL patterns for payment/forms
        if (isset($_SERVER['REQUEST_URI'])) {
            $requestUri = sanitize_text_field(wp_unslash($_SERVER['REQUEST_URI']));
            
            $criticalPatterns = [
                '/checkout',
                '/payment',
                '/stripe',
                '/paypal',
            ];

            foreach ($criticalPatterns as $pattern) {
                if (strpos($requestUri, $pattern) !== false) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Add loading="lazy" to images
     */
    public function addLazyLoadToImage(array $attr, $attachment, $size): array
    {
        // Skip if already set
        if (isset($attr['loading'])) {
            return $attr;
        }

        // Skip if in exclusion list
        if ($this->shouldSkipImage($attr)) {
            return $attr;
        }

        // Add native lazy loading
        $attr['loading'] = 'lazy';

        // Add decoding hint for better performance
        if (!isset($attr['decoding'])) {
            $attr['decoding'] = 'async';
        }

        return $attr;
    }

    /**
     * Add lazy loading to avatar images
     */
    public function addLazyLoadToAvatar(string $avatar, $id_or_email, $size, $default, $alt): string
    {
        // Skip if already has loading attribute
        if (strpos($avatar, 'loading=') !== false) {
            return $avatar;
        }

        // Add loading="lazy" to img tag
        $avatar = str_replace('<img ', '<img loading="lazy" decoding="async" ', $avatar);

        return $avatar;
    }

    /**
     * Add lazy loading to iframes in content
     */
    public function addLazyLoadToIframes(string $content): string
    {
        // Skip if no iframes
        if (strpos($content, '<iframe') === false) {
            return $content;
        }

        // Add loading="lazy" to iframes that don't have it
        $content = preg_replace_callback(
            '/<iframe([^>]*)>/i',
            function ($matches) {
                $attrs = $matches[1];
                
                // Skip if already has loading attribute
                if (stripos($attrs, 'loading=') !== false) {
                    return $matches[0];
                }

                // Skip if in exclusion list (e.g., YouTube consent forms)
                $exclusions = $this->getSetting('iframe_exclusions', []);
                foreach ($exclusions as $pattern) {
                    if (!empty($pattern) && stripos($attrs, $pattern) !== false) {
                        return $matches[0];
                    }
                }

                // Add loading="lazy"
                return '<iframe loading="lazy"' . $attrs . '>';
            },
            $content
        );

        return $content;
    }

    /**
     * Check if image should be skipped from lazy loading
     */
    private function shouldSkipImage(array $attr): bool
    {
        // Check exclusion classes
        if (isset($attr['class'])) {
            $excludeClasses = $this->getSetting('exclude_classes', []);
            foreach ($excludeClasses as $class) {
                if (!empty($class) && strpos($attr['class'], $class) !== false) {
                    return true;
                }
            }
        }

        // Check if it's a logo or icon (common exclusions)
        if (isset($attr['class'])) {
            $autoExclude = ['logo', 'site-logo', 'custom-logo', 'icon', 'emoji'];
            foreach ($autoExclude as $keyword) {
                if (strpos($attr['class'], $keyword) !== false) {
                    return true;
                }
            }
        }

        // Skip very small images (likely icons)
        if (isset($attr['width'], $attr['height'])) {
            $width = (int) $attr['width'];
            $height = (int) $attr['height'];
            $minSize = $this->getSetting('min_size', 100);
            
            if ($width < $minSize || $height < $minSize) {
                return true;
            }
        }

        return false;
    }

    /**
     * Add script to skip first N images (above-the-fold optimization)
     */
    public function addSkipFirstScript(): void
    {
        $skipCount = (int) $this->getSetting('skip_first', 0);
        
        if ($skipCount < 1) {
            return;
        }

        ?>
        <script>
        /* FP Performance Suite - Skip first <?php echo $skipCount; ?> image(s) from lazy load */
        (function() {
            if ('loading' in HTMLImageElement.prototype) {
                var images = document.querySelectorAll('img[loading="lazy"]');
                for (var i = 0; i < Math.min(<?php echo $skipCount; ?>, images.length); i++) {
                    images[i].loading = 'eager';
                }
            }
        })();
        </script>
        <?php
    }

    /**
     * Check if lazy loading is enabled
     */
    public function isEnabled(): bool
    {
        $settings = $this->getSettings();
        return !empty($settings['enabled']);
    }

    /**
     * Get all settings
     */
    public function getSettings(): array
    {
        $defaults = [
            'enabled' => true,
            'images' => true,
            'iframes' => true,
            'skip_first' => 1, // Skip first image (usually hero)
            'min_size' => 100, // Min dimension in pixels
            'exclude_classes' => [], // CSS classes to exclude
            'iframe_exclusions' => ['youtube.com/consent'], // Iframe patterns to skip
        ];

        $settings = get_option(self::OPTION, []);
        return is_array($settings) ? array_merge($defaults, $settings) : $defaults;
    }

    /**
     * Get specific setting
     */
    private function getSetting(string $key, $default = null)
    {
        $settings = $this->getSettings();
        return $settings[$key] ?? $default;
    }

    /**
     * Update settings
     */
    public function updateSettings(array $settings): bool
    {
        $current = $this->getSettings();
        $updated = array_merge($current, $settings);

        $result = update_option(self::OPTION, $updated);

        if ($result) {
            Logger::info('LazyLoad settings updated', $updated);
            do_action('fp_ps_lazy_load_updated', $updated);
        }

        return $result;
    }

    /**
     * Get status for admin display
     */
    public function status(): array
    {
        $settings = $this->getSettings();
        
        return [
            'enabled' => $this->isEnabled(),
            'images_enabled' => !empty($settings['images']),
            'iframes_enabled' => !empty($settings['iframes']),
            'skip_first' => $settings['skip_first'],
            'exclusions_count' => count($settings['exclude_classes']),
        ];
    }
}
