<?php

namespace FP\PerfSuite\Services\Media;

use FP\PerfSuite\Services\Media\AVIF\AVIFImageConverter;
use FP\PerfSuite\Services\Media\AVIF\AVIFPathHelper;
use FP\PerfSuite\Utils\Logger;

use function add_action;
use function add_filter;
use function get_option;
use function update_option;
use function wp_parse_args;

/**
 * AVIF Converter Service
 *
 * Orchestrates AVIF conversion for WordPress media
 *
 * @package FP\PerfSuite\Services\Media
 * @author Francesco Passeri
 */
class AVIFConverter
{
    private const OPTION = 'fp_ps_avif';
    private const CONVERSION_META = '_fp_ps_avif_generated';

    private AVIFImageConverter $imageConverter;
    private AVIFPathHelper $pathHelper;

    public function __construct(
        ?AVIFImageConverter $imageConverter = null,
        ?AVIFPathHelper $pathHelper = null
    ) {
        $this->imageConverter = $imageConverter ?? new AVIFImageConverter();
        $this->pathHelper = $pathHelper ?? new AVIFPathHelper();
    }

    public function register(): void
    {
        $settings = $this->settings();

        if (!$settings['enabled']) {
            return;
        }

        // Only register if AVIF is available
        if (!$this->imageConverter->isAvailable()) {
            Logger::warning('AVIF conversion enabled but not available on this server');
            return;
        }

        add_filter('wp_generate_attachment_metadata', [$this, 'generateAVIF'], 10, 2);
        add_filter('wp_update_attachment_metadata', [$this, 'generateAVIF'], 10, 2);

        // Register automatic AVIF delivery if enabled
        if (!empty($settings['auto_deliver'])) {
            $this->registerDelivery();
        }
    }

    /**
     * Get current settings
     *
     * @return array{enabled:bool,quality:int,keep_original:bool,auto_deliver:bool,speed:int,strip_metadata:bool}
     */
    public function settings(): array
    {
        $defaults = [
            'enabled' => false,
            'quality' => 75,
            'keep_original' => true,
            'auto_deliver' => true,
            'speed' => 6, // Imagick only: 0-10 (higher = faster, lower quality)
            'strip_metadata' => false,
        ];
        return wp_parse_args(get_option(self::OPTION, []), $defaults);
    }

    /**
     * Update settings
     *
     * @param array $settings New settings
     */
    public function update(array $settings): void
    {
        $current = $this->settings();
        
        $quality = isset($settings['quality']) ? (int)$settings['quality'] : $current['quality'];
        $quality = max(1, min(100, $quality));

        $speed = isset($settings['speed']) ? (int)$settings['speed'] : $current['speed'];
        $speed = max(0, min(10, $speed));

        $new = [
            'enabled' => !empty($settings['enabled']),
            'quality' => $quality,
            'keep_original' => !empty($settings['keep_original']),
            'auto_deliver' => isset($settings['auto_deliver']) ? !empty($settings['auto_deliver']) : $current['auto_deliver'],
            'speed' => $speed,
            'strip_metadata' => !empty($settings['strip_metadata']),
        ];

        update_option(self::OPTION, $new);
    }

    /**
     * Generate AVIF on attachment upload/update
     *
     * @param array $metadata Attachment metadata
     * @param int|string $attachment_id Attachment ID
     * @return array Modified metadata
     */
    public function generateAVIF(array $metadata, int|string $attachment_id): array
    {
        // Ensure attachment_id is an integer (WordPress may pass it as string)
        $attachment_id = (int) $attachment_id;
        
        $settings = $this->settings();
        
        if (!$settings['enabled'] || !$this->imageConverter->isAvailable()) {
            return $metadata;
        }

        $file = get_attached_file($attachment_id);
        
        if (!$file || !file_exists($file)) {
            return $metadata;
        }

        // Convert main file
        $avifPath = $this->pathHelper->getAVIFPath($file);
        $converted = $this->imageConverter->convert($file, $avifPath, $settings);

        if ($converted) {
            update_post_meta($attachment_id, self::CONVERSION_META, '1');
            
            Logger::info('AVIF generated for attachment', [
                'id' => $attachment_id,
                'file' => basename($file),
            ]);
        }

        // Convert thumbnails
        if (!empty($metadata['sizes']) && is_array($metadata['sizes'])) {
            $uploadDir = wp_upload_dir();
            $basePath = trailingslashit(dirname($file));

            foreach ($metadata['sizes'] as $size => $sizeData) {
                if (empty($sizeData['file'])) {
                    continue;
                }

                $sizePath = $basePath . $sizeData['file'];
                $sizeAvifPath = $this->pathHelper->getAVIFPath($sizePath);
                
                $this->imageConverter->convert($sizePath, $sizeAvifPath, $settings);
            }
        }

        return $metadata;
    }

    /**
     * Check if AVIF conversion is available
     *
     * @return bool True if AVIF is available
     */
    public function isAvailable(): bool
    {
        return $this->imageConverter->isAvailable();
    }

    /**
     * Get conversion method info
     *
     * @return array{available:bool,method:string,version:string}
     */
    public function getInfo(): array
    {
        $method = $this->imageConverter->getMethod();
        $version = '';

        if ($method === 'gd') {
            $version = 'PHP ' . PHP_VERSION . ' (GD)';
        } elseif ($method === 'imagick') {
            if (class_exists('Imagick')) {
                $imagick = new \Imagick();
                $v = $imagick->getVersion();
                $version = $v['versionString'] ?? 'Unknown';
            }
        }

        return [
            'available' => $this->imageConverter->isAvailable(),
            'method' => $method,
            'version' => $version,
        ];
    }

    /**
     * Check detailed AVIF format conversion availability
     *
     * @return array Detailed availability information with recommendations
     */
    public function checkAvailability(): array
    {
        return $this->imageConverter->checkAvailability();
    }

    /**
     * Register AVIF delivery filters
     */
    private function registerDelivery(): void
    {
        if (!$this->shouldDeliverAVIF()) {
            return;
        }

        add_filter('wp_get_attachment_image_src', [$this, 'filterAttachmentImageSrc'], 10, 4);
        add_filter('wp_calculate_image_srcset', [$this, 'filterImageSrcset'], 10, 5);
        add_filter('the_content', [$this, 'filterContentImages'], 20);

        Logger::info('AVIF automatic delivery enabled');
    }

    /**
     * Check if AVIF should be delivered
     *
     * @return bool True if browser supports AVIF
     */
    private function shouldDeliverAVIF(): bool
    {
        // Check Accept header for image/avif
        $accept = $_SERVER['HTTP_ACCEPT'] ?? '';
        $supportsAVIF = strpos($accept, 'image/avif') !== false;

        // Allow filtering
        return apply_filters('fp_ps_avif_delivery_supported', $supportsAVIF);
    }

    /**
     * Rewrite image URL to AVIF
     *
     * @param string $url Original URL
     * @return string AVIF URL if available
     */
    private function rewriteImageUrl(string $url): string
    {
        $uploadDir = wp_get_upload_dir();
        $baseUrl = $uploadDir['baseurl'] ?? '';
        
        if ($baseUrl === '' || strpos($url, $baseUrl) === false) {
            return $url;
        }

        $relativePath = str_replace($baseUrl, '', $url);
        $uploadPath = $uploadDir['basedir'] ?? '';
        $filePath = $uploadPath . $relativePath;

        // Check if AVIF exists using filesystem path
        if (!$this->pathHelper->avifExists($filePath)) {
            return $url;
        }

        // Build AVIF URL correctly (add .avif to original URL)
        return $url . '.avif';
    }

    /**
     * Filter attachment image source
     *
     * @param array|false $image Image data
     * @param int|string $attachment_id Attachment ID
     * @param string|int[] $size Image size
     * @param bool $icon Whether icon
     * @return array|false Modified image data
     */
    public function filterAttachmentImageSrc($image, int|string $attachment_id, $size, bool $icon)
    {
        if (!is_array($image) || empty($image[0])) {
            return $image;
        }

        $image[0] = $this->rewriteImageUrl($image[0]);
        return $image;
    }

    /**
     * Filter image srcset
     *
     * @param array $sources Srcset sources
     * @param array $size_array Size array
     * @param string $image_src Image source
     * @param array $image_meta Image metadata
     * @param int|string $attachment_id Attachment ID
     * @return array Modified sources
     */
    public function filterImageSrcset(array $sources, array $size_array, string $image_src, array $image_meta, int|string $attachment_id): array
    {
        foreach ($sources as $key => $source) {
            if (!empty($source['url'])) {
                $sources[$key]['url'] = $this->rewriteImageUrl($source['url']);
            }
        }

        return $sources;
    }

    /**
     * Filter post content images
     *
     * @param string $content Post content
     * @return string Modified content
     */
    public function filterContentImages(string $content): string
    {
        if (empty($content) || strpos($content, '<img') === false) {
            return $content;
        }

        $pattern = '/<img([^>]+)src=["\']([^"\']+)["\']([^>]*)>/i';
        
        return preg_replace_callback($pattern, function($matches) {
            $beforeSrc = $matches[1];
            $src = $matches[2];
            $afterSrc = $matches[3];

            $newSrc = $this->rewriteImageUrl($src);
            $newTag = '<img' . $beforeSrc . 'src="' . $newSrc . '"' . $afterSrc . '>';

            if (strpos($afterSrc, 'srcset=') !== false) {
                $newTag = preg_replace_callback(
                    '/srcset=["\']([^"\']+)["\']/i',
                    function($srcsetMatch) {
                        $srcset = $srcsetMatch[1];
                        $sources = explode(',', $srcset);
                        $rewritten = [];

                        foreach ($sources as $source) {
                            $trimmed = trim($source);
                            if ($trimmed === '') {
                                continue;
                            }
                            $parts = preg_split('/\s+/', $trimmed);
                            if (!empty($parts[0])) {
                                $parts[0] = $this->rewriteImageUrl($parts[0]);
                                $rewritten[] = implode(' ', $parts);
                            }
                        }

                        return 'srcset="' . implode(', ', $rewritten) . '"';
                    },
                    $newTag
                );
            }

            return $newTag;
        }, $content);
    }

    /**
     * Get status
     *
     * @return array{enabled:bool,available:bool,method:string,quality:int}
     */
    public function status(): array
    {
        $settings = $this->settings();
        $info = $this->getInfo();

        return [
            'enabled' => $settings['enabled'],
            'available' => $info['available'],
            'method' => $info['method'],
            'quality' => $settings['quality'],
        ];
    }
}
