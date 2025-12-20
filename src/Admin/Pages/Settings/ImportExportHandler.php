<?php

namespace FP\PerfSuite\Admin\Pages\Settings;

use FP\PerfSuite\ServiceContainer;
use FP\PerfSuite\Services\Cache\PageCache;
use FP\PerfSuite\Services\Cache\Headers;
use FP\PerfSuite\Services\Assets\Optimizer;
use FP\PerfSuite\Services\DB\Cleaner;

use function wp_verify_nonce;
use function wp_unslash;
use function json_decode;
use function is_array;
use function is_string;
use function array_key_exists;
use function sanitize_key;
use function __;

/**
 * Gestisce import/export delle impostazioni
 * 
 * @package FP\PerfSuite\Admin\Pages\Settings
 * @author Francesco Passeri
 */
class ImportExportHandler
{
    private ServiceContainer $container;
    private DataNormalizer $normalizer;

    public function __construct(ServiceContainer $container)
    {
        $this->container = $container;
        $this->normalizer = new DataNormalizer();
    }

    /**
     * Gestisce l'import delle impostazioni
     */
    public function handleImport(): ?string
    {
        if ('POST' !== ($_SERVER['REQUEST_METHOD'] ?? '') || !isset($_POST['fp_ps_import_nonce'])) {
            return null;
        }

        $nonce = wp_unslash($_POST['fp_ps_import_nonce']);
        if (!is_string($nonce) || !wp_verify_nonce($nonce, 'fp-ps-import')) {
            return __('Verifica di sicurezza fallita. Riprova.', 'fp-performance-suite');
        }

        if (!isset($_POST['import_json'])) {
            return null;
        }

        $json = wp_unslash($_POST['settings_json'] ?? '');
        $data = json_decode($json, true);

        if (!is_array($data)) {
            return __('❌ Payload JSON non valido.', 'fp-performance-suite');
        }

        $prepared = [];
        $valid = true;
        $allowed = [
            'fp_ps_page_cache',
            'fp_ps_browser_cache',
            'fp_ps_assets',
            'fp_ps_db',
        ];

        $pageCache = $this->container->get(PageCache::class);
        $headers = $this->container->get(Headers::class);
        $optimizer = $this->container->get(Optimizer::class);
        $cleaner = $this->container->get(Cleaner::class);

        foreach ($allowed as $option) {
            if (!array_key_exists($option, $data)) {
                continue;
            }
            if (!is_array($data[$option])) {
                $valid = false;
                break;
            }

            switch ($option) {
                case 'fp_ps_page_cache':
                    $prepared[$option] = $this->normalizer->normalizePageCacheImport(
                        $data[$option],
                        $pageCache->settings()
                    );
                    break;
                case 'fp_ps_browser_cache':
                    $prepared[$option] = $this->normalizer->normalizeBrowserCacheImport(
                        $data[$option],
                        $headers->settings()
                    );
                    break;
                case 'fp_ps_assets':
                    $assetDefaults = $optimizer->settings();
                    $prepared[$option] = $this->normalizer->normalizeAssetSettingsImport($data[$option], $assetDefaults);
                    break;
                case 'fp_ps_db':
                    $prepared[$option] = [
                        'schedule' => sanitize_key($data[$option]['schedule'] ?? $cleaner->settings()['schedule']),
                        'batch' => isset($data[$option]['batch']) ? (int) $data[$option]['batch'] : $cleaner->settings()['batch'],
                    ];
                    break;
            }
        }

        if (!$valid) {
            return __('❌ Payload JSON non valido.', 'fp-performance-suite');
        }

        // Applica le impostazioni
        if (isset($prepared['fp_ps_page_cache'])) {
            $pageCache->update($prepared['fp_ps_page_cache']);
        }
        if (isset($prepared['fp_ps_browser_cache'])) {
            $headers->update($prepared['fp_ps_browser_cache']);
        }
        if (isset($prepared['fp_ps_assets'])) {
            $optimizer->update($prepared['fp_ps_assets']);
        }
        if (isset($prepared['fp_ps_db'])) {
            $cleaner->update($prepared['fp_ps_db']);
        }

        return __('✅ Impostazioni importate con successo.', 'fp-performance-suite');
    }

    /**
     * Prepara i dati per l'export
     */
    public function prepareExport(): array
    {
        $pageCache = $this->container->get(PageCache::class);
        $headers = $this->container->get(Headers::class);
        $optimizer = $this->container->get(Optimizer::class);
        $cleaner = $this->container->get(Cleaner::class);

        return [
            'fp_ps_page_cache' => $pageCache->settings(),
            'fp_ps_browser_cache' => $headers->settings(),
            'fp_ps_assets' => $optimizer->settings(),
            'fp_ps_db' => $cleaner->settings(),
        ];
    }
}

