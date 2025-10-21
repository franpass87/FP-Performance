<?php

namespace FP\PerfSuite\Services\Compatibility;

use function get_option;
use function is_object;
use function update_option;
use function wp_count_attachments;

/**
 * WebP Plugin Compatibility Manager
 * 
 * Rileva e gestisce la compatibilità con altri plugin WebP:
 * - Converter for Media
 * - ShortPixel
 * - Imagify
 * - EWWW Image Optimizer
 * - WebP Express
 * 
 * @author Francesco Passeri
 * @link https://francescopasseri.com
 */
class WebPPluginCompatibility
{
    /**
     * Plugin WebP supportati e le loro meta keys
     */
    private const WEBP_PLUGINS = [
        'converter-for-media' => [
            'name' => 'Converter for Media',
            'plugin_file' => 'webp-converter-for-media/webp-converter-for-media.php',
            'meta_key' => '_webp_converter_metadata',
            'alternative_detection' => 'WebpConverterVendor\\MatthiasWeb\\WebPConverter\\Core',
            'priority' => 100,
        ],
        'shortpixel' => [
            'name' => 'ShortPixel Image Optimizer',
            'plugin_file' => 'shortpixel-image-optimiser/wp-shortpixel.php',
            'meta_key' => '_shortpixel_status',
            'priority' => 90,
        ],
        'imagify' => [
            'name' => 'Imagify',
            'plugin_file' => 'imagify/imagify.php',
            'meta_key' => '_imagify_data',
            'priority' => 85,
        ],
        'ewww' => [
            'name' => 'EWWW Image Optimizer',
            'plugin_file' => 'ewww-image-optimizer/ewww-image-optimizer.php',
            'meta_key' => '_ewww_image_optimizer',
            'priority' => 80,
        ],
        'webp-express' => [
            'name' => 'WebP Express',
            'plugin_file' => 'webp-express/webp-express.php',
            'meta_key' => '_webp_express_converted',
            'priority' => 75,
        ],
    ];

    /**
     * Rileva se c'è un plugin WebP attivo
     * 
     * @return array|false Array con info del plugin, false se nessuno attivo
     */
    public function detectActiveWebPPlugin()
    {
        $detectedPlugins = [];
        
        // Carica le funzioni dei plugin se non disponibili
        if (!function_exists('is_plugin_active')) {
            require_once ABSPATH . 'wp-admin/includes/plugin.php';
        }

        foreach (self::WEBP_PLUGINS as $slug => $pluginInfo) {
            // Metodo 1: Controlla se il file del plugin esiste
            if (is_plugin_active($pluginInfo['plugin_file'])) {
                $pluginInfo['slug'] = $slug;
                $pluginInfo['detection_method'] = 'plugin_active';
                $detectedPlugins[] = $pluginInfo;
                continue;
            }

            // Metodo 2: Controlla se la classe del plugin esiste
            if (isset($pluginInfo['alternative_detection']) && class_exists($pluginInfo['alternative_detection'])) {
                $pluginInfo['slug'] = $slug;
                $pluginInfo['detection_method'] = 'class_exists';
                $detectedPlugins[] = $pluginInfo;
                continue;
            }

            // Metodo 3: Controlla se ci sono immagini con la meta key del plugin
            $count = $this->countImagesWithMeta($pluginInfo['meta_key']);
            if ($count > 0) {
                $pluginInfo['slug'] = $slug;
                $pluginInfo['detection_method'] = 'meta_exists';
                $pluginInfo['converted_images'] = $count;
                $detectedPlugins[] = $pluginInfo;
            }
        }

        // Ordina per priorità (più alta prima)
        usort($detectedPlugins, function($a, $b) {
            return $b['priority'] - $a['priority'];
        });

        return !empty($detectedPlugins) ? $detectedPlugins[0] : false;
    }

    /**
     * Conta le immagini convertite da un plugin specifico
     * 
     * @param string $metaKey Meta key del plugin
     * @return int Numero di immagini convertite
     */
    public function countImagesWithMeta(string $metaKey): int
    {
        global $wpdb;
        
        $query = $wpdb->prepare(
            "SELECT COUNT(DISTINCT post_id) FROM {$wpdb->postmeta} WHERE meta_key = %s",
            $metaKey
        );

        if ($query === false) {
            return 0;
        }

        return (int) $wpdb->get_var($query);
    }

    /**
     * Conta tutte le immagini WebP (da qualsiasi fonte)
     * 
     * @return array Array con statistiche complete
     */
    public function countAllWebPImages(): array
    {
        $stats = [
            'total_images' => $this->countTotalImages(),
            'webp_images' => 0,
            'sources' => [],
            'has_third_party' => false,
            'primary_source' => null,
        ];

        // Conta le immagini FP Performance Suite
        $fpImages = $this->countImagesWithMeta('_fp_ps_webp_generated');
        if ($fpImages > 0) {
            $stats['sources']['fp_performance'] = [
                'name' => 'FP Performance Suite',
                'count' => $fpImages,
                'active' => true,
            ];
            $stats['webp_images'] += $fpImages;
        }

        // Conta le immagini da plugin di terze parti
        foreach (self::WEBP_PLUGINS as $slug => $pluginInfo) {
            $count = $this->countImagesWithMeta($pluginInfo['meta_key']);
            if ($count > 0) {
                // Carica le funzioni dei plugin se necessario
                if (!function_exists('is_plugin_active')) {
                    require_once ABSPATH . 'wp-admin/includes/plugin.php';
                }
                
                $stats['sources'][$slug] = [
                    'name' => $pluginInfo['name'],
                    'count' => $count,
                    'active' => is_plugin_active($pluginInfo['plugin_file']),
                ];
                $stats['webp_images'] += $count;
                $stats['has_third_party'] = true;
            }
        }

        // Determina la fonte primaria (quella con più immagini)
        if (!empty($stats['sources'])) {
            $maxCount = 0;
            foreach ($stats['sources'] as $slug => $source) {
                if ($source['count'] > $maxCount) {
                    $maxCount = $source['count'];
                    $stats['primary_source'] = $slug;
                }
            }
        }

        $stats['coverage'] = $stats['total_images'] > 0 
            ? min(100.0, ($stats['webp_images'] / $stats['total_images']) * 100) 
            : 100.0;

        return $stats;
    }

    /**
     * Conta il totale delle immagini nella libreria media
     * 
     * @return int Numero totale di immagini
     */
    private function countTotalImages(): int
    {
        $attachments = wp_count_attachments('image');
        $count = 0;

        if (is_object($attachments)) {
            foreach ($attachments as $status => $num) {
                if ($status !== 'trash') {
                    $count += (int) $num;
                }
            }
        }

        return $count;
    }

    /**
     * Controlla se FP Performance WebP dovrebbe essere disabilitato
     * 
     * @return bool True se deve essere disabilitato
     */
    public function shouldDisableFPWebP(): bool
    {
        $activePlugin = $this->detectActiveWebPPlugin();
        
        // Se non ci sono plugin attivi, non disabilitare
        if ($activePlugin === false) {
            return false;
        }

        // Se c'è un plugin attivo con priorità più alta di FP Performance (70)
        // e ha già convertito immagini, disabilita FP WebP
        return $activePlugin['priority'] > 70 && 
               isset($activePlugin['converted_images']) && 
               $activePlugin['converted_images'] > 0;
    }

    /**
     * Ottieni messaggio di avviso per l'interfaccia
     * 
     * @return array|false Array con tipo e messaggio, false se nessun avviso
     */
    public function getWarningMessage()
    {
        $activePlugin = $this->detectActiveWebPPlugin();
        
        if ($activePlugin === false) {
            return false;
        }

        $stats = $this->countAllWebPImages();
        
        return [
            'type' => 'info',
            'plugin' => $activePlugin['name'],
            'slug' => $activePlugin['slug'],
            'message' => sprintf(
                __('È stato rilevato %s che ha già convertito %d immagini. Puoi disabilitare la conversione WebP di FP Performance Suite per evitare conflitti.', 'fp-performance-suite'),
                '<strong>' . esc_html($activePlugin['name']) . '</strong>',
                $stats['sources'][$activePlugin['slug']]['count'] ?? 0
            ),
            'stats' => $stats,
            'recommendation' => $this->getRecommendation($activePlugin, $stats),
        ];
    }

    /**
     * Ottieni raccomandazione basata sulla situazione
     * 
     * @param array $activePlugin Plugin attivo rilevato
     * @param array $stats Statistiche delle conversioni
     * @return string Raccomandazione
     */
    private function getRecommendation(array $activePlugin, array $stats): string
    {
        $fpCount = $stats['sources']['fp_performance']['count'] ?? 0;
        $thirdPartyCount = $stats['sources'][$activePlugin['slug']]['count'] ?? 0;

        // Se il plugin di terze parti ha più immagini, raccomanda di usare quello
        if ($thirdPartyCount > $fpCount * 2) {
            return sprintf(
                __('Raccomandazione: Disabilita la conversione WebP di FP Performance Suite e usa %s come fonte primaria. Ha già convertito la maggior parte delle tue immagini.', 'fp-performance-suite'),
                $activePlugin['name']
            );
        }

        // Se FP Performance ha più immagini, raccomanda di continuare con FP
        if ($fpCount > $thirdPartyCount * 2) {
            return sprintf(
                __('Raccomandazione: Continua a usare FP Performance Suite come fonte primaria. Puoi disabilitare %s per evitare duplicazioni.', 'fp-performance-suite'),
                $activePlugin['name']
            );
        }

        // Se sono simili, lascia decidere all\'utente
        return __('Raccomandazione: Scegli uno dei due plugin per la conversione WebP ed evita di usarli entrambi contemporaneamente per prevenire conflitti e duplicazioni.', 'fp-performance-suite');
    }

    /**
     * Sincronizza le statistiche per l'interfaccia
     * 
     * @return array Statistiche unificate
     */
    public function getUnifiedStats(): array
    {
        $allStats = $this->countAllWebPImages();
        $activePlugin = $this->detectActiveWebPPlugin();

        return [
            'total_images' => $allStats['total_images'],
            'converted_images' => $allStats['webp_images'],
            'coverage' => $allStats['coverage'],
            'sources' => $allStats['sources'],
            'has_conflict' => $allStats['has_third_party'],
            'active_third_party' => $activePlugin !== false ? $activePlugin : null,
            'recommendation' => $activePlugin !== false ? $this->getRecommendation($activePlugin, $allStats) : null,
        ];
    }

    /**
     * Imposta automaticamente la configurazione ottimale
     * 
     * @return array Risultato dell'auto-configurazione
     */
    public function autoConfigureWebP(): array
    {
        $activePlugin = $this->detectActiveWebPPlugin();
        $stats = $this->countAllWebPImages();
        
        $result = [
            'action_taken' => 'none',
            'reason' => '',
            'fp_webp_enabled' => true,
        ];

        // Se non c'è nessun plugin attivo, lascia FP WebP attivo
        if ($activePlugin === false) {
            $result['action_taken'] = 'keep_fp_enabled';
            $result['reason'] = __('Nessun plugin WebP di terze parti rilevato. FP Performance WebP rimane attivo.', 'fp-performance-suite');
            return $result;
        }

        $fpCount = $stats['sources']['fp_performance']['count'] ?? 0;
        $thirdPartyCount = $stats['sources'][$activePlugin['slug']]['count'] ?? 0;

        // Se il plugin di terze parti ha significativamente più immagini, disabilita FP WebP
        if ($thirdPartyCount > 50 && $thirdPartyCount > $fpCount * 1.5) {
            $result['action_taken'] = 'disable_fp_webp';
            $result['reason'] = sprintf(
                __('%s è già la fonte primaria con %d immagini convertite. FP WebP è stato disabilitato automaticamente.', 'fp-performance-suite'),
                $activePlugin['name'],
                $thirdPartyCount
            );
            $result['fp_webp_enabled'] = false;
            
            // Disabilita FP WebP nelle impostazioni
            $settings = get_option('fp_ps_webp', []);
            $settings['enabled'] = false;
            $settings['auto_disabled_reason'] = $result['reason'];
            $settings['auto_disabled_at'] = time();
            update_option('fp_ps_webp', $settings);
        }

        return $result;
    }
}

