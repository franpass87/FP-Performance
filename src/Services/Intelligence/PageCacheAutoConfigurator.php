<?php

namespace FP\PerfSuite\Services\Intelligence;

use FP\PerfSuite\Core\Options\OptionsRepositoryInterface;
use FP\PerfSuite\Utils\Logger;

/**
 * Page Cache Auto Configurator
 * 
 * Configura automaticamente esclusioni cache basandosi sul sito
 *
 * @package FP\PerfSuite\Services\Intelligence
 * @author Francesco Passeri
 * @link https://francescopasseri.com
 */
class PageCacheAutoConfigurator
{
    private SmartExclusionDetector $detector;
    
    /**
     * @var OptionsRepositoryInterface|null
     */
    private $optionsRepo;

    /**
     * Constructor
     * 
     * @param SmartExclusionDetector $detector Smart exclusion detector instance
     * @param OptionsRepositoryInterface|null $optionsRepo Options repository instance
     */
    public function __construct(SmartExclusionDetector $detector, ?OptionsRepositoryInterface $optionsRepo = null)
    {
        $this->detector = $detector;
        $this->optionsRepo = $optionsRepo;
    }

    /**
     * Auto-configura cache
     */
    public function autoConfigure(): array
    {
        $exclusions = [];

        // Rileva plugin e-commerce
        if ($this->hasEcommerce()) {
            $exclusions = array_merge($exclusions, $this->getEcommerceExclusions());
        }

        // Rileva membership
        if ($this->hasMembership()) {
            $exclusions = array_merge($exclusions, $this->getMembershipExclusions());
        }

        // Rileva form
        if ($this->hasForms()) {
            $exclusions = array_merge($exclusions, $this->getFormExclusions());
        }

        // Rileva user-specific content
        if ($this->hasUserContent()) {
            $exclusions = array_merge($exclusions, $this->getUserContentExclusions());
        }

        $exclusions = array_unique($exclusions);

        Logger::info('Auto-configured cache exclusions', ['count' => count($exclusions)]);

        return $exclusions;
    }

    /**
     * Verifica e-commerce
     */
    private function hasEcommerce(): bool
    {
        return class_exists('WooCommerce') || 
               class_exists('Easy_Digital_Downloads') ||
               function_exists('edd_get_option');
    }

    /**
     * Esclusioni e-commerce
     */
    private function getEcommerceExclusions(): array
    {
        $exclusions = [];

        if (class_exists('WooCommerce')) {
            $exclusions[] = '/cart/';
            $exclusions[] = '/checkout/';
            $exclusions[] = '/my-account/';
            $exclusions[] = '/shop/';
            $exclusions[] = '?add-to-cart=';
            $exclusions[] = '?removed_item=';
        }

        return $exclusions;
    }

    /**
     * Verifica membership
     */
    private function hasMembership(): bool
    {
        return class_exists('MeprUser') || 
               class_exists('\\memberpress\\mepr') ||
               function_exists('pmpro_hasMembershipLevel');
    }

    /**
     * Esclusioni membership
     */
    private function getMembershipExclusions(): array
    {
        return [
            '/members/',
            '/member/',
            '/membership/',
            '/account/',
            '/profile/',
        ];
    }

    /**
     * Verifica form
     */
    private function hasForms(): bool
    {
        return class_exists('GFForms') ||
               defined('WPCF7_VERSION') ||
               class_exists('FrmForm');
    }

    /**
     * Esclusioni form
     */
    private function getFormExclusions(): array
    {
        $exclusions = [];

        // Contact Form 7
        if (defined('WPCF7_VERSION')) {
            $exclusions[] = '?wpcf7=';
        }

        // Gravity Forms
        if (class_exists('GFForms')) {
            $exclusions[] = '?gf_page=';
        }

        return $exclusions;
    }

    /**
     * Verifica contenuto utente
     */
    private function hasUserContent(): bool
    {
        return function_exists('bp_is_active') || 
               function_exists('is_bbpress');
    }

    /**
     * Esclusioni contenuto utente
     */
    private function getUserContentExclusions(): array
    {
        $exclusions = [];

        // BuddyPress
        if (function_exists('bp_is_active')) {
            $exclusions[] = '/members/';
            $exclusions[] = '/activity/';
            $exclusions[] = '/groups/';
        }

        // bbPress
        if (function_exists('is_bbpress')) {
            $exclusions[] = '/forums/';
        }

        return $exclusions;
    }

    /**
     * Ottiene raccomandazioni
     */
    public function getRecommendations(): array
    {
        $recommendations = [];

        $autoExclusions = $this->autoConfigure();
        
        if (!empty($autoExclusions)) {
            $recommendations[] = [
                'type' => 'auto_exclusions',
                'title' => __('Esclusioni automatiche rilevate', 'fp-performance-suite'),
                'description' => sprintf(
                    __('Rilevate %d pagine che dovrebbero essere escluse dalla cache', 'fp-performance-suite'),
                    count($autoExclusions)
                ),
                'exclusions' => $autoExclusions,
                'priority' => 'high',
            ];
        }

        return $recommendations;
    }

    /**
     * Applica configurazione automatica
     */
    public function apply(): bool
    {
        $exclusions = $this->autoConfigure();
        
        // Salva nelle opzioni cache
        $currentExclusions = $this->getOption('fp_ps_cache_exclusions', []);
        $newExclusions = array_unique(array_merge($currentExclusions, $exclusions));
        
        return $this->setOption('fp_ps_cache_exclusions', $newExclusions);
    }
    
    /**
     * Registra il servizio
     */
    public function register(): void
    {
        // PageCacheAutoConfigurator non ha hook specifici da registrare
        // È utilizzato principalmente per configurazione on-demand
    }

    /**
     * Get option value (with fallback)
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

    /**
     * Set option value (with fallback)
     * 
     * @param string $key Option key
     * @param mixed $value Value to set
     * @return bool
     */
    private function setOption(string $key, $value): bool
    {
        if ($this->optionsRepo !== null) {
            return $this->optionsRepo->set($key, $value);
        }
        return update_option($key, $value);
    }
}

