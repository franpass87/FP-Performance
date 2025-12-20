<?php

namespace FP\PerfSuite\Services\Intelligence\CDNExclusionSync;

use FP\PerfSuite\Core\Options\OptionsRepositoryInterface;
use function get_option;

/**
 * Rileva provider CDN attivi
 * 
 * @package FP\PerfSuite\Services\Intelligence\CDNExclusionSync
 * @author Francesco Passeri
 */
class CDNProviderDetector
{
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
    }
    /**
     * Rileva provider CDN attivi
     */
    public function detectActiveProviders(): array
    {
        $providers = [];

        // Cloudflare
        if ($this->isCloudflareActive()) {
            $providers[] = [
                'name' => 'cloudflare',
                'type' => 'cloudflare',
                'api_available' => $this->hasCloudflareAPI(),
            ];
        }

        // CloudFront
        if ($this->isCloudFrontActive()) {
            $providers[] = [
                'name' => 'cloudfront',
                'type' => 'aws',
                'api_available' => $this->hasAWSAPI(),
            ];
        }

        // KeyCDN
        if ($this->isKeyCDNActive()) {
            $providers[] = [
                'name' => 'keycdn',
                'type' => 'keycdn',
                'api_available' => $this->hasKeyCDNAPI(),
            ];
        }

        // BunnyCDN
        if ($this->isBunnyCDNActive()) {
            $providers[] = [
                'name' => 'bunnycdn',
                'type' => 'bunnycdn',
                'api_available' => $this->hasBunnyCDNAPI(),
            ];
        }

        // Generic CDN (headers detection)
        if ($this->isGenericCDNActive()) {
            $providers[] = [
                'name' => 'generic',
                'type' => 'generic',
                'api_available' => false,
            ];
        }

        return $providers;
    }

    /**
     * Controlla se Cloudflare è attivo
     */
    public function isCloudflareActive(): bool
    {
        return isset($_SERVER['HTTP_CF_RAY']) || 
               isset($_SERVER['HTTP_CF_CONNECTING_IP']) ||
               (isset($_SERVER['HTTP_CF_VISITOR']) && strpos($_SERVER['HTTP_CF_VISITOR'], 'cloudflare') !== false);
    }

    /**
     * Controlla se CloudFront è attivo
     */
    public function isCloudFrontActive(): bool
    {
        return isset($_SERVER['HTTP_CLOUDFRONT_VIEWER_COUNTRY']) ||
               isset($_SERVER['HTTP_CLOUDFRONT_IS_DESKTOP_VIEWER']) ||
               (isset($_SERVER['HTTP_VIA']) && strpos($_SERVER['HTTP_VIA'], 'cloudfront') !== false);
    }

    /**
     * Controlla se KeyCDN è attivo
     */
    public function isKeyCDNActive(): bool
    {
        return isset($_SERVER['HTTP_X_KEYCDN']) ||
               (isset($_SERVER['HTTP_SERVER']) && strpos($_SERVER['HTTP_SERVER'], 'keycdn') !== false);
    }

    /**
     * Controlla se BunnyCDN è attivo
     */
    public function isBunnyCDNActive(): bool
    {
        return isset($_SERVER['HTTP_X_BUNNYCDN']) ||
               (isset($_SERVER['HTTP_SERVER']) && strpos($_SERVER['HTTP_SERVER'], 'bunnycdn') !== false);
    }

    /**
     * Controlla se c'è un CDN generico attivo
     */
    public function isGenericCDNActive(): bool
    {
        $cdnHeaders = [
            'HTTP_X_CACHE',
            'HTTP_X_CACHE_STATUS',
            'HTTP_X_CDN',
            'HTTP_X_EDGE',
            'HTTP_X_ORIGIN',
        ];

        foreach ($cdnHeaders as $header) {
            if (isset($_SERVER[$header])) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get option value (with fallback)
     * 
     * @param string $key Option key
     * @param mixed $default Default value
     * @return mixed
     */
    private function getOption(string $key, $default = '')
    {
        if ($this->optionsRepo !== null) {
            return $this->optionsRepo->get($key, $default);
        }
        return get_option($key, $default);
    }

    /**
     * Controlla se Cloudflare API è disponibile
     */
    public function hasCloudflareAPI(): bool
    {
        $apiKey = $this->getOption('fp_ps_cloudflare_api_key');
        $email = $this->getOption('fp_ps_cloudflare_email');
        $zoneId = $this->getOption('fp_ps_cloudflare_zone_id');
        return !empty($apiKey) && !empty($email) && !empty($zoneId);
    }

    /**
     * Controlla se AWS API è disponibile
     */
    public function hasAWSAPI(): bool
    {
        $accessKey = $this->getOption('fp_ps_aws_access_key');
        $secretKey = $this->getOption('fp_ps_aws_secret_key');
        $region = $this->getOption('fp_ps_aws_region');
        return !empty($accessKey) && !empty($secretKey) && !empty($region);
    }

    /**
     * Controlla se KeyCDN API è disponibile
     */
    public function hasKeyCDNAPI(): bool
    {
        $apiKey = $this->getOption('fp_ps_keycdn_api_key');
        return !empty($apiKey);
    }

    /**
     * Controlla se BunnyCDN API è disponibile
     */
    public function hasBunnyCDNAPI(): bool
    {
        $apiKey = $this->getOption('fp_ps_bunnycdn_api_key');
        return !empty($apiKey);
    }
}

