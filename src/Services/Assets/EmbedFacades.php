<?php

namespace FP\PerfSuite\Services\Assets;

use FP\PerfSuite\Core\Logging\LoggerInterface;
use FP\PerfSuite\Core\Options\OptionsRepositoryInterface;
use FP\PerfSuite\Utils\Logger as StaticLogger;

use function add_filter;
use function add_action;
use function is_admin;
use function wp_doing_ajax;
use function wp_enqueue_style;
use function wp_add_inline_style;
use function wp_add_inline_script;
use function preg_match;
use function preg_replace_callback;
use function esc_url_raw;
use function esc_attr;
use function esc_url;

/**
 * Embed Facades
 * 
 * Sostituisce iframe pesanti (YouTube, Vimeo, Google Maps) con facades leggere
 * che caricano il contenuto reale solo al click
 * 
 * Risparmio: ~500KB+ per video, ~1MB+ per mappe
 * 
 * @package FP\PerfSuite\Services\Assets
 * @author Francesco Passeri
 * @link https://francescopasseri.com
 */
class EmbedFacades
{
    private const OPTION = 'fp_ps_embed_facades';
    
    /** @var OptionsRepositoryInterface|null Options repository (injected) */
    private ?OptionsRepositoryInterface $optionsRepo = null;
    
    /** @var LoggerInterface|null Logger (injected) */
    private ?LoggerInterface $logger = null;
    
    public function __construct(?OptionsRepositoryInterface $optionsRepo = null, ?LoggerInterface $logger = null)
    {
        $this->optionsRepo = $optionsRepo;
        $this->logger = $logger;
    }
    
    /**
     * Helper per logging con fallback
     * 
     * @param string $level Log level
     * @param string $message Message
     * @param array $context Context
     */
    private function log(string $level, string $message, array $context = []): void
    {
        if ($this->logger !== null) {
            $this->logger->$level($message, $context);
        } else {
            StaticLogger::$level($message, $context);
        }
    }
    
    /**
     * Helper method per ottenere opzioni con fallback
     * 
     * @param string $key Option key
     * @param mixed $default Default value
     * @return mixed
     */
    private function getOption(string $key, $default = [])
    {
        if ($this->optionsRepo !== null) {
            return $this->optionsRepo->get($key, $default);
        }
        
        // Fallback to direct option call for backward compatibility
        return get_option($key, $default);
    }
    
    /**
     * Helper method per salvare opzioni con fallback
     * 
     * @param string $key Option key
     * @param mixed $value Value to save
     * @return bool
     */
    private function setOption(string $key, $value): bool
    {
        if ($this->optionsRepo !== null) {
            $this->optionsRepo->set($key, $value);
            return true;
        }
        
        // Fallback to direct option call for backward compatibility
        return update_option($key, $value, false);
    }
    
    /**
     * Impostazioni del servizio
     */
    public function getSettings(): array
    {
        return $this->getOption(self::OPTION, [
            'enabled' => false,
            'youtube' => true,
            'vimeo' => true,
            'google_maps' => true,
            'thumbnail_quality' => 'hqdefault', // default, mqdefault, hqdefault, sddefault, maxresdefault
            'play_button_style' => 'default', // default, minimal, custom
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
        
        // Validazione
        $validQualities = ['default', 'mqdefault', 'hqdefault', 'sddefault', 'maxresdefault'];
        if (isset($new['thumbnail_quality']) && !in_array($new['thumbnail_quality'], $validQualities, true)) {
            $new['thumbnail_quality'] = 'hqdefault';
        }
        
        $validStyles = ['default', 'minimal', 'custom'];
        if (isset($new['play_button_style']) && !in_array($new['play_button_style'], $validStyles, true)) {
            $new['play_button_style'] = 'default';
        }
        
        $result = $this->setOption(self::OPTION, $new);
        
        if ($result) {
            // FIX: Reinizializza il servizio per applicare immediatamente le modifiche
            $this->forceInit();
        }
        
        return $result;
    }
    
    /**
     * Forza l'inizializzazione del servizio
     * FIX: Ricarica le impostazioni e reinizializza il servizio
     */
    public function forceInit(): void
    {
        // Rimuovi hook esistenti
        remove_filter('the_content', [$this, 'replaceEmbeds'], 20);
        remove_filter('widget_text', [$this, 'replaceEmbeds'], 20);
        remove_action('wp_enqueue_scripts', [$this, 'enqueueAssets']);
        
        // Reinizializza
        $this->register();
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
        
        // Non attivare in admin o AJAX
        if (is_admin() || wp_doing_ajax()) {
            return;
        }
        
        // Filtra il contenuto per sostituire embed
        add_filter('the_content', [$this, 'replaceEmbeds'], 20);
        add_filter('widget_text', [$this, 'replaceEmbeds'], 20);
        
        // Enqueue CSS e JS
        add_action('wp_enqueue_scripts', [$this, 'enqueueAssets']);
        
        $this->log('debug', 'EmbedFacades registered', $settings);
    }
    
    /**
     * Sostituisce embed con facades
     */
    public function replaceEmbeds(string $content): string
    {
        if (empty($content)) {
            return $content;
        }
        
        $settings = $this->getSettings();
        
        // YouTube
        if (!empty($settings['youtube'])) {
            $content = $this->replaceYouTube($content, $settings);
        }
        
        // Vimeo
        if (!empty($settings['vimeo'])) {
            $content = $this->replaceVimeo($content, $settings);
        }
        
        // Google Maps
        if (!empty($settings['google_maps'])) {
            $content = $this->replaceGoogleMaps($content, $settings);
        }
        
        return $content;
    }
    
    /**
     * Sostituisce YouTube iframe con facade
     */
    private function replaceYouTube(string $content, array $settings): string
    {
        // BUGFIX: Protezione contro contenuto troppo grande
        if (strlen($content) > 5000000) { // 5MB limit
            $this->log('warning', 'Content too large for YouTube facade replacement', ['size' => strlen($content)]);
            return $content;
        }
        
        // Pattern per YouTube iframe
        $pattern = '/<iframe[^>]*src=["\'](?:https?:)?\/\/(?:www\.)?(?:youtube\.com\/embed\/|youtu\.be\/)([a-zA-Z0-9_-]+)([^"\']*)["\'][^>]*>.*?<\/iframe>/is';
        
        // BUGFIX: Valida thumbnail quality
        $thumbnailQuality = $settings['thumbnail_quality'] ?? 'hqdefault';
        $validQualities = ['default', 'mqdefault', 'hqdefault', 'sddefault', 'maxresdefault'];
        if (!in_array($thumbnailQuality, $validQualities, true)) {
            $thumbnailQuality = 'hqdefault'; // Fallback sicuro
        }
        
        $result = preg_replace_callback($pattern, function($matches) use ($thumbnailQuality) {
            // BUGFIX: Verifica che i match esistano
            if (!isset($matches[1]) || empty($matches[1])) {
                return $matches[0]; // Ritorna iframe originale se video ID mancante
            }
            
            $videoId = $matches[1];
            $params = $matches[2] ?? '';
            
            // Estrai parametri dall'URL
            $autoplay = strpos($params, 'autoplay=1') !== false ? '&autoplay=1' : '';
            
            // Thumbnail YouTube
            $thumbnail = "https://i.ytimg.com/vi/{$videoId}/{$thumbnailQuality}.jpg";
            
            // Genera facade HTML
            return sprintf(
                '<div class="fp-youtube-facade" data-video-id="%s" data-params="%s" style="background-image: url(\'%s\');">
                    <button class="fp-facade-play-button" aria-label="Play video" type="button">
                        <svg height="100%%" version="1.1" viewBox="0 0 68 48" width="100%%">
                            <path d="M66.52,7.74c-0.78-2.93-2.49-5.41-5.42-6.19C55.79,.13,34,0,34,0S12.21,.13,6.9,1.55 C3.97,2.33,2.27,4.81,1.48,7.74C0.06,13.05,0,24,0,24s0.06,10.95,1.48,16.26c0.78,2.93,2.49,5.41,5.42,6.19 C12.21,47.87,34,48,34,48s21.79-0.13,27.1-1.55c2.93-0.78,4.64-3.26,5.42-6.19C67.94,34.95,68,24,68,24S67.94,13.05,66.52,7.74z" fill="#f00"></path>
                            <path d="M 45,24 27,14 27,34" fill="#fff"></path>
                        </svg>
                    </button>
                </div>',
                esc_attr($videoId),
                esc_attr($params . $autoplay),
                esc_url($thumbnail)
            );
        }, $content);
        
        // BUGFIX: Verifica errori regex
        if (preg_last_error() !== PREG_NO_ERROR) {
            $this->log('error', 'YouTube facade regex error', ['error_code' => preg_last_error()]);
            return $content; // Ritorna contenuto originale se errore
        }
        
        return $result;
    }
    
    /**
     * Sostituisce Vimeo iframe con facade
     */
    private function replaceVimeo(string $content, array $settings): string
    {
        // BUGFIX: Protezione contro contenuto troppo grande
        if (strlen($content) > 5000000) { // 5MB limit
            $this->log('warning', 'Content too large for Vimeo facade replacement', ['size' => strlen($content)]);
            return $content;
        }
        
        // Pattern per Vimeo iframe
        $pattern = '/<iframe[^>]*src=["\'](?:https?:)?\/\/(?:www\.)?player\.vimeo\.com\/video\/(\d+)([^"\']*)["\'][^>]*>.*?<\/iframe>/is';
        
        $result = preg_replace_callback($pattern, function($matches) {
            // BUGFIX: Verifica che i match esistano
            if (!isset($matches[1]) || empty($matches[1])) {
                return $matches[0]; // Ritorna iframe originale se video ID mancante
            }
            
            $videoId = $matches[1];
            $params = $matches[2] ?? '';
            
            // Per Vimeo, dobbiamo fare API call per thumbnail o usare placeholder
            // Per semplicità, usiamo un placeholder generico
            $thumbnail = "https://vumbnail.com/{$videoId}.jpg";
            
            return sprintf(
                '<div class="fp-vimeo-facade" data-video-id="%s" data-params="%s" style="background-image: url(\'%s\');">
                    <button class="fp-facade-play-button" aria-label="Play video" type="button">
                        <svg height="100%%" viewBox="0 0 24 24" width="100%%">
                            <path d="M8 5v14l11-7z" fill="#00adef"/>
                        </svg>
                    </button>
                </div>',
                esc_attr($videoId),
                esc_attr($params),
                esc_url($thumbnail)
            );
        }, $content);
        
        // BUGFIX: Verifica errori regex
        if (preg_last_error() !== PREG_NO_ERROR) {
            $this->log('error', 'Vimeo facade regex error', ['error_code' => preg_last_error()]);
            return $content;
        }
        
        return $result;
    }
    
    /**
     * Sostituisce Google Maps iframe con facade
     */
    private function replaceGoogleMaps(string $content, array $settings): string
    {
        // BUGFIX: Protezione contro contenuto troppo grande
        if (strlen($content) > 5000000) { // 5MB limit
            $this->log('warning', 'Content too large for Maps facade replacement', ['size' => strlen($content)]);
            return $content;
        }
        
        // Pattern per Google Maps iframe
        $pattern = '/<iframe[^>]*src=["\'](?:https?:)?\/\/(?:www\.)?google\.com\/maps\/embed([^"\']*)["\'][^>]*>.*?<\/iframe>/is';
        
        $result = preg_replace_callback($pattern, function($matches) {
            // BUGFIX: Verifica che il match esista
            $embedParams = $matches[1] ?? '';
            
            // BUGFIX: Sanitizza URL per prevenire injection
            $embedParams = esc_url_raw($embedParams);
            
            $fullUrl = 'https://www.google.com/maps/embed' . $embedParams;
            
            // Genera facade HTML
            return sprintf(
                '<div class="fp-maps-facade" data-embed-url="%s">
                    <div class="fp-maps-overlay">
                        <button class="fp-facade-load-button" type="button">
                            <svg width="24" height="24" viewBox="0 0 24 24">
                                <path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z" fill="#4285f4"/>
                            </svg>
                            <span>Load Map</span>
                        </button>
                    </div>
                </div>',
                esc_attr($fullUrl)
            );
        }, $content);
        
        // BUGFIX: Verifica errori regex
        if (preg_last_error() !== PREG_NO_ERROR) {
            $this->log('error', 'Maps facade regex error', ['error_code' => preg_last_error()]);
            return $content;
        }
        
        return $result;
    }
    
    /**
     * Enqueue CSS e JS per facades
     */
    public function enqueueAssets(): void
    {
        // Inline CSS
        $css = $this->getFacadeCSS();
        wp_add_inline_style('wp-block-library', $css);
        
        // Inline JS
        $js = $this->getFacadeJS();
        wp_add_inline_script('jquery', $js, 'after');
    }
    
    /**
     * CSS per facades
     */
    private function getFacadeCSS(): string
    {
        return <<<CSS
/* FP Performance Suite - Embed Facades */
.fp-youtube-facade,
.fp-vimeo-facade {
    position: relative;
    padding-bottom: 56.25%; /* 16:9 ratio */
    height: 0;
    overflow: hidden;
    background-color: #000;
    background-position: center;
    background-size: cover;
    cursor: pointer;
}

.fp-youtube-facade iframe,
.fp-vimeo-facade iframe {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
}

.fp-facade-play-button {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    width: 68px;
    height: 48px;
    background: none;
    border: none;
    cursor: pointer;
    opacity: 0.8;
    transition: opacity 0.3s;
    padding: 0;
}

.fp-facade-play-button:hover {
    opacity: 1;
}

.fp-maps-facade {
    position: relative;
    padding-bottom: 56.25%;
    height: 0;
    overflow: hidden;
    background-color: #e5e3df;
    background-image: 
        linear-gradient(45deg, #f0eeeb 25%, transparent 25%),
        linear-gradient(-45deg, #f0eeeb 25%, transparent 25%),
        linear-gradient(45deg, transparent 75%, #f0eeeb 75%),
        linear-gradient(-45deg, transparent 75%, #f0eeeb 75%);
    background-size: 20px 20px;
    background-position: 0 0, 0 10px, 10px -10px, -10px 0px;
}

.fp-maps-facade iframe {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
}

.fp-maps-overlay {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    background: rgba(255, 255, 255, 0.9);
}

.fp-facade-load-button {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 12px 24px;
    background: #fff;
    border: 2px solid #4285f4;
    border-radius: 4px;
    color: #4285f4;
    font-size: 16px;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.3s;
}

.fp-facade-load-button:hover {
    background: #4285f4;
    color: #fff;
}

.fp-facade-load-button svg {
    width: 24px;
    height: 24px;
}
CSS;
    }
    
    /**
     * JavaScript per facades
     */
    private function getFacadeJS(): string
    {
        return <<<JAVASCRIPT
(function() {
    'use strict';
    
    // FP Performance Suite - Embed Facades
    
    // YouTube facades
    document.addEventListener('click', function(e) {
        const facade = e.target.closest('.fp-youtube-facade');
        if (!facade) return;
        
        const videoId = facade.getAttribute('data-video-id');
        const params = facade.getAttribute('data-params') || '';
        
        // Crea iframe YouTube
        const iframe = document.createElement('iframe');
        iframe.src = 'https://www.youtube.com/embed/' + videoId + '?autoplay=1' + params;
        iframe.allow = 'accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture';
        iframe.allowFullscreen = true;
        iframe.frameBorder = '0';
        
        // Sostituisci facade con iframe
        facade.innerHTML = '';
        facade.appendChild(iframe);
    });
    
    // Vimeo facades
    document.addEventListener('click', function(e) {
        const facade = e.target.closest('.fp-vimeo-facade');
        if (!facade) return;
        
        const videoId = facade.getAttribute('data-video-id');
        const params = facade.getAttribute('data-params') || '';
        
        // Crea iframe Vimeo
        const iframe = document.createElement('iframe');
        iframe.src = 'https://player.vimeo.com/video/' + videoId + '?autoplay=1' + params;
        iframe.allow = 'autoplay; fullscreen; picture-in-picture';
        iframe.allowFullscreen = true;
        iframe.frameBorder = '0';
        
        // Sostituisci facade con iframe
        facade.innerHTML = '';
        facade.appendChild(iframe);
    });
    
    // Google Maps facades
    document.addEventListener('click', function(e) {
        const button = e.target.closest('.fp-facade-load-button');
        if (!button) return;
        
        const facade = button.closest('.fp-maps-facade');
        if (!facade) return;
        
        const embedUrl = facade.getAttribute('data-embed-url');
        
        // Crea iframe Maps
        const iframe = document.createElement('iframe');
        iframe.src = embedUrl;
        iframe.frameBorder = '0';
        iframe.style.border = '0';
        iframe.allowFullscreen = true;
        iframe.loading = 'lazy';
        
        // Sostituisci facade con iframe
        facade.innerHTML = '';
        facade.appendChild(iframe);
    });
    
})();
JAVASCRIPT;
    }
    
    /**
     * Status del servizio
     */
    public function status(): array
    {
        $settings = $this->getSettings();
        
        return [
            'enabled' => !empty($settings['enabled']),
            'youtube' => !empty($settings['youtube']),
            'vimeo' => !empty($settings['vimeo']),
            'google_maps' => !empty($settings['google_maps']),
            'thumbnail_quality' => $settings['thumbnail_quality'] ?? 'hqdefault',
        ];
    }
}

