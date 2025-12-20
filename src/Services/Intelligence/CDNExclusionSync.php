<?php

namespace FP\PerfSuite\Services\Intelligence;

use FP\PerfSuite\Core\Logging\LoggerInterface;
use FP\PerfSuite\Core\Options\OptionsRepositoryInterface;
use FP\PerfSuite\Services\Intelligence\CDNExclusionSync\CDNProviderDetector;
use FP\PerfSuite\Services\Intelligence\CDNExclusionSync\CDNSyncHandlers;
use FP\PerfSuite\Services\Intelligence\CDNExclusionSync\CDNRulesGenerator;
use FP\PerfSuite\Services\Intelligence\CDNExclusionSync\CDNValidator;
use FP\PerfSuite\Services\Intelligence\CDNExclusionSync\CDNReportGenerator;
use FP\PerfSuite\Utils\Logger as StaticLogger;

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
    private CDNProviderDetector $providerDetector;
    private CDNSyncHandlers $syncHandlers;
    private CDNRulesGenerator $rulesGenerator;
    private CDNValidator $validator;
    private CDNReportGenerator $reportGenerator;
    
    /** @var OptionsRepositoryInterface|null Options repository (injected) */
    private ?OptionsRepositoryInterface $optionsRepo = null;
    
    /** @var LoggerInterface|null Logger (injected) */
    private ?LoggerInterface $logger = null;

    /**
     * Constructor
     * 
     * @param OptionsRepositoryInterface|null $optionsRepo Options repository (optional for backward compatibility)
     * @param LoggerInterface|null $logger Logger (optional for backward compatibility)
     */
    public function __construct(?OptionsRepositoryInterface $optionsRepo = null, ?LoggerInterface $logger = null)
    {
        $this->optionsRepo = $optionsRepo;
        $this->logger = $logger;
        $this->smartDetector = new SmartExclusionDetector();
        $this->performanceDetector = new PerformanceBasedExclusionDetector();
        $this->providerDetector = new CDNProviderDetector($optionsRepo);
        $this->syncHandlers = new CDNSyncHandlers($this->providerDetector, $this->optionsRepo);
        $this->rulesGenerator = new CDNRulesGenerator();
        $this->validator = new CDNValidator($this->providerDetector);
        $this->reportGenerator = new CDNReportGenerator($this->validator, $this->rulesGenerator, $optionsRepo);
    }
    
    /**
     * Helper per logging con fallback
     * 
     * @param string $level Log level
     * @param string $message Message
     * @param array $context Context
     * @param \Throwable|null $exception Optional exception
     */
    private function log(string $level, string $message, array $context = [], ?\Throwable $exception = null): void
    {
        if ($this->logger !== null) {
            if ($exception !== null && method_exists($this->logger, $level)) {
                $this->logger->$level($message, $context, $exception);
            } else {
                $this->logger->$level($message, $context);
            }
        } else {
            StaticLogger::$level($message, $context);
        }
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
            $cdnProviders = $this->providerDetector->detectActiveProviders();
            
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
                $providerResults = $this->syncHandlers->syncWithProvider($provider, $exclusions);
                $results['cdn_providers_synced']++;
                $results['exclusion_rules_applied'] += $providerResults['rules_applied'];
                $results['cache_purged'] += $providerResults['cache_purged'];
                
                if (!empty($providerResults['errors'])) {
                    $results['errors'] = array_merge($results['errors'], $providerResults['errors']);
                }
            }

            $this->log('info', 'CDN exclusions synchronized', $results);

        } catch (\Exception $e) {
            $results['errors'][] = $e->getMessage();
            $this->log('error', 'CDN exclusion sync failed', ['error' => $e->getMessage()], $e);
        }

        return $results;
    }

    // Metodi rimossi - ora gestiti da CDNProviderDetector
    // detectActiveCDNProviders() -> CDNProviderDetector::detectActiveProviders()
    // isCloudflareActive() -> CDNProviderDetector::isCloudflareActive()
    // isCloudFrontActive() -> CDNProviderDetector::isCloudFrontActive()
    // isKeyCDNActive() -> CDNProviderDetector::isKeyCDNActive()
    // isBunnyCDNActive() -> CDNProviderDetector::isBunnyCDNActive()
    // isGenericCDNActive() -> CDNProviderDetector::isGenericCDNActive()
    // hasCloudflareAPI() -> CDNProviderDetector::hasCloudflareAPI()
    // hasAWSAPI() -> CDNProviderDetector::hasAWSAPI()
    // hasKeyCDNAPI() -> CDNProviderDetector::hasKeyCDNAPI()
    // hasBunnyCDNAPI() -> CDNProviderDetector::hasBunnyCDNAPI()

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

    // Metodi rimossi - ora gestiti da CDNSyncHandlers
    // syncWithProvider() -> CDNSyncHandlers::syncWithProvider()
    // syncWithCloudflare() -> CDNSyncHandlers::syncWithCloudflare()
    // syncWithCloudFront() -> CDNSyncHandlers::syncWithCloudFront()
    // syncWithKeyCDN() -> CDNSyncHandlers::syncWithKeyCDN()
    // syncWithBunnyCDN() -> CDNSyncHandlers::syncWithBunnyCDN()
    // syncWithGenericCDN() -> CDNSyncHandlers::syncWithGenericCDN()

    /**
     * Genera regole CDN in formato standard
     */
    public function generateCDNRules(): array
    {
        $exclusions = $this->getExclusionsForCDN();
        return $this->rulesGenerator->generate($exclusions);
    }

    /**
     * Esporta regole CDN in formato JSON
     */
    public function exportCDNRules(string $format = 'json'): string
    {
        $rules = $this->generateCDNRules();
        return $this->rulesGenerator->export($rules, $format);
    }

    /**
     * Valida configurazione CDN
     */
    public function validateCDNConfiguration(): array
    {
        return $this->validator->validate();
    }

    /**
     * Genera report di sincronizzazione CDN
     */
    public function generateCDNSyncReport(): array
    {
        $exclusions = $this->getExclusionsForCDN();
        return $this->reportGenerator->generate($exclusions);
    }

    // Metodi rimossi - ora gestiti dalle classi CDNExclusionSync
    // exportToNginx() -> CDNRulesGenerator::exportToNginx()
    // exportToApache() -> CDNRulesGenerator::exportToApache()
    // getRecommendedSyncFrequency() -> CDNReportGenerator::getRecommendedSyncFrequency()
    // generateCDNRecommendations() -> CDNReportGenerator::generateRecommendations()
}
