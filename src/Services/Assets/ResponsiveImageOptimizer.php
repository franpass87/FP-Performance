<?php

namespace FP\PerfSuite\Services\Assets;

use FP\PerfSuite\Core\Options\OptionsRepositoryInterface;
use FP\PerfSuite\Utils\Logger;
use FP\PerfSuite\Services\Assets\ResponsiveImageOptimizer\ImageSizeOptimizer;
use FP\PerfSuite\Services\Assets\ResponsiveImageOptimizer\ImageSizeGenerator;
use FP\PerfSuite\Services\Assets\ResponsiveImageOptimizer\ImageMetadataManager;

/**
 * Responsive Image Optimizer
 *
 * Automatically optimizes image delivery by detecting display dimensions
 * and serving appropriately sized images to reduce bandwidth and improve LCP.
 *
 * Features:
 * - Auto-detection of CSS display dimensions
 * - Dynamic generation of optimized image sizes
 * - Smart caching for generated sizes
 * - Integration with responsive image delivery
 * - Lighthouse "Improve image delivery" optimization
 *
 * @author Francesco Passeri
 * @link https://francescopasseri.com
 */
class ResponsiveImageOptimizer
{
    private const OPTION = 'fp_ps_responsive_images';
    
    private ImageMetadataManager $metadataManager;
    private ImageSizeGenerator $sizeGenerator;
    private ImageSizeOptimizer $sizeOptimizer;
    
    /** @var OptionsRepositoryInterface|null Options repository (injected) */
    private ?OptionsRepositoryInterface $optionsRepo = null;

    /**
     * Constructor
     * 
     * @param OptionsRepositoryInterface|null $optionsRepo Options repository (optional for backward compatibility)
     */
    public function __construct(?OptionsRepositoryInterface $optionsRepo = null)
    {
        $this->optionsRepo = $optionsRepo;
        $this->metadataManager = new ImageMetadataManager();
        $this->sizeGenerator = new ImageSizeGenerator($this->metadataManager);
        $this->sizeOptimizer = new ImageSizeOptimizer($this->metadataManager, $this->sizeGenerator);
    }

    /**
     * Register hooks
     */
    public function register(): void
    {
        if (!is_admin() && $this->isEnabled()) {
            // Optimize attachment image sources
            add_filter('wp_get_attachment_image_src', [$this, 'optimizeImageSrc'], 10, 4);
            
            // Optimize srcset for responsive images
            add_filter('wp_calculate_image_srcset', [$this, 'optimizeImageSrcset'], 10, 5);
            
            // Optimize images in post content
            add_filter('the_content', [$this, 'optimizeContentImages'], 20);
            
            // Add JavaScript for dimension detection - PRIORITÀ MEDIA per responsive images
            add_action('wp_footer', [$this, 'addDimensionDetectionScript'], 12);

            Logger::debug('ResponsiveImageOptimizer registered');
        }
    }

    /**
     * Optimize single image source based on display dimensions
     */
    public function optimizeImageSrc($image, $attachment_id, $size, $icon)
    {
        if (!is_array($image) || empty($image[0])) {
            return $image;
        }

        // Skip if not enabled for this image
        if (!$this->shouldOptimizeImage($image[0], $attachment_id)) {
            return $image;
        }

        // Get optimal size based on context
        $optimalSize = $this->getOptimalImageSize($image[0], $attachment_id, $size);
        
        if ($optimalSize && $optimalSize !== $image[0]) {
            $image[0] = $optimalSize;
            
            Logger::debug('Image optimized', [
                'original' => basename($image[0]),
                'optimized' => basename($optimalSize),
                'attachment_id' => $attachment_id
            ]);
        }

        return $image;
    }

    /**
     * Optimize image srcset for responsive delivery
     */
    public function optimizeImageSrcset($sources, $size_array, $image_src, $image_meta, $attachment_id)
    {
        if (empty($sources) || !is_array($sources)) {
            return $sources;
        }

        // Skip if not enabled
        if (!$this->shouldOptimizeImage($image_src, $attachment_id)) {
            return $sources;
        }

        $optimizedSources = [];
        
        foreach ($sources as $width => $source) {
            if (empty($source['url'])) {
                continue;
            }

            // Get optimal size for this width
            $optimalUrl = $this->sizeOptimizer->getOptimalSizeForWidth($source['url'], $attachment_id, $width);
            
            if ($optimalUrl && $optimalUrl !== $source['url']) {
                $source['url'] = $optimalUrl;
            }

            $optimizedSources[$width] = $source;
        }

        return $optimizedSources;
    }

    /**
     * Optimize images in post content
     */
    public function optimizeContentImages(string $content): string
    {
        if (empty($content) || strpos($content, '<img') === false) {
            return $content;
        }

        // Match img tags and optimize them
        $content = preg_replace_callback(
            '/<img([^>]+)>/i',
            [$this, 'optimizeSingleContentImage'],
            $content
        );

        return $content;
    }

    /**
     * Optimize single content image
     */
    private function optimizeSingleContentImage(array $matches): string
    {
        $imgTag = $matches[0];
        $attrs = $matches[1];

        // Extract src attribute
        if (!preg_match('/\ssrc\s*=\s*["\']([^"\']+)["\']/i', $attrs, $srcMatch)) {
            return $imgTag;
        }

        $src = $srcMatch[1];
        $attachmentId = $this->metadataManager->getAttachmentIdFromUrl($src);

        if ($attachmentId <= 0) {
            return $imgTag;
        }

        // Skip if not enabled for this image
        if (!$this->shouldOptimizeImage($src, $attachmentId)) {
            return $imgTag;
        }

        // Get optimal size
        $optimalSrc = $this->sizeOptimizer->getOptimalImageSize($src, $attachmentId);
        
        if ($optimalSrc && $optimalSrc !== $src) {
            // Replace src in attributes
            $newAttrs = preg_replace(
                '/\ssrc\s*=\s*["\'][^"\']+["\']/i',
                ' src="' . esc_attr($optimalSrc) . '"',
                $attrs
            );
            
            return '<img' . $newAttrs . '>';
        }

        return $imgTag;
    }

    // Metodo getOptimalImageSize() rimosso - ora gestito da ImageSizeOptimizer

    /**
     * Get target dimensions based on context
     */
    private function getTargetDimensions(string $url, $attachmentId, $requestedSize = null): ?array
    {
        // Try to get dimensions from CSS context
        $cssDimensions = $this->getCSSDimensions($url);
        if ($cssDimensions) {
            return $cssDimensions;
        }

        // Fallback to requested size
        if ($requestedSize) {
            if (is_string($requestedSize)) {
                $sizes = $this->getImageSizes();
                if (isset($sizes[$requestedSize])) {
                    return [
                        'width' => $sizes[$requestedSize]['width'],
                        'height' => $sizes[$requestedSize]['height']
                    ];
                }
            } elseif (is_array($requestedSize) && count($requestedSize) === 2) {
                return [
                    'width' => $requestedSize[0],
                    'height' => $requestedSize[1]
                ];
            }
        }

        // Default fallback - use medium size
        $sizes = $this->getImageSizes();
        if (isset($sizes['medium'])) {
            return [
                'width' => $sizes['medium']['width'],
                'height' => $sizes['medium']['height']
            ];
        }

        return null;
    }

    /**
     * Get CSS dimensions for an image (placeholder for future JS integration)
     */
    private function getCSSDimensions(string $url): ?array
    {
        // This would be populated by JavaScript dimension detection
        // For now, return null to use fallback methods
        return null;
    }

    /**
     * Find suitable existing size
     */
    private function findSuitableSize(array $metadata, int $targetWidth, int $targetHeight): ?string
    {
        if (empty($metadata['sizes'])) {
            return null;
        }

        $bestSize = null;
        $bestScore = PHP_INT_MAX;

        foreach ($metadata['sizes'] as $sizeName => $sizeData) {
            $width = $sizeData['width'] ?? 0;
            $height = $sizeData['height'] ?? 0;

            if ($width <= 0 || $height <= 0) {
                continue;
            }

            // Calculate how close this size is to target
            $widthDiff = abs($width - $targetWidth);
            $heightDiff = abs($height - $targetHeight);
            $score = $widthDiff + $heightDiff;

            // Prefer sizes that are close but not smaller than target
            if ($width >= $targetWidth && $height >= $targetHeight && $score < $bestScore) {
                $bestSize = $sizeName;
                $bestScore = $score;
            }
        }

        if (null === $bestSize) {
            return null;
        }

        $uploadDir = wp_get_upload_dir();
        $baseUrl   = rtrim($uploadDir['baseurl'], '/');

        $relativeDir = '';
        if (!empty($metadata['file'])) {
            $dir = dirname($metadata['file']);
            if ('.' !== $dir) {
                $relativeDir = '/' . ltrim($dir, '/');
            }
        }

        $sizeFile = $metadata['sizes'][$bestSize]['file'] ?? '';
        if ('' === $sizeFile) {
            return null;
        }

        return $baseUrl . $relativeDir . '/' . ltrim($sizeFile, '/');
    }

    /**
     * Generate optimal size if needed
     */
    private function generateOptimalSize(string $originalUrl, $attachmentId, int $width, int $height): ?string
    {
        // Get original file path
        $uploadDir = wp_get_upload_dir();
        $baseUrl = $uploadDir['baseurl'];
        
        if (strpos($originalUrl, $baseUrl) === false) {
            return null;
        }

        $relativePath = str_replace($baseUrl, '', $originalUrl);
        $originalPath = $uploadDir['basedir'] . $relativePath;

        if (!file_exists($originalPath)) {
            return null;
        }

        // Generate new size name
        $pathInfo = pathinfo($originalPath);
        $newFileName = $pathInfo['filename'] . '-' . $width . 'x' . $height . '.' . $pathInfo['extension'];
        $newPath = $pathInfo['dirname'] . '/' . $newFileName;
        $newUrl = $baseUrl . str_replace($uploadDir['basedir'], '', $newPath);

        // Check if already exists
        if (file_exists($newPath)) {
            return $newUrl;
        }

        // Generate the new size
        if ($this->generateImageSize($originalPath, $newPath, $width, $height)) {
            // Update attachment metadata
            $this->updateAttachmentMetadata($attachmentId, $width, $height, $newFileName);
            
            return $newUrl;
        }

        return null;
    }

    /**
     * Generate image size using WordPress image editor
     */
    private function generateImageSize(string $originalPath, string $newPath, int $width, int $height): bool
    {
        $editor = wp_get_image_editor($originalPath);
        
        if (is_wp_error($editor)) {
            Logger::error('Failed to load image editor', ['path' => $originalPath, 'error' => $editor->get_error_message()]);
            return false;
        }

        $editor->resize($width, $height, true);
        $result = $editor->save($newPath);

        if (is_wp_error($result)) {
            Logger::error('Failed to save resized image', ['path' => $newPath, 'error' => $result->get_error_message()]);
            return false;
        }

        Logger::debug('Generated new image size', [
            'original' => basename($originalPath),
            'new' => basename($newPath),
            'dimensions' => $width . 'x' . $height
        ]);

        return true;
    }

    /**
     * Update attachment metadata with new size
     */
    private function updateAttachmentMetadata($attachmentId, int $width, int $height, string $fileName): void
    {
        $metadata = wp_get_attachment_metadata($attachmentId);
        
        if (!is_array($metadata)) {
            return;
        }

        // Add new size to metadata
        $sizeName = 'fp_optimized_' . $width . 'x' . $height;
        $metadata['sizes'][$sizeName] = [
            'file' => $fileName,
            'width' => $width,
            'height' => $height,
            'mime-type' => wp_get_image_mime($this->buildAbsolutePathFromMetadata($metadata, $fileName)) ?: 'image/jpeg'
        ];

        wp_update_attachment_metadata($attachmentId, $metadata);
    }

    /**
     * Helper to build the absolute path for a derived image.
     */
    private function buildAbsolutePathFromMetadata(array $metadata, string $fileName): string
    {
        $uploadDir = wp_get_upload_dir();
        $baseDir   = rtrim($uploadDir['basedir'], DIRECTORY_SEPARATOR);

        $relativeDir = '';
        if (!empty($metadata['file'])) {
            $dir = dirname($metadata['file']);
            if ('.' !== $dir) {
                $relativeDir = DIRECTORY_SEPARATOR . ltrim($dir, DIRECTORY_SEPARATOR);
            }
        }

        return $baseDir . $relativeDir . DIRECTORY_SEPARATOR . ltrim($fileName, DIRECTORY_SEPARATOR);
    }

    /**
     * Get optimal size for specific width
     */
    private function getOptimalSizeForWidth(string $url, $attachmentId, int $width): ?string
    {
        $metadata = wp_get_attachment_metadata($attachmentId);
        if (!is_array($metadata)) {
            return null;
        }

        // Find closest size for this width
        $bestSize = null;
        $bestDiff = PHP_INT_MAX;

        foreach ($metadata['sizes'] as $sizeName => $sizeData) {
            $sizeWidth = $sizeData['width'] ?? 0;
            $diff = abs($sizeWidth - $width);

            if ($diff < $bestDiff && $sizeWidth >= $width) {
                $bestSize = $sizeName;
                $bestDiff = $diff;
            }
        }

        if ($bestSize) {
            $uploadDir = wp_get_upload_dir();
            $baseUrl = $uploadDir['baseurl'];
            $baseDir = $uploadDir['basedir'];
            
            $sizeData = $metadata['sizes'][$bestSize];
            $sizeUrl = $baseUrl . '/' . dirname($metadata['file']) . '/' . $sizeData['file'];
            
            return $sizeUrl;
        }

        return null;
    }

    /**
     * Check if image should be optimized
     */
    private function shouldOptimizeImage(string $url, $attachmentId): bool
    {
        // Skip external images
        $uploadDir = wp_get_upload_dir();
        if (strpos($url, $uploadDir['baseurl']) === false) {
            return false;
        }

        // Skip if disabled
        if (!$this->isEnabled()) {
            return false;
        }

        // Skip small images
        $metadata = wp_get_attachment_metadata($attachmentId);
        if (is_array($metadata)) {
            $width  = (int) ($metadata['width'] ?? 0);
            $height = (int) ($metadata['height'] ?? 0);

            $minWidth  = (int) $this->getSetting('min_width', 300);
            $minHeight = (int) $this->getSetting('min_height', 300);

            if ($minWidth > 0 && $width > 0 && $width < $minWidth) {
                return false;
            }

            if ($minHeight > 0 && $height > 0 && $height < $minHeight) {
                return false;
            }
        }

        return true;
    }

    /**
     * Get attachment ID from URL
     */
    private function getAttachmentIdFromUrl(string $url): int
    {
        global $wpdb;

        if (!isset($wpdb)) {
            return 0;
        }

        $normalizedUrl = $this->normalizeImageUrl($url);

        if (isset($this->attachmentIdCache[$normalizedUrl])) {
            return $this->attachmentIdCache[$normalizedUrl];
        }

        $cacheKey = 'attachment_id_' . md5($normalizedUrl);
        $cached = wp_cache_get($cacheKey, self::CACHE_GROUP);

        if (false !== $cached) {
            $this->attachmentIdCache[$normalizedUrl] = (int) $cached;
            return (int) $cached;
        }

        $filename = basename($normalizedUrl);

        $attachmentId = $wpdb->get_var($wpdb->prepare(
            "SELECT post_id FROM {$wpdb->postmeta} 
            WHERE meta_key = '_wp_attached_file' 
            AND meta_value LIKE %s 
            LIMIT 1",
            '%' . $wpdb->esc_like($filename)
        ));

        $attachmentId = $attachmentId ? (int) $attachmentId : 0;

        $this->attachmentIdCache[$normalizedUrl] = $attachmentId;
        wp_cache_set($cacheKey, $attachmentId, self::CACHE_GROUP, self::CACHE_EXPIRATION);

        return $attachmentId;
    }

    /**
     * Normalizza l'URL dell'immagine rimuovendo suffissi di dimensione.
     */
    private function normalizeImageUrl(string $url): string
    {
        return preg_replace('/-\d+x\d+(?=\.[a-z]{3,4}$)/i', '', $url) ?? $url;
    }

    /**
     * Get WordPress image sizes
     */
    private function getImageSizes(): array
    {
        global $_wp_additional_image_sizes;

        $sizes = [];

        // Get default WordPress sizes
        $default_sizes = ['thumbnail', 'medium', 'medium_large', 'large'];
        
        foreach ($default_sizes as $size) {
            $width = get_option("{$size}_size_w");
            $height = get_option("{$size}_size_h");
            if ((!$width && !$height) || (0 === (int) $width && 0 === (int) $height)) {
                continue;
            }

            if ($width || $height) {
                $sizes[$size] = [
                    'width' => (int) $width,
                    'height' => (int) $height,
                    'crop' => (bool) get_option("{$size}_crop")
                ];
            }
        }

        // Get additional registered sizes
        if (isset($_wp_additional_image_sizes) && is_array($_wp_additional_image_sizes)) {
            foreach ($_wp_additional_image_sizes as $name => $data) {
                $sizes[$name] = [
                    'width' => isset($data['width']) ? (int) $data['width'] : 0,
                    'height' => isset($data['height']) ? (int) $data['height'] : 0,
                    'crop' => isset($data['crop']) ? (bool) $data['crop'] : false
                ];
            }
        }

        return $sizes;
    }

    /**
     * Add JavaScript for dimension detection
     */
    public function addDimensionDetectionScript(): void
    {
        if (!$this->getSetting('js_detection', true)) {
            return;
        }
        ?>
        <script id="fp-responsive-images-detection">
        (function() {
            'use strict';
            
            // Detect image display dimensions and send to server
            function detectImageDimensions() {
                const images = document.querySelectorAll('img[data-fp-optimize]');
                
                images.forEach(function(img) {
                    const rect = img.getBoundingClientRect();
                    const computedStyle = window.getComputedStyle(img);
                    
                    // Get actual display dimensions
                    const displayWidth = Math.round(rect.width);
                    const displayHeight = Math.round(rect.height);
                    
                    // Send to server for optimization
                    if (displayWidth > 0 && displayHeight > 0) {
                        fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/x-www-form-urlencoded',
                            },
                            body: 'action=fp_ps_optimize_image&url=' + encodeURIComponent(img.src) + 
                                  '&width=' + displayWidth + '&height=' + displayHeight + 
                                  '&nonce=<?php echo wp_create_nonce('fp_ps_optimize_image'); ?>'
                        }).catch(function(error) {
                            console.debug('FP Performance: Image optimization request failed', error);
                        });
                    }
                });
            }
            
            // Run detection after page load
            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', detectImageDimensions);
            } else {
                detectImageDimensions();
            }
            
            // Re-run on resize
            let resizeTimeout;
            window.addEventListener('resize', function() {
                clearTimeout(resizeTimeout);
                resizeTimeout = setTimeout(detectImageDimensions, 250);
            });
        })();
        </script>
        <?php
    }

    /**
     * Check if responsive images are enabled
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
            'enabled' => false,
            'generate_sizes' => false,
            'js_detection' => false,
            'min_width' => 300,
            'min_height' => 300,
            'quality' => 85,
        ];

        $settings = $this->getOption(self::OPTION, []);
        return is_array($settings) ? array_merge($defaults, $settings) : $defaults;
    }

    /**
     * Get specific setting
     */
    private function getSetting(string $key, mixed $default = null): mixed
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
            Logger::info('Responsive images settings updated', $updated);
            do_action('fp_ps_responsive_images_updated', $updated);
            
            // FIX: Reinizializza il servizio per applicare immediatamente le modifiche
            $this->forceInit();
        }

        return $result;
    }
    
    /**
     * Forza l'inizializzazione del servizio
     * FIX: Ricarica le impostazioni e reinizializza il servizio
     */
    public function forceInit(): void
    {
        // Rimuovi hook esistenti
        remove_filter('wp_get_attachment_image_src', [$this, 'optimizeImageSrc'], 10);
        remove_filter('wp_calculate_image_srcset', [$this, 'optimizeImageSrcset'], 10);
        remove_filter('the_content', [$this, 'optimizeContentImages'], 20);
        remove_action('wp_footer', [$this, 'addDimensionDetectionScript'], 12);
        
        // Reinizializza
        $this->register();
    }

    /**
     * Get status for admin display
     */
    public function status(): array
    {
        $settings = $this->getSettings();
        
        return [
            'enabled' => $this->isEnabled(),
            'generate_sizes' => !empty($settings['generate_sizes']),
            'js_detection' => !empty($settings['js_detection']),
            'min_dimensions' => $settings['min_width'] . 'x' . $settings['min_height'],
            'quality' => $settings['quality'],
        ];
    }

    /**
     * Get option value (with fallback)
     * 
     * Note: WordPress core image size options (thumbnail_size_w, etc.) are accessed directly
     * via get_option() as they are WordPress core settings, not plugin settings.
     * 
     * @param string $key Option key
     * @param mixed $default Default value
     * @return mixed
     */
    private function getOption(string $key, $default = null)
    {
        if ($this->optionsRepo !== null) {
            return $this->optionsRepo->get($key, $default);
        }
        return get_option($key, $default);
    }
}

