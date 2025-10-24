<?php

namespace FP\PerfSuite\Http\Ajax;

use FP\PerfSuite\ServiceContainer;
use FP\PerfSuite\Services\Assets\Optimizer;
use FP\PerfSuite\Services\Cache\Headers;
use FP\PerfSuite\Services\Cache\PageCache;
use FP\PerfSuite\Utils\Logger;

use function check_ajax_referer;
use function current_user_can;
use function update_option;
use function wp_send_json_error;
use function wp_send_json_success;

/**
 * Recommendations AJAX Handlers
 * 
 * Gestisce l'applicazione automatica dei suggerimenti dalla overview
 *
 * @package FP\PerfSuite\Http\Ajax
 * @author Francesco Passeri
 */
class RecommendationsAjax
{
    private ServiceContainer $container;
    
    public function __construct(ServiceContainer $container)
    {
        $this->container = $container;
    }
    
    public function register(): void
    {
        add_action('wp_ajax_fp_ps_apply_recommendation', [$this, 'applyRecommendation']);
    }
    
    /**
     * Applica un suggerimento automaticamente
     */
    public function applyRecommendation(): void
    {
        check_ajax_referer('fp_ps_apply_recommendation', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => __('Permessi insufficienti', 'fp-performance-suite')], 403);
            return;
        }
        
        $actionId = sanitize_text_field($_POST['action_id'] ?? '');
        
        if (empty($actionId)) {
            wp_send_json_error(['message' => __('ID azione non specificato', 'fp-performance-suite')], 400);
            return;
        }
        
        try {
            $result = $this->executeAction($actionId);
            
            if ($result) {
                Logger::info('Raccomandazione applicata', ['action_id' => $actionId]);
                wp_send_json_success([
                    'message' => __('Suggerimento applicato con successo!', 'fp-performance-suite'),
                    'action_id' => $actionId,
                ]);
            } else {
                wp_send_json_error(['message' => __('Impossibile applicare il suggerimento', 'fp-performance-suite')], 500);
            }
        } catch (\Exception $e) {
            Logger::error('Errore applicazione raccomandazione', $e);
            wp_send_json_error(['message' => __('Errore: ', 'fp-performance-suite') . $e->getMessage()], 500);
        }
    }
    
    /**
     * Esegue l'azione specifica
     */
    private function executeAction(string $actionId): bool
    {
        switch ($actionId) {
            case 'enable_page_cache':
                return $this->enablePageCache();
                
            case 'enable_browser_cache':
                return $this->enableBrowserCache();
                
            case 'enable_minify_html':
                return $this->enableMinifyHtml();
                
            case 'enable_defer_js':
                return $this->enableDeferJs();
                
            case 'remove_emojis':
                return $this->removeEmojis();
                
            default:
                return false;
        }
    }
    
    private function enablePageCache(): bool
    {
        $pageCache = $this->container->get(PageCache::class);
        $settings = get_option('fp_ps_cache', []);
        $settings['enabled'] = true;
        $settings['ttl'] = $settings['ttl'] ?? 3600;
        return update_option('fp_ps_cache', $settings);
    }
    
    private function enableBrowserCache(): bool
    {
        $headers = $this->container->get(Headers::class);
        $settings = get_option('fp_ps_cache_headers', []);
        $settings['enabled'] = true;
        $settings['max_age'] = $settings['max_age'] ?? 2592000; // 30 giorni
        return update_option('fp_ps_cache_headers', $settings);
    }
    
    private function enableMinifyHtml(): bool
    {
        $settings = get_option('fp_ps_assets', []);
        $settings['minify_html'] = true;
        return update_option('fp_ps_assets', $settings);
    }
    
    private function enableDeferJs(): bool
    {
        $settings = get_option('fp_ps_assets', []);
        $settings['defer_js'] = true;
        return update_option('fp_ps_assets', $settings);
    }
    
    private function removeEmojis(): bool
    {
        $settings = get_option('fp_ps_assets', []);
        $settings['remove_emojis'] = true;
        return update_option('fp_ps_assets', $settings);
    }
}
