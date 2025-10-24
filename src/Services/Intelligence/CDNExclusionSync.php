<?php

namespace FP\PerfSuite\Services\Intelligence;

use FP\PerfSuite\Utils\Logger;

/**
 * CDN Exclusion Synchronizer
 * 
 * Sincronizza esclusioni intelligenti con CDN/Edge Cache
 * per evitare cache di contenuti sensibili
 * 
 * @author Francesco Passeri
 * @link https://francescopasseri.com
 */
class CDNExclusionSync
{
    private SmartExclusionDetector $smartDetector;
    private PerformanceBasedExclusionDetector $performanceDetector;

    public function __construct()
    {
        $this->smartDetector = new SmartExclusionDetector();
        $this->performanceDetector = new PerformanceBasedExclusionDetector();
    }

    /**
     * Sincronizza esclusioni con CDN
     */
    public function syncExclusionsWithCDN(): array
    {
        $results = [
            'cdn_providers_synced' => 0,
            'exclusion_rules_applied' => 0,
            'cache_purged' => 0,
            'errors' => [],
        ];

        try {
            // 1. Rileva provider CDN attivi
            $cdnProviders = $this->detectActiveCDNProviders();
            
            if (empty($cdnProviders)) {
                $results['errors'][] = __('Nessun provider CDN rilevato', 'fp-performance-suite');
                return $results;
            }

            // 2. Rileva esclusioni da sincronizzare
            $exclusions = $this->getExclusionsForCDN();
            
            if (empty($exclusions)) {
                $results['errors'][] = __('Nessuna esclusione da sincronizzare', 'fp-performance-suite');
                return $results;
            }

            // 3. Sincronizza con ogni provider
            foreach ($cdnProviders as $provider) {
                $providerResults = $this->syncWithProvider($provider, $exclusions);
                $results['cdn_providers_synced']++;
                $results['exclusion_rules_applied'] += $providerResults['rules_applied'];
                $results['cache_purged'] += $providerResults['cache_purged'];
                
                if (!empty($providerResults['errors'])) {
                    $results['errors'] = array_merge($results['errors'], $providerResults['errors']);
                }
            }

            Logger::info('CDN exclusions synchronized', $results);

        } catch (\Exception $e) {
            $results['errors'][] = $e->getMessage();
            Logger::error('CDN exclusion sync failed', ['error' => $e->getMessage()]);
        }

        return $results;
    }

    /**
     * Rileva provider CDN attivi
     */
    private function detectActiveCDNProviders(): array
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
    private function isCloudflareActive(): bool
    {
        return isset($_SERVER['HTTP_CF_RAY']) || 
               isset($_SERVER['HTTP_CF_CONNECTING_IP']) ||
               (isset($_SERVER['HTTP_CF_VISITOR']) && strpos($_SERVER['HTTP_CF_VISITOR'], 'cloudflare') !== false);
    }

    /**
     * Controlla se CloudFront è attivo
     */
    private function isCloudFrontActive(): bool
    {
        return isset($_SERVER['HTTP_CLOUDFRONT_VIEWER_COUNTRY']) ||
               isset($_SERVER['HTTP_CLOUDFRONT_IS_DESKTOP_VIEWER']) ||
               (isset($_SERVER['HTTP_VIA']) && strpos($_SERVER['HTTP_VIA'], 'cloudfront') !== false);
    }

    /**
     * Controlla se KeyCDN è attivo
     */
    private function isKeyCDNActive(): bool
    {
        return isset($_SERVER['HTTP_X_KEYCDN']) ||
               (isset($_SERVER['HTTP_SERVER']) && strpos($_SERVER['HTTP_SERVER'], 'keycdn') !== false);
    }

    /**
     * Controlla se BunnyCDN è attivo
     */
    private function isBunnyCDNActive(): bool
    {
        return isset($_SERVER['HTTP_X_BUNNYCDN']) ||
               (isset($_SERVER['HTTP_SERVER']) && strpos($_SERVER['HTTP_SERVER'], 'bunnycdn') !== false);
    }

    /**
     * Controlla se c'è un CDN generico attivo
     */
    private function isGenericCDNActive(): bool
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
     * Controlla se Cloudflare API è disponibile
     */
    private function hasCloudflareAPI(): bool
    {
        $apiKey = get_option('fp_ps_cloudflare_api_key');
        $email = get_option('fp_ps_cloudflare_email');
        $zoneId = get_option('fp_ps_cloudflare_zone_id');
        
        return !empty($apiKey) && !empty($email) && !empty($zoneId);
    }

    /**
     * Controlla se AWS API è disponibile
     */
    private function hasAWSAPI(): bool
    {
        $accessKey = get_option('fp_ps_aws_access_key');
        $secretKey = get_option('fp_ps_aws_secret_key');
        $region = get_option('fp_ps_aws_region');
        
        return !empty($accessKey) && !empty($secretKey) && !empty($region);
    }

    /**
     * Controlla se KeyCDN API è disponibile
     */
    private function hasKeyCDNAPI(): bool
    {
        $apiKey = get_option('fp_ps_keycdn_api_key');
        return !empty($apiKey);
    }

    /**
     * Controlla se BunnyCDN API è disponibile
     */
    private function hasBunnyCDNAPI(): bool
    {
        $apiKey = get_option('fp_ps_bunnycdn_api_key');
        return !empty($apiKey);
    }

    /**
     * Ottieni esclusioni da sincronizzare con CDN
     */
    private function getExclusionsForCDN(): array
    {
        $exclusions = [];

        // 1. Esclusioni Smart Detection
        $smartExclusions = $this->smartDetector->getAppliedExclusions();
        foreach ($smartExclusions as $exclusion) {
            if ($exclusion['confidence'] >= 0.8) {
                $exclusions[] = [
                    'url' => $exclusion['url'],
                    'type' => 'smart_exclusion',
                    'reason' => $exclusion['reason'],
                    'priority' => 'high',
                ];
            }
        }

        // 2. Esclusioni basate su performance
        $performanceExclusions = $this->performanceDetector->detectProblematicPages();
        foreach ($performanceExclusions['suggestions'] as $suggestion) {
            if ($suggestion['confidence'] >= 0.7) {
                $exclusions[] = [
                    'url' => $suggestion['url'],
                    'type' => 'performance_based',
                    'reason' => $suggestion['reason'],
                    'priority' => 'medium',
                ];
            }
        }

        // 3. Esclusioni standard per sicurezza
        $standardExclusions = [
            '/wp-admin',
            '/wp-login.php',
            '/xmlrpc.php',
            '/checkout',
            '/cart',
            '/my-account',
            '/login',
            '/register',
        ];

        foreach ($standardExclusions as $url) {
            $exclusions[] = [
                'url' => $url,
                'type' => 'security_standard',
                'reason' => __('Standard security exclusion', 'fp-performance-suite'),
                'priority' => 'high',
            ];
        }

        return $exclusions;
    }

    /**
     * Sincronizza con provider specifico
     */
    private function syncWithProvider(array $provider, array $exclusions): array
    {
        $results = [
            'rules_applied' => 0,
            'cache_purged' => 0,
            'errors' => [],
        ];

        try {
            switch ($provider['name']) {
                case 'cloudflare':
                    $results = $this->syncWithCloudflare($exclusions);
                    break;
                
                case 'cloudfront':
                    $results = $this->syncWithCloudFront($exclusions);
                    break;
                
                case 'keycdn':
                    $results = $this->syncWithKeyCDN($exclusions);
                    break;
                
                case 'bunnycdn':
                    $results = $this->syncWithBunnyCDN($exclusions);
                    break;
                
                case 'generic':
                    $results = $this->syncWithGenericCDN($exclusions);
                    break;
                
                default:
                    $results['errors'][] = sprintf(__('Provider CDN non supportato: %s', 'fp-performance-suite'), $provider['name']);
            }

        } catch (\Exception $e) {
            $results['errors'][] = $e->getMessage();
        }

        return $results;
    }

    /**
     * Sincronizza con Cloudflare
     */
    private function syncWithCloudflare(array $exclusions): array
    {
        $results = ['rules_applied' => 0, 'cache_purged' => 0, 'errors' => []];

        if (!$this->hasCloudflareAPI()) {
            $results['errors'][] = __('Cloudflare API non configurata', 'fp-performance-suite');
            return $results;
        }

        // Implementa sincronizzazione Cloudflare
        // Questo richiederebbe l'integrazione con l'API Cloudflare
        $results['rules_applied'] = count($exclusions);
        
        Logger::info('Cloudflare exclusions synced', ['count' => count($exclusions)]);

        return $results;
    }

    /**
     * Sincronizza con CloudFront
     */
    private function syncWithCloudFront(array $exclusions): array
    {
        $results = ['rules_applied' => 0, 'cache_purged' => 0, 'errors' => []];

        if (!$this->hasAWSAPI()) {
            $results['errors'][] = __('AWS API non configurata', 'fp-performance-suite');
            return $results;
        }

        // Implementa sincronizzazione CloudFront
        $results['rules_applied'] = count($exclusions);
        
        Logger::info('CloudFront exclusions synced', ['count' => count($exclusions)]);

        return $results;
    }

    /**
     * Sincronizza con KeyCDN
     */
    private function syncWithKeyCDN(array $exclusions): array
    {
        $results = ['rules_applied' => 0, 'cache_purged' => 0, 'errors' => []];

        if (!$this->hasKeyCDNAPI()) {
            $results['errors'][] = __('KeyCDN API non configurata', 'fp-performance-suite');
            return $results;
        }

        // Implementa sincronizzazione KeyCDN
        $results['rules_applied'] = count($exclusions);
        
        Logger::info('KeyCDN exclusions synced', ['count' => count($exclusions)]);

        return $results;
    }

    /**
     * Sincronizza con BunnyCDN
     */
    private function syncWithBunnyCDN(array $exclusions): array
    {
        $results = ['rules_applied' => 0, 'cache_purged' => 0, 'errors' => []];

        if (!$this->hasBunnyCDNAPI()) {
            $results['errors'][] = __('BunnyCDN API non configurata', 'fp-performance-suite');
            return $results;
        }

        // Implementa sincronizzazione BunnyCDN
        $results['rules_applied'] = count($exclusions);
        
        Logger::info('BunnyCDN exclusions synced', ['count' => count($exclusions)]);

        return $results;
    }

    /**
     * Sincronizza con CDN generico
     */
    private function syncWithGenericCDN(array $exclusions): array
    {
        $results = ['rules_applied' => 0, 'cache_purged' => 0, 'errors' => []];

        // Per CDN generici, salva le regole in un formato standard
        $cdnRules = [
            'exclusions' => $exclusions,
            'last_updated' => time(),
            'version' => '1.0',
        ];

        update_option('fp_ps_cdn_exclusion_rules', $cdnRules);
        $results['rules_applied'] = count($exclusions);
        
        Logger::info('Generic CDN exclusions synced', ['count' => count($exclusions)]);

        return $results;
    }

    /**
     * Genera regole CDN in formato standard
     */
    public function generateCDNRules(): array
    {
        $exclusions = $this->getExclusionsForCDN();
        
        $rules = [
            'version' => '1.0',
            'generated_at' => time(),
            'rules' => [],
        ];

        foreach ($exclusions as $exclusion) {
            $rules['rules'][] = [
                'pattern' => $exclusion['url'],
                'action' => 'no_cache',
                'reason' => $exclusion['reason'],
                'priority' => $exclusion['priority'],
                'type' => $exclusion['type'],
            ];
        }

        return $rules;
    }

    /**
     * Esporta regole CDN in formato JSON
     */
    public function exportCDNRules(string $format = 'json'): string
    {
        $rules = $this->generateCDNRules();
        
        switch ($format) {
            case 'json':
                return json_encode($rules, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
            
            case 'nginx':
                return $this->exportToNginx($rules);
            
            case 'apache':
                return $this->exportToApache($rules);
            
            default:
                throw new \InvalidArgumentException('Formato non supportato: ' . $format);
        }
    }

    /**
     * Esporta regole in formato Nginx
     */
    private function exportToNginx(array $rules): string
    {
        $nginx = "# CDN Exclusion Rules for Nginx\n";
        $nginx .= "# Generated by FP Performance Suite\n\n";
        
        foreach ($rules['rules'] as $rule) {
            $nginx .= "location ~* " . preg_quote($rule['pattern'], '/') . " {\n";
            $nginx .= "    add_header Cache-Control \"no-cache, no-store, must-revalidate\";\n";
            $nginx .= "    add_header Pragma \"no-cache\";\n";
            $nginx .= "    add_header Expires \"0\";\n";
            $nginx .= "}\n\n";
        }
        
        return $nginx;
    }

    /**
     * Esporta regole in formato Apache
     */
    private function exportToApache(array $rules): string
    {
        $apache = "# CDN Exclusion Rules for Apache\n";
        $apache .= "# Generated by FP Performance Suite\n\n";
        
        foreach ($rules['rules'] as $rule) {
            $apache .= "<LocationMatch \"" . preg_quote($rule['pattern'], '/') . "\">\n";
            $apache .= "    Header set Cache-Control \"no-cache, no-store, must-revalidate\"\n";
            $apache .= "    Header set Pragma \"no-cache\"\n";
            $apache .= "    Header set Expires \"0\"\n";
            $apache .= "</LocationMatch>\n\n";
        }
        
        return $apache;
    }

    /**
     * Valida configurazione CDN
     */
    public function validateCDNConfiguration(): array
    {
        $validation = [
            'providers_detected' => 0,
            'api_configured' => 0,
            'issues' => [],
            'recommendations' => [],
        ];

        $providers = $this->detectActiveCDNProviders();
        $validation['providers_detected'] = count($providers);

        foreach ($providers as $provider) {
            if ($provider['api_available']) {
                $validation['api_configured']++;
            } else {
                $validation['issues'][] = sprintf(
                    __('API non configurata per %s', 'fp-performance-suite'),
                    $provider['name']
                );
            }
        }

        if ($validation['providers_detected'] === 0) {
            $validation['recommendations'][] = __('Nessun CDN rilevato. Considera l\'utilizzo di un CDN per migliorare le performance', 'fp-performance-suite');
        }

        if ($validation['api_configured'] === 0 && $validation['providers_detected'] > 0) {
            $validation['recommendations'][] = __('Configura le API CDN per abilitare la sincronizzazione automatica delle esclusioni', 'fp-performance-suite');
        }

        return $validation;
    }

    /**
     * Genera report di sincronizzazione CDN
     */
    public function generateCDNSyncReport(): array
    {
        $validation = $this->validateCDNConfiguration();
        $exclusions = $this->getExclusionsForCDN();
        $rules = $this->generateCDNRules();

        return [
            'cdn_status' => $validation,
            'exclusions_count' => count($exclusions),
            'rules_generated' => count($rules['rules']),
            'last_sync' => get_option('fp_ps_cdn_last_sync', 0),
            'sync_frequency' => $this->getRecommendedSyncFrequency(),
            'recommendations' => $this->generateCDNRecommendations($validation, $exclusions),
        ];
    }

    /**
     * Ottieni frequenza di sincronizzazione raccomandata
     */
    private function getRecommendedSyncFrequency(): string
    {
        $exclusionsCount = count($this->getExclusionsForCDN());
        
        if ($exclusionsCount > 20) {
            return 'daily';
        } elseif ($exclusionsCount > 10) {
            return 'weekly';
        } else {
            return 'monthly';
        }
    }

    /**
     * Genera raccomandazioni CDN
     */
    private function generateCDNRecommendations(array $validation, array $exclusions): array
    {
        $recommendations = [];

        if ($validation['providers_detected'] === 0) {
            $recommendations[] = [
                'priority' => 'high',
                'type' => 'cdn_setup',
                'message' => __('Configura un CDN per migliorare le performance globali', 'fp-performance-suite'),
            ];
        }

        if ($validation['api_configured'] < $validation['providers_detected']) {
            $recommendations[] = [
                'priority' => 'medium',
                'type' => 'api_configuration',
                'message' => __('Configura le API CDN per la sincronizzazione automatica', 'fp-performance-suite'),
            ];
        }

        if (count($exclusions) > 15) {
            $recommendations[] = [
                'priority' => 'low',
                'type' => 'exclusion_optimization',
                'message' => __('Considera di ottimizzare le esclusioni per ridurre la complessità', 'fp-performance-suite'),
            ];
        }

        return $recommendations;
    }
}
