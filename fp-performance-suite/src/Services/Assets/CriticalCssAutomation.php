<?php

namespace FP\PerfSuite\Services\Assets;

use FP\PerfSuite\Utils\Logger;

/**
 * Critical CSS Automation
 * 
 * Genera e gestisce automaticamente il Critical CSS
 *
 * @package FP\PerfSuite\Services\Assets
 * @author Francesco Passeri
 * @link https://francescopasseri.com
 */
class CriticalCssAutomation
{
    private const OPTION_KEY = 'fp_ps_critical_css_automation';
    private const CACHE_KEY = 'fp_critical_css_cache';

    /**
     * Registra il servizio
     */
    public function register(): void
    {
        $settings = $this->getSettings();

        if (empty($settings['enabled'])) {
            return;
        }

        // Genera critical CSS automaticamente
        if (!empty($settings['auto_generate'])) {
            add_action('save_post', [$this, 'generateOnSave'], 10, 2);
            add_action('switch_theme', [$this, 'regenerateAll']);
        }

        // Inline critical CSS
        add_action('wp_head', [$this, 'inlineCriticalCss'], 1);

        Logger::debug('Critical CSS Automation registered');
    }

    /**
     * Ottiene le impostazioni
     */
    public function getSettings(): array
    {
        $defaults = [
            'enabled' => false,
            'auto_generate' => false,
            'viewport_width' => 1920,
            'viewport_height' => 1080,
            'timeout' => 30,
            'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
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
            Logger::info('Critical CSS Automation settings updated', $updated);
        }

        return $result;
    }

    /**
     * Genera Critical CSS per un URL
     */
    public function generate(string $url): ?string
    {
        // Metodo 1: Usa Critical library PHP se disponibile
        if (class_exists('\\Critical\\Critical')) {
            return $this->generateWithCritical($url);
        }

        // Metodo 2: Estrai CSS above-the-fold manualmente
        return $this->extractAboveTheFoldCss($url);
    }

    /**
     * Genera usando Critical library
     */
    private function generateWithCritical(string $url): ?string
    {
        // Questa funzionalità richiede la libreria Critical
        // Per ora ritorniamo null, da implementare con la libreria
        Logger::warning('Critical library not available');
        return null;
    }

    /**
     * Estrae CSS above-the-fold manualmente
     */
    private function extractAboveTheFoldCss(string $url): ?string
    {
        try {
            // Fetch HTML
            $response = wp_remote_get($url, [
                'timeout' => $this->getSettings()['timeout'],
                'user-agent' => $this->getSettings()['user_agent'],
            ]);

            if (is_wp_error($response)) {
                Logger::error('Failed to fetch URL for Critical CSS', [
                    'url' => $url,
                    'error' => $response->get_error_message(),
                ]);
                return null;
            }

            $html = wp_remote_retrieve_body($response);

            // Estrai tutti i CSS inline e link
            $css = $this->extractCssFromHtml($html);

            // Filtra solo le regole critiche (semplificato)
            $criticalCss = $this->filterCriticalRules($css);

            return $criticalCss;

        } catch (\Exception $e) {
            Logger::error('Critical CSS extraction failed', [
                'url' => $url,
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }

    /**
     * Estrae CSS dall'HTML
     */
    private function extractCssFromHtml(string $html): string
    {
        $css = '';

        // Estrai CSS inline
        preg_match_all('/<style[^>]*>(.*?)<\/style>/is', $html, $matches);
        foreach ($matches[1] as $inlineCss) {
            $css .= $inlineCss . "\n";
        }

        // Estrai CSS da link (solo locali)
        preg_match_all('/<link[^>]+rel=["\']stylesheet["\'][^>]*>/i', $html, $linkMatches);
        foreach ($linkMatches[0] as $link) {
            if (preg_match('/href=["\']([^"\']+)["\']/', $link, $href)) {
                $cssUrl = $href[1];
                
                // Solo CSS locali
                if ($this->isLocalUrl($cssUrl)) {
                    $cssContent = $this->fetchCss($cssUrl);
                    if ($cssContent) {
                        $css .= $cssContent . "\n";
                    }
                }
            }
        }

        return $css;
    }

    /**
     * Filtra regole CSS critiche
     */
    private function filterCriticalRules(string $css): string
    {
        // Questa è una versione semplificata
        // In produzione, dovresti usare un parser CSS appropriato

        $critical = '';

        // Selettori critici comuni
        $criticalSelectors = [
            'html',
            'body',
            'header',
            'nav',
            'main',
            '.site-header',
            '.site-title',
            '.site-nav',
            '.hero',
            'h1',
            'h2',
            'p',
            'a',
            'img',
            'button',
        ];

        // Estrai regole per selettori critici
        foreach ($criticalSelectors as $selector) {
            $pattern = '/' . preg_quote($selector, '/') . '\s*\{[^}]+\}/';
            if (preg_match_all($pattern, $css, $matches)) {
                foreach ($matches[0] as $rule) {
                    $critical .= $rule . "\n";
                }
            }
        }

        // Minifica
        $critical = $this->minifyCss($critical);

        return $critical;
    }

    /**
     * Minifica CSS
     */
    private function minifyCss(string $css): string
    {
        // Rimuovi commenti
        $css = preg_replace('!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $css);
        
        // Rimuovi spazi extra
        $css = preg_replace('/\s+/', ' ', $css);
        
        // Rimuovi spazi prima e dopo caratteri speciali
        $css = preg_replace('/\s*([{}:;,])\s*/', '$1', $css);
        
        return trim($css);
    }

    /**
     * Verifica se URL è locale
     */
    private function isLocalUrl(string $url): bool
    {
        $homeUrl = home_url();
        return strpos($url, $homeUrl) !== false || strpos($url, '//') === false;
    }

    /**
     * Fetch CSS da URL
     */
    private function fetchCss(string $url): ?string
    {
        // Converti URL relativo in assoluto
        if (strpos($url, '//') === false) {
            $url = home_url($url);
        }

        $response = wp_remote_get($url, ['timeout' => 10]);

        if (is_wp_error($response)) {
            return null;
        }

        return wp_remote_retrieve_body($response);
    }

    /**
     * Genera Critical CSS al salvataggio post
     */
    public function generateOnSave(int $postId, \WP_Post $post): void
    {
        // Solo per post pubblicati
        if ($post->post_status !== 'publish') {
            return;
        }

        // Solo per post type pubblici
        if (!is_post_type_viewable($post->post_type)) {
            return;
        }

        $url = get_permalink($postId);
        if (!$url) {
            return;
        }

        $criticalCss = $this->generate($url);

        if ($criticalCss) {
            update_post_meta($postId, '_fp_critical_css', $criticalCss);
            update_post_meta($postId, '_fp_critical_css_generated', time());

            Logger::info('Critical CSS generated for post', ['post_id' => $postId]);
        }
    }

    /**
     * Rigenera tutto al cambio tema
     */
    public function regenerateAll(): void
    {
        // Pulisci cache
        delete_transient(self::CACHE_KEY);

        // Rigenera per homepage
        $homeUrl = home_url();
        $criticalCss = $this->generate($homeUrl);

        if ($criticalCss) {
            update_option('fp_ps_critical_css', $criticalCss);
        }

        Logger::info('Critical CSS regenerated after theme switch');
    }

    /**
     * Inline Critical CSS in head
     */
    public function inlineCriticalCss(): void
    {
        // Prendi Critical CSS specifico per pagina
        $criticalCss = '';

        if (is_singular()) {
            $postId = get_the_ID();
            $postCriticalCss = get_post_meta($postId, '_fp_critical_css', true);
            
            if ($postCriticalCss) {
                $criticalCss = $postCriticalCss;
            }
        }

        // Fallback a Critical CSS globale
        if (empty($criticalCss)) {
            $criticalCss = get_option('fp_ps_critical_css', '');
        }

        if (empty($criticalCss)) {
            return;
        }

        echo '<style id="fp-critical-css">' . $criticalCss . '</style>';
    }

    /**
     * Ottiene Critical CSS per un post
     */
    public function getCriticalCss(?int $postId = null): ?string
    {
        if ($postId) {
            $css = get_post_meta($postId, '_fp_critical_css', true);
            if ($css) {
                return $css;
            }
        }

        return get_option('fp_ps_critical_css', null);
    }

    /**
     * Imposta Critical CSS manualmente
     */
    public function setCriticalCss(string $css, ?int $postId = null): bool
    {
        if ($postId) {
            update_post_meta($postId, '_fp_critical_css', $css);
            update_post_meta($postId, '_fp_critical_css_generated', time());
            return true;
        }

        return update_option('fp_ps_critical_css', $css);
    }

    /**
     * Pulisce Critical CSS
     */
    public function clearCriticalCss(?int $postId = null): bool
    {
        if ($postId) {
            delete_post_meta($postId, '_fp_critical_css');
            delete_post_meta($postId, '_fp_critical_css_generated');
            return true;
        }

        return delete_option('fp_ps_critical_css');
    }

    /**
     * Ottiene statistiche
     */
    public function getStats(): array
    {
        global $wpdb;

        $postsWithCriticalCss = $wpdb->get_var(
            "SELECT COUNT(*) FROM {$wpdb->postmeta} 
            WHERE meta_key = '_fp_critical_css'"
        );

        $globalCriticalCss = get_option('fp_ps_critical_css', '');

        return [
            'enabled' => !empty($this->getSettings()['enabled']),
            'global_css_size' => strlen($globalCriticalCss),
            'posts_with_critical_css' => (int) $postsWithCriticalCss,
            'auto_generation' => !empty($this->getSettings()['auto_generate']),
        ];
    }

    /**
     * Status
     */
    public function status(): array
    {
        return [
            'enabled' => !empty($this->getSettings()['enabled']),
            'settings' => $this->getSettings(),
            'stats' => $this->getStats(),
        ];
    }
}

