<?php

namespace FP\PerfSuite\Services\Media;

use FP\PerfSuite\Services\Media\AVIF\AVIFImageConverter;
use FP\PerfSuite\Services\Media\AVIF\AVIFPathHelper;
use FP\PerfSuite\Utils\Logger;

/**
 * AVIF Converter Service
 * 
 * Servizio principale per la conversione AVIF
 *
 * @package FP\PerfSuite\Services\Media
 * @author Francesco Passeri
 * @link https://francescopasseri.com
 */
class AVIFConverter
{
    private const OPTION_KEY = 'fp_ps_avif';

    private AVIFImageConverter $converter;
    private AVIFPathHelper $pathHelper;

    public function __construct(AVIFImageConverter $converter, AVIFPathHelper $pathHelper)
    {
        $this->converter = $converter;
        $this->pathHelper = $pathHelper;
    }

    /**
     * Registra il servizio
     */
    public function register(): void
    {
        $settings = $this->getSettings();

        if (empty($settings['enabled'])) {
            return;
        }

        // Conversione automatica al caricamento
        if (!empty($settings['auto_convert'])) {
            add_filter('wp_handle_upload', [$this, 'convertOnUpload']);
        }

        // Sostituzione output HTML
        if (!empty($settings['replace_in_content'])) {
            add_filter('the_content', [$this, 'replaceImagesInContent'], 999);
            add_filter('post_thumbnail_html', [$this, 'replaceImageInHtml'], 999);
        }

        // Pulizia alla cancellazione
        add_action('delete_attachment', [$this, 'deleteAVIF']);

        Logger::debug('AVIF Converter registered');
    }

    /**
     * Ottiene le impostazioni
     */
    public function getSettings(): array
    {
        $defaults = [
            'enabled' => false,
            'auto_convert' => false,
            'replace_in_content' => false,
            'quality' => 80,
            'max_width' => 2560,
            'max_height' => 2560,
        ];

        $options = get_option(self::OPTION_KEY, []);
        return wp_parse_args($options, $defaults);
    }

    /**
     * Aggiorna le impostazioni
     */
    public function updateSettings(array $settings): bool
    {
        $current = $this->getSettings();
        $updated = wp_parse_args($settings, $current);

        $result = update_option(self::OPTION_KEY, $updated);
        
        if ($result) {
            Logger::info('AVIF settings updated', $updated);
        }

        return $result;
    }

    /**
     * Verifica se AVIF è supportato
     */
    public function isSupported(): bool
    {
        return $this->converter->isSupported();
    }

    /**
     * Converte un'immagine in AVIF
     */
    public function convertImage(string $sourcePath): ?string
    {
        if (!$this->isSupported()) {
            return null;
        }

        $avifPath = $this->pathHelper->getAVIFPath($sourcePath);
        
        // Assicura che la directory esista
        $this->pathHelper->ensureDirectoryExists($avifPath);

        $settings = $this->getSettings();
        $result = $this->converter->convert($sourcePath, $avifPath, $settings['quality']);

        return $result ? $avifPath : null;
    }

    /**
     * Converte un attachment
     */
    public function convertAttachment(int $attachmentId): bool
    {
        $originalPath = get_attached_file($attachmentId);
        
        if (!$originalPath || !file_exists($originalPath)) {
            return false;
        }

        // Verifica che sia un'immagine
        $mimeType = get_post_mime_type($attachmentId);
        if (!in_array($mimeType, ['image/jpeg', 'image/png', 'image/webp'], true)) {
            return false;
        }

        $avifPath = $this->convertImage($originalPath);

        if ($avifPath) {
            // Salva metadata
            update_post_meta($attachmentId, '_fp_avif_path', $avifPath);
            update_post_meta($attachmentId, '_fp_avif_converted', time());
            
            Logger::info('Attachment converted to AVIF', [
                'id' => $attachmentId,
                'avif_path' => $avifPath,
            ]);

            return true;
        }

        return false;
    }

    /**
     * Conversione automatica al caricamento
     */
    public function convertOnUpload(array $upload): array
    {
        if (empty($upload['file'])) {
            return $upload;
        }

        $filePath = $upload['file'];
        $mimeType = $upload['type'];

        // Solo immagini
        if (!in_array($mimeType, ['image/jpeg', 'image/png', 'image/webp'], true)) {
            return $upload;
        }

        // Converti
        $avifPath = $this->convertImage($filePath);

        if ($avifPath) {
            $upload['avif_file'] = $avifPath;
        }

        return $upload;
    }

    /**
     * Sostituisce immagini con AVIF nel contenuto
     */
    public function replaceImagesInContent(string $content): string
    {
        if (empty($content)) {
            return $content;
        }

        // Trova tutti gli img tag
        preg_match_all('/<img[^>]+>/i', $content, $matches);

        if (empty($matches[0])) {
            return $content;
        }

        foreach ($matches[0] as $imgTag) {
            // Estrai src
            if (!preg_match('/src=(["\'])(.*?)\1/i', $imgTag, $srcMatch)) {
                continue;
            }

            $originalSrc = $srcMatch[2];
            
            // Ottieni path da URL
            $uploadDir = wp_upload_dir();
            $imagePath = str_replace($uploadDir['baseurl'], $uploadDir['basedir'], $originalSrc);

            // Verifica se esiste AVIF
            if (!$this->pathHelper->avifExists($imagePath)) {
                continue;
            }

            $avifUrl = $this->pathHelper->getAVIFUrl($originalSrc);

            // Crea picture tag con fallback
            $picture = sprintf(
                '<picture><source srcset="%s" type="image/avif">%s</picture>',
                esc_url($avifUrl),
                $imgTag
            );

            $content = str_replace($imgTag, $picture, $content);
        }

        return $content;
    }

    /**
     * Sostituisce immagine singola in HTML
     */
    public function replaceImageInHtml(string $html): string
    {
        return $this->replaceImagesInContent($html);
    }

    /**
     * Elimina file AVIF quando attachment viene cancellato
     */
    public function deleteAVIF(int $attachmentId): void
    {
        $this->pathHelper->deleteAVIFFromAttachment($attachmentId);
        delete_post_meta($attachmentId, '_fp_avif_path');
        delete_post_meta($attachmentId, '_fp_avif_converted');
    }

    /**
     * Conversione batch
     */
    public function convertBatch(array $attachmentIds, int $batchSize = 10): array
    {
        $results = [
            'success' => [],
            'failed' => [],
            'skipped' => [],
            'total' => count($attachmentIds),
        ];

        $processed = 0;

        foreach ($attachmentIds as $attachmentId) {
            if ($processed >= $batchSize) {
                break;
            }

            // Salta se già convertito
            if ($this->pathHelper->attachmentHasAVIF($attachmentId)) {
                $results['skipped'][] = $attachmentId;
                continue;
            }

            $result = $this->convertAttachment($attachmentId);

            if ($result) {
                $results['success'][] = $attachmentId;
            } else {
                $results['failed'][] = $attachmentId;
            }

            $processed++;
        }

        return $results;
    }

    /**
     * Ottiene tutti gli attachment che possono essere convertiti
     */
    public function getConvertibleAttachments(): array
    {
        $args = [
            'post_type' => 'attachment',
            'post_mime_type' => ['image/jpeg', 'image/png', 'image/webp'],
            'post_status' => 'inherit',
            'posts_per_page' => -1,
            'fields' => 'ids',
            'meta_query' => [
                [
                    'key' => '_fp_avif_converted',
                    'compare' => 'NOT EXISTS',
                ],
            ],
        ];

        return get_posts($args);
    }

    /**
     * Ottiene statistiche AVIF
     */
    public function getStats(): array
    {
        global $wpdb;

        $pathStats = $this->pathHelper->getStats();

        // Conta attachment convertiti
        $converted = $wpdb->get_var(
            "SELECT COUNT(*) FROM {$wpdb->postmeta} 
            WHERE meta_key = '_fp_avif_converted'"
        );

        // Conta attachment totali
        $total = $wpdb->get_var(
            "SELECT COUNT(*) FROM {$wpdb->posts} 
            WHERE post_type = 'attachment' 
            AND post_mime_type IN ('image/jpeg', 'image/png', 'image/webp')"
        );

        $coverage = $total > 0 ? round(($converted / $total) * 100, 2) : 0;

        return [
            'enabled' => !empty($this->getSettings()['enabled']),
            'supported' => $this->isSupported(),
            'converted' => (int) $converted,
            'total' => (int) $total,
            'coverage' => $coverage,
            'file_count' => $pathStats['count'],
            'total_size' => $pathStats['total_size'],
            'total_size_formatted' => $pathStats['total_size_formatted'],
        ];
    }

    /**
     * Pulisce tutti i file AVIF
     */
    public function cleanAll(): bool
    {
        global $wpdb;

        // Elimina file
        $result = $this->pathHelper->cleanAVIFDirectory();

        // Elimina metadata
        $wpdb->query(
            "DELETE FROM {$wpdb->postmeta} 
            WHERE meta_key IN ('_fp_avif_path', '_fp_avif_converted')"
        );

        Logger::info('All AVIF files cleaned');

        return $result;
    }

    /**
     * Ottiene info supporto
     */
    public function getSupportInfo(): array
    {
        return $this->converter->getSupportInfo();
    }

    /**
     * Status completo
     */
    public function status(): array
    {
        $settings = $this->getSettings();
        $stats = $this->getStats();
        $support = $this->getSupportInfo();

        return [
            'settings' => $settings,
            'stats' => $stats,
            'support' => $support,
        ];
    }
}

