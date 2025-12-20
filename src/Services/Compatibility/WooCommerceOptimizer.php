<?php

namespace FP\PerfSuite\Services\Compatibility;

use FP\PerfSuite\Core\Options\OptionsRepositoryInterface;
use FP\PerfSuite\Utils\Logger;

use function add_action;
use function add_filter;
use function is_admin;
use function is_cart;
use function is_checkout;
use function is_account_page;
use function is_woocommerce;
use function is_product;
use function is_shop;
use function wp_dequeue_script;
use function wp_dequeue_style;
use function wp_enqueue_script;
use function class_exists;
use function defined;

/**
 * WooCommerce Optimizer
 * 
 * Ottimizzazioni specifiche per WooCommerce per migliorare performance
 * 
 * Features:
 * - Disabilita cart fragments su pagine non-WooCommerce
 * - Lazy load cart widget
 * - Defer WooCommerce scripts su pagine non-checkout
 * - Preload checkout assets
 * - Exclude cart/checkout/account da page cache
 * - Conditional script loading per page type
 * 
 * @package FP\PerfSuite\Services\Compatibility
 * @author Francesco Passeri
 * @link https://francescopasseri.com
 */
class WooCommerceOptimizer
{
    private const OPTION = 'fp_ps_woocommerce';
    private ?OptionsRepositoryInterface $optionsRepo = null;
    
    /**
     * Costruttore
     * 
     * @param OptionsRepositoryInterface|null $optionsRepo Repository opzionale per gestione opzioni
     */
    public function __construct(?OptionsRepositoryInterface $optionsRepo = null)
    {
        $this->optionsRepo = $optionsRepo;
    }
    
    /**
     * Helper per ottenere opzioni con fallback
     * 
     * @param string $key Chiave opzione
     * @param mixed $default Valore di default
     * @return mixed Valore opzione
     */
    private function getOption(string $key, $default = null)
    {
        if ($this->optionsRepo !== null) {
            return $this->optionsRepo->get($key, $default);
        }
        return get_option($key, $default);
    }
    
    /**
     * Helper per salvare opzioni con fallback
     * 
     * @param string $key Chiave opzione
     * @param mixed $value Valore opzione
     * @param bool $autoload Se autoload
     * @return bool True se salvato con successo
     */
    private function setOption(string $key, $value, bool $autoload = true): bool
    {
        if ($this->optionsRepo !== null) {
            return $this->optionsRepo->set($key, $value, $autoload);
        }
        return update_option($key, $value, $autoload);
    }
    
    /**
     * Impostazioni del servizio
     */
    public function getSettings(): array
    {
        return $this->getOption(self::OPTION, [
            'enabled' => false,
            'disable_cart_fragments' => true, // Su non-cart pages
            'lazy_load_cart' => true,
            'defer_non_critical_scripts' => true,
            'exclude_from_cache' => true, // Cart/Checkout/Account
            'disable_password_strength' => true, // Su non-account pages
            'optimize_widgets' => true,
            'conditional_scripts' => true, // Carica script solo su pagine appropriate
        ]);
    }
    
    /**
     * Aggiorna impostazioni
     */
    public function updateSettings(array $settings): bool
    {
        // BUGFIX: Valida che settings sia un array valido
        if (!is_array($settings) || empty($settings)) {
            return false;
        }
        
        $current = $this->getSettings();
        $new = array_merge($current, $settings);
        
        return $this->setOption(self::OPTION, $new, false);
    }
    
    /**
     * Registra il servizio
     */
    public function register(): void
    {
        // Verifica che WooCommerce sia attivo
        if (!$this->isWooCommerceActive()) {
            return;
        }
        
        $settings = $this->getSettings();
        
        if (empty($settings['enabled'])) {
            return;
        }
        
        // Non attivare in admin
        if (is_admin()) {
            return;
        }
        
        // Disabilita cart fragments su pagine non-WooCommerce
        if (!empty($settings['disable_cart_fragments'])) {
            add_action('wp_enqueue_scripts', [$this, 'disableCartFragments'], 999);
        }
        
        // Lazy load cart
        if (!empty($settings['lazy_load_cart'])) {
            add_filter('woocommerce_add_to_cart_fragments', [$this, 'lazyLoadCartFragments'], 10, 1);
        }
        
        // Exclude da cache
        if (!empty($settings['exclude_from_cache'])) {
            add_action('template_redirect', [$this, 'excludeFromCache'], 1);
        }
        
        // Disabilita password strength meter su non-account
        if (!empty($settings['disable_password_strength'])) {
            add_action('wp_enqueue_scripts', [$this, 'disablePasswordStrength'], 999);
        }
        
        // Conditional script loading
        if (!empty($settings['conditional_scripts'])) {
            add_action('wp_enqueue_scripts', [$this, 'conditionalScriptLoading'], 999);
        }
        
        // Ottimizza widgets
        if (!empty($settings['optimize_widgets'])) {
            add_filter('woocommerce_cart_widget_fragment_name', [$this, 'optimizeWidgetFragment']);
        }
        
        Logger::debug('WooCommerceOptimizer registered', $settings);
    }
    
    /**
     * Verifica se WooCommerce è attivo
     */
    private function isWooCommerceActive(): bool
    {
        return class_exists('WooCommerce') || defined('WC_VERSION');
    }
    
    /**
     * Disabilita cart fragments su pagine non-WooCommerce
     * 
     * Risparmio: ~300KB di richieste AJAX non necessarie
     */
    public function disableCartFragments(): void
    {
        // Mantieni attivo solo su pagine WooCommerce
        if (!$this->isWooCommercePage()) {
            wp_dequeue_script('wc-cart-fragments');
            wp_dequeue_script('wc-add-to-cart');
            
            Logger::debug('Cart fragments disabled on non-WC page');
        }
    }
    
    /**
     * Lazy load cart fragments
     */
    public function lazyLoadCartFragments(array $fragments): array
    {
        // Implementa lazy loading per cart widget
        // Invece di aggiornare subito, aspetta interazione
        
        if (isset($fragments['div.widget_shopping_cart_content'])) {
            // Avvolgi in un container lazy
            $fragments['div.widget_shopping_cart_content'] = 
                '<div class="fp-lazy-cart" data-lazy-load="true">' . 
                $fragments['div.widget_shopping_cart_content'] . 
                '</div>';
        }
        
        return $fragments;
    }
    
    /**
     * Exclude cart/checkout/account da page cache
     */
    public function excludeFromCache(): void
    {
        // BUGFIX: Verifica che le funzioni WooCommerce esistano prima di chiamarle
        $isWooPage = $this->isWooCommercePage();
        $isCart = function_exists('is_cart') && is_cart();
        $isCheckout = function_exists('is_checkout') && is_checkout();
        $isAccount = function_exists('is_account_page') && is_account_page();
        
        if ($isWooPage || $isCart || $isCheckout || $isAccount) {
            // Impedisce caching di queste pagine
            if (!defined('DONOTCACHEPAGE')) {
                define('DONOTCACHEPAGE', true);
            }
            
            // Header no-cache aggiuntivi
            if (!headers_sent()) {
                header('Cache-Control: no-cache, no-store, must-revalidate');
                header('Pragma: no-cache');
                header('Expires: 0');
            }
            
            Logger::debug('WooCommerce page excluded from cache');
        }
    }
    
    /**
     * Disabilita password strength meter su pagine non-account
     */
    public function disablePasswordStrength(): void
    {
        // BUGFIX: Verifica che le funzioni esistano
        $isAccount = function_exists('is_account_page') && is_account_page();
        $isCheckout = function_exists('is_checkout') && is_checkout();
        
        if (!$isAccount && !$isCheckout) {
            wp_dequeue_script('wc-password-strength-meter');
            wp_dequeue_script('password-strength-meter');
        }
    }
    
    /**
     * Conditional script loading per tipo di pagina
     */
    public function conditionalScriptLoading(): void
    {
        // BUGFIX: Verifica che le funzioni esistano
        // Checkout scripts solo su checkout
        if (function_exists('is_checkout') && !is_checkout()) {
            wp_dequeue_script('wc-checkout');
            wp_dequeue_script('wc-credit-card-form');
        }
        
        // Single product scripts solo su product pages
        if (function_exists('is_product') && !is_product()) {
            wp_dequeue_script('wc-single-product');
            wp_dequeue_script('photoswipe');
            wp_dequeue_script('photoswipe-ui-default');
            wp_dequeue_style('photoswipe');
            wp_dequeue_style('photoswipe-default-skin');
        }
        
        // Cart scripts solo su cart
        if (function_exists('is_cart') && !is_cart()) {
            wp_dequeue_script('wc-cart');
        }
        
        Logger::debug('WooCommerce conditional scripts applied');
    }
    
    /**
     * Ottimizza widget fragment
     */
    public function optimizeWidgetFragment(string $fragmentName): string
    {
        // Usa fragment name più specifico per migliore caching
        return $fragmentName . '_optimized';
    }
    
    /**
     * Verifica se siamo su una pagina WooCommerce
     */
    private function isWooCommercePage(): bool
    {
        if (!function_exists('is_woocommerce')) {
            return false;
        }
        
        return is_woocommerce() || 
               is_cart() || 
               is_checkout() || 
               is_account_page() || 
               is_product() || 
               is_shop();
    }
    
    /**
     * Status del servizio
     */
    public function status(): array
    {
        $settings = $this->getSettings();
        
        return [
            'enabled' => !empty($settings['enabled']),
            'woocommerce_active' => $this->isWooCommerceActive(),
            'cart_fragments_disabled' => !empty($settings['disable_cart_fragments']),
            'cache_exclusion' => !empty($settings['exclude_from_cache']),
            'conditional_scripts' => !empty($settings['conditional_scripts']),
        ];
    }
    
    /**
     * Ottieni statistiche ottimizzazioni
     */
    public function getOptimizationStats(): array
    {
        $stats = [
            'scripts_dequeued' => 0,
            'styles_dequeued' => 0,
            'pages_excluded_cache' => 0,
            'estimated_savings_kb' => 0,
        ];
        
        // Calcola risparmio stimato
        if ($this->getSettings()['disable_cart_fragments'] ?? false) {
            $stats['estimated_savings_kb'] += 300; // wc-cart-fragments
        }
        
        if ($this->getSettings()['disable_password_strength'] ?? false) {
            $stats['estimated_savings_kb'] += 50;
        }
        
        if ($this->getSettings()['conditional_scripts'] ?? false) {
            $stats['estimated_savings_kb'] += 200; // Various conditional scripts
        }
        
        return $stats;
    }
}

