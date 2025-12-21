<?php

namespace FP\PerfSuite\Services\Media;

use FP\PerfSuite\Core\Options\OptionsRepositoryInterface;

/**
 * Lazy Load Manager per Media
 * 
 * Gestisce lazy loading di immagini, iframe e video
 * 
 * @package FP\PerfSuite\Services\Media
 * @author Francesco Passeri
 */
class LazyLoadManager
{
    private bool $enabled = false;
    private bool $lazy_load_iframes = true;
    private bool $lazy_load_videos = true;
    private int $threshold = 200;
    private ?OptionsRepositoryInterface $optionsRepo = null;
    
    /**
     * Costruttore
     * 
     * @param OptionsRepositoryInterface|null $optionsRepo Repository opzionale per gestione opzioni
     */
    public function __construct(?OptionsRepositoryInterface $optionsRepo = null)
    {
        $this->optionsRepo = $optionsRepo;
        $settings = $this->getSettings();
        $this->enabled = $settings['enabled'] ?? false;
        $this->lazy_load_iframes = $settings['lazy_load_iframes'] ?? true;
        $this->lazy_load_videos = $settings['lazy_load_videos'] ?? true;
        $this->threshold = $settings['threshold'] ?? 200;
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
     * Inizializza il servizio
     */
    public function init(): void
    {
        if (!$this->enabled || is_admin()) {
            return;
        }
        
        // Aggiungi attributo loading="lazy" alle immagini
        add_filter('wp_get_attachment_image_attributes', [$this, 'addLazyLoadAttribute'], 10, 3);
        add_filter('the_content', [$this, 'addLazyLoadToContent'], 999);
        
        // Script per lazy loading avanzato
        add_action('wp_footer', [$this, 'addLazyLoadScript'], 99);
    }
    
    /**
     * Aggiunge attributo lazy alle immagini WordPress
     */
    public function addLazyLoadAttribute($attr, $attachment, $size)
    {
        if (!isset($attr['loading'])) {
            $attr['loading'] = 'lazy';
        }
        
        return $attr;
    }
    
    /**
     * Aggiunge lazy loading al contenuto
     */
    public function addLazyLoadToContent($content)
    {
        if (is_admin() || is_feed()) {
            return $content;
        }
        
        // Aggiungi loading="lazy" a img
        $content = preg_replace('/<img(?![^>]*loading=)/i', '<img loading="lazy"', $content);
        
        // Lazy load iframe se abilitato
        if ($this->lazy_load_iframes) {
            $content = preg_replace('/<iframe(?![^>]*loading=)/i', '<iframe loading="lazy"', $content);
        }
        
        return $content;
    }
    
    /**
     * Aggiunge script per lazy loading
     */
    public function addLazyLoadScript(): void
    {
        if (!$this->enabled) {
            return;
        }
        
        ?>
        <script>
        // Lazy Load Observer
        if ('IntersectionObserver' in window) {
            const lazyImages = document.querySelectorAll('img[loading="lazy"]');
            const imageObserver = new IntersectionObserver(function(entries, observer) {
                entries.forEach(function(entry) {
                    if (entry.isIntersecting) {
                        const img = entry.target;
                        if (img.dataset.src) {
                            img.src = img.dataset.src;
                            img.removeAttribute('data-src');
                        }
                        observer.unobserve(img);
                    }
                });
            }, {
                rootMargin: '<?php echo $this->threshold; ?>px'
            });
            
            lazyImages.forEach(function(img) {
                imageObserver.observe(img);
            });
        }
        </script>
        <?php
    }
    
    /**
     * Ottiene le impostazioni
     */
    public function getSettings(): array
    {
        $saved = $this->getOption('fp_ps_lazy_load', []);
        
        return [
            'enabled' => $saved['enabled'] ?? $this->enabled,
            'lazy_load_iframes' => $saved['lazy_load_iframes'] ?? $this->lazy_load_iframes,
            'lazy_load_videos' => $saved['lazy_load_videos'] ?? $this->lazy_load_videos,
            'threshold' => $saved['threshold'] ?? $this->threshold,
        ];
    }
    
    /**
     * Aggiorna le impostazioni
     */
    public function updateSettings(array $settings): bool
    {
        $current = $this->getOption('fp_ps_lazy_load', []);
        $new = array_merge($current, $settings);
        
        // Validazione
        $new['enabled'] = (bool) ($new['enabled'] ?? false);
        $new['lazy_load_iframes'] = (bool) ($new['lazy_load_iframes'] ?? true);
        $new['lazy_load_videos'] = (bool) ($new['lazy_load_videos'] ?? true);
        $new['threshold'] = max(0, (int) ($new['threshold'] ?? 200));
        
        $result = $this->setOption('fp_ps_lazy_load', $new, false);
        
        if ($result) {
            $this->enabled = $new['enabled'];
            $this->lazy_load_iframes = $new['lazy_load_iframes'];
            $this->lazy_load_videos = $new['lazy_load_videos'];
            $this->threshold = $new['threshold'];
            
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
        remove_filter('wp_get_attachment_image_attributes', [$this, 'addLazyLoadAttribute'], 10);
        remove_filter('the_content', [$this, 'addLazyLoadToContent'], 999);
        remove_action('wp_footer', [$this, 'addLazyLoadScript'], 99);
        
        // Ricarica le impostazioni dal database
        $settings = $this->getSettings();
        $this->enabled = $settings['enabled'] ?? false;
        $this->lazy_load_iframes = $settings['lazy_load_iframes'] ?? true;
        $this->lazy_load_videos = $settings['lazy_load_videos'] ?? true;
        $this->threshold = $settings['threshold'] ?? 200;
        
        // Reinizializza
        $this->init();
    }
    
    /**
     * Restituisce le impostazioni (alias)
     */
    public function settings(): array
    {
        return $this->getSettings();
    }
    
    /**
     * Registra il servizio
     */
    public function register(): void
    {
        $this->init();
    }
}

