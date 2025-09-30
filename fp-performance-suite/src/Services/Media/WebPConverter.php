<?php

namespace FP\PerfSuite\Services\Media;

use FP\PerfSuite\Utils\Fs;
use WP_Query;

class WebPConverter
{
    private const OPTION = 'fp_ps_webp';
    private Fs $fs;

    public function __construct(Fs $fs)
    {
        $this->fs = $fs;
    }

    public function register(): void
    {
        if (!$this->settings()['enabled']) {
            return;
        }

        add_filter('wp_generate_attachment_metadata', [$this, 'generateWebp'], 10, 2);
        add_filter('wp_update_attachment_metadata', [$this, 'generateWebp'], 10, 2);
    }

    /**
     * @return array{enabled:bool,quality:int,keep_original:bool,lossy:bool}
     */
    public function settings(): array
    {
        $defaults = [
            'enabled' => false,
            'quality' => 82,
            'keep_original' => true,
            'lossy' => true,
        ];
        return wp_parse_args(get_option(self::OPTION, []), $defaults);
    }

    public function update(array $settings): void
    {
        $current = $this->settings();
        $new = [
            'enabled' => !empty($settings['enabled']),
            'quality' => isset($settings['quality']) ? (int) $settings['quality'] : $current['quality'],
            'keep_original' => !empty($settings['keep_original']),
            'lossy' => !empty($settings['lossy']),
        ];
        update_option(self::OPTION, $new);
    }

    /**
     * @param array<string, mixed> $metadata
     * @param int $attachment_id
     * @return array<string, mixed>
     */
    public function generateWebp(array $metadata, int $attachment_id): array
    {
        $settings = $this->settings();
        if (!$settings['enabled']) {
            return $metadata;
        }

        $file = get_attached_file($attachment_id);
        if (!$file || !file_exists($file)) {
            return $metadata;
        }

        $this->convert($file, $settings);
        if (!empty($metadata['sizes'])) {
            foreach ($metadata['sizes'] as $size) {
                if (!empty($size['file'])) {
                    $path = path_join(dirname($file), $size['file']);
                    $this->convert($path, $settings);
                }
            }
        }
        return $metadata;
    }

    /**
     * @param string $file
     * @param array<string, mixed> $settings
     */
    public function convert(string $file, array $settings): void
    {
        $info = pathinfo($file);
        $ext = strtolower($info['extension'] ?? '');
        if (!in_array($ext, ['jpg', 'jpeg', 'png'], true)) {
            return;
        }

        $webpFile = $info['dirname'] . '/' . $info['filename'] . '.webp';
        if (file_exists($webpFile)) {
            return;
        }

        if (class_exists('Imagick')) {
            $imagick = new \Imagick($file);
            if ($settings['lossy']) {
                $imagick->setImageCompressionQuality($settings['quality']);
            }
            $imagick->setImageFormat('webp');
            $imagick->writeImage($webpFile);
            $imagick->clear();
            $imagick->destroy();
        } elseif (function_exists('imagecreatefromstring') && function_exists('imagewebp')) {
            $image = imagecreatefromstring((string) file_get_contents($file));
            if ($image !== false) {
                imagepalettetotruecolor($image);
                imagewebp($image, $webpFile, $settings['quality']);
                imagedestroy($image);
            }
        }

        if (!$settings['keep_original'] && file_exists($webpFile)) {
            unlink($file);
        }
    }

    public function bulkConvert(int $limit = 20, int $offset = 0): array
    {
        $query = new WP_Query([
            'post_type' => 'attachment',
            'post_status' => 'inherit',
            'post_mime_type' => ['image/jpeg', 'image/png'],
            'posts_per_page' => $limit,
            'offset' => $offset,
            'fields' => 'ids',
        ]);
        $settings = $this->settings();
        $converted = 0;

        foreach ($query->posts as $attachment_id) {
            $metadata = wp_get_attachment_metadata($attachment_id);
            $this->generateWebp($metadata ?: [], (int) $attachment_id);
            $converted++;
        }

        return [
            'converted' => $converted,
            'total' => (int) $query->found_posts,
        ];
    }

    public function coverage(): float
    {
        $count = (int) wp_count_attachments('image')['inherit'] ?? 0;
        if ($count === 0) {
            return 100.0;
        }
        global $wpdb;
        $like = $wpdb->esc_like('.webp');
        $webp = (int) $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->postmeta} WHERE meta_key = '_wp_attached_file' AND meta_value LIKE '%$like'");
        return min(100.0, ($webp / $count) * 100);
    }

    public function status(): array
    {
        $settings = $this->settings();
        return [
            'enabled' => $settings['enabled'],
            'quality' => $settings['quality'],
            'coverage' => $this->coverage(),
        ];
    }
}
