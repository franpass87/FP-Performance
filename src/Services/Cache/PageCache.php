<?php

namespace FP\PerfSuite\Services\Cache;

use FP\PerfSuite\Core\Options\OptionsRepositoryInterface;
use FP\PerfSuite\Services\Cache\PageCache\CacheFileManager;
use FP\PerfSuite\Services\Cache\PageCache\UrlNormalizer;
use FP\PerfSuite\Services\Cache\PageCache\CachePurger;
use FP\PerfSuite\Services\Cache\PageCache\AutoPurgeHandler;
use FP\PerfSuite\Utils\ErrorHandler;

class PageCache
{
    private const OPTION_KEY = 'fp_ps_page_cache_settings';
    
    private $cache_dir;
    private $ttl;
    private CacheFileManager $fileManager;
    private UrlNormalizer $urlNormalizer;
    private CachePurger $purger;
    private AutoPurgeHandler $autoPurgeHandler;
    
    /** @var OptionsRepositoryInterface|null Options repository (injected) */
    private ?OptionsRepositoryInterface $optionsRepo = null;
    
    public function __construct($cache_dir = null, $ttl = 3600, ?OptionsRepositoryInterface $optionsRepo = null)
    {
        $this->cache_dir = $cache_dir ?: WP_CONTENT_DIR . '/cache/fp-performance/page-cache';
        $this->ttl = $ttl;
        $this->optionsRepo = $optionsRepo;
        
        if (!file_exists($this->cache_dir)) {
            wp_mkdir_p($this->cache_dir);
        }
        
        // Inizializza le classi helper
        $this->fileManager = new CacheFileManager($this->cache_dir);
        $this->urlNormalizer = new UrlNormalizer();
        $this->purger = new CachePurger(
            $this->cache_dir,
            $this->fileManager,
            $this->urlNormalizer,
            [$this, 'delete']
        );
        $this->autoPurgeHandler = new AutoPurgeHandler(
            $this->purger,
            [$this, 'settings'],
            [$this->purger, 'purgePost']
        );
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
    
    public function get($key)
    {
        // SICUREZZA: Validiamo la chiave per prevenire path traversal
        if (empty($key) || !is_string($key)) {
            return false;
        }
        
        $file = $this->fileManager->getCacheFile($key);
        
        // SICUREZZA: Verifichiamo che il file sia nella directory cache
        if (!$this->fileManager->isValidCacheFile($file)) {
            return false;
        }
        
        if (!file_exists($file) || !is_readable($file)) {
            return false;
        }
        
        try {
            $data = file_get_contents($file);
            if ($data === false) {
                return false;
            }
            
            // SICUREZZA: Validiamo i dati prima di unserialize per prevenire object injection
            $cache_data = $this->fileManager->safeUnserialize($data);
            
            if (!is_array($cache_data) || !isset($cache_data['expires'])) {
                $this->delete($key);
                return false;
            }
            
            if ($cache_data['expires'] < time()) {
                $this->delete($key);
                return false;
            }
            
            return $cache_data['content'];
        } catch (\Exception $e) {
            ErrorHandler::handleSilently($e, 'PageCache read');
            return false;
        }
    }
    
    public function set($key, $content)
    {
        // SICUREZZA: Validiamo la chiave per prevenire path traversal
        if (empty($key) || !is_string($key)) {
            return false;
        }
        
        $file = $this->fileManager->getCacheFile($key);
        
        // SICUREZZA: Verifichiamo che il file sia nella directory cache
        if (!$this->fileManager->isValidCacheFile($file)) {
            return false;
        }
        
        $cache_data = [
            'content' => $content,
            'expires' => time() + $this->ttl
        ];
        
        try {
            $result = file_put_contents($file, serialize($cache_data), LOCK_EX);
            return $result !== false;
        } catch (\Exception $e) {
            ErrorHandler::handleSilently($e, 'PageCache write');
            return false;
        }
    }
    
    public function delete($key)
    {
        // SECURITY FIX: Validazione chiave e path
        if (empty($key) || !is_string($key)) {
            return false;
        }
        
        $file = $this->fileManager->getCacheFile($key);
        
        // SECURITY FIX: Verifica che il file sia nella directory cache autorizzata
        if (!$this->isValidCacheFile($file)) {
            error_log('FP Performance Suite: Tentativo di eliminare file fuori dalla cache directory: ' . $file);
            return false;
        }
        
        if (!file_exists($file)) {
            return true; // GiÃ  eliminato
        }
        
        return @unlink($file);
    }
    
    public function clear()
    {
        // FIX: Gestisci errori da glob() e unlink()
        if (!file_exists($this->cache_dir) || !is_readable($this->cache_dir)) {
            error_log('FP Performance Suite: Cache directory non accessibile');
            return false;
        }
        
        $files = glob($this->cache_dir . '/*');
        
        if ($files === false) {
            error_log('FP Performance Suite: glob() fallito su cache directory');
            return false;
        }
        
        $errors = 0;
        $deleted = 0;
        
        foreach ($files as $file) {
            // Skip directories e file non validi
            if (!is_file($file) || !$this->fileManager->isValidCacheFile($file)) {
                continue;
            }
            
            if (@unlink($file)) {
                $deleted++;
            } else {
                $errors++;
                error_log('FP Performance Suite: Impossibile eliminare file cache: ' . basename($file));
            }
        }
        
        if ($errors > 0) {
            error_log("FP Performance Suite: Clear completato con {$errors} errori. Eliminati {$deleted} file.");
        }
        
        // Ritorna true solo se nessun errore
        return $errors === 0;
    }
    
    // Metodi getCacheFile(), isValidCacheFile(), safeUnserialize() rimossi - ora gestiti da CacheFileManager
    
    /**
     * Verifica se la page cache Ã¨ abilitata
     * 
     * @return bool
     */
    public function isEnabled(): bool
    {
        $settings = $this->getOption(self::OPTION_KEY, []);
        return isset($settings['enabled']) && $settings['enabled'];
    }
    
    /**
     * Restituisce le impostazioni della page cache
     * 
     * @return array Array con le impostazioni
     */
    public function settings(): array
    {
        $settings = $this->getOption(self::OPTION_KEY, []);
        
        return [
            'enabled' => isset($settings['enabled']) && $settings['enabled'],
            'ttl' => $settings['ttl'] ?? $this->ttl,
            'cache_dir' => $this->cache_dir,
            'exclude_urls' => $settings['exclude_urls'] ?? [],
            'exclude_cookies' => $settings['exclude_cookies'] ?? [],
            'exclude_query_strings' => $settings['exclude_query_strings'] ?? [],
        ];
    }
    
    /**
     * Aggiorna le impostazioni della cache
     * 
     * @param array $settings Nuove impostazioni
     * @return bool True se salvato con successo
     */
    public function update(array $settings): bool
    {
        $currentSettings = $this->getOption(self::OPTION_KEY, []);
        $newSettings = array_merge($currentSettings, $settings);
        
        // Validazione
        if (isset($newSettings['enabled'])) {
            $newSettings['enabled'] = (bool) $newSettings['enabled'];
        }
        
        if (isset($newSettings['ttl'])) {
            $newSettings['ttl'] = max(0, (int) $newSettings['ttl']);
            // Se TTL Ã¨ 0, disabilita la cache
            if ($newSettings['ttl'] === 0) {
                $newSettings['enabled'] = false;
            }
        }
        
        // Valida array di esclusioni
        if (isset($newSettings['exclude_urls']) && !is_array($newSettings['exclude_urls'])) {
            $newSettings['exclude_urls'] = [];
        }
        if (isset($newSettings['exclude_cookies']) && !is_array($newSettings['exclude_cookies'])) {
            $newSettings['exclude_cookies'] = [];
        }
        if (isset($newSettings['exclude_query_strings']) && !is_array($newSettings['exclude_query_strings'])) {
            $newSettings['exclude_query_strings'] = [];
        }
        
        $result = $this->setOption(self::OPTION_KEY, $newSettings);
        
        if ($result && isset($newSettings['ttl'])) {
            $this->ttl = $newSettings['ttl'];
        }
        
        return $result;
    }
    
    /**
     * Restituisce lo stato della cache
     * 
     * @return array Array con 'enabled' e altre informazioni
     */
    public function status(): array
    {
        $enabled = $this->isEnabled();
        
        return [
            'enabled' => $enabled,
            'ttl' => $this->ttl,
            'cache_dir' => $this->cache_dir,
            'dir_exists' => file_exists($this->cache_dir),
            'dir_writable' => is_writable($this->cache_dir),
            'files' => $this->countCachedFiles(),
        ];
    }
    
    /**
     * Conta i file presenti nella cache
     * 
     * @return int Numero di file in cache
     */
    private function countCachedFiles(): int
    {
        if (!file_exists($this->cache_dir) || !is_readable($this->cache_dir)) {
            return 0;
        }
        
        try {
            $iterator = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator(
                    $this->cache_dir,
                    \RecursiveDirectoryIterator::SKIP_DOTS
                ),
                \RecursiveIteratorIterator::LEAVES_ONLY
            );
            
            $count = 0;
            foreach ($iterator as $file) {
                if ($file->isFile() && $file->getExtension() === 'cache') {
                    $count++;
                }
            }
            
            return $count;
        } catch (\Exception $e) {
            // Se c'Ã¨ un errore (permessi, etc), restituiamo 0
            return 0;
        }
    }
    
    /**
     * Purga la cache per un URL specifico
     * 
     * @param string $url URL da purgare
     * @return bool True se purgato con successo
     */
    public function purgeUrl(string $url): bool
    {
        return $this->purger->purgeUrl($url);
    }
    
    /**
     * Purga la cache per un post specifico
     * 
     * @param int $postId ID del post
     * @return int Numero di URL purgati
     */
    public function purgePost(int $postId): int
    {
        return $this->purger->purgePost($postId);
    }
    
    /**
     * Purga la cache usando un pattern
     * 
     * @param string $pattern Pattern regex o wildcard
     * @return int Numero di file purgati
     */
    public function purgePattern(string $pattern): int
    {
        return $this->purger->purgePattern($pattern);
    }
    
    // Metodi getPostUrls(), urlToKey(), normalizeUrl(), patternToRegex() rimossi - ora gestiti da:
    // - CachePurger (getPostUrls)
    // - UrlNormalizer (urlToKey, normalizeUrl, patternToRegex)
    
    // Metodi getPostUrls(), urlToKey(), normalizeUrl(), patternToRegex() rimossi - ora gestiti da:
    // - CachePurger (getPostUrls)
    // - UrlNormalizer (urlToKey, normalizeUrl, patternToRegex)
    
    /**
     * Registra il servizio
     */
    public function register(): void
    {
        // BUGFIX #8: Aggiunto hook per GENERARE la cache (era completamente mancante!)
        // Intercetta richieste e serve/genera cache
        add_action('template_redirect', [$this, 'serveOrCachePage'], 1);
        
        // Hook per auto-purge quando un post viene aggiornato
        add_action('save_post', [$this, 'autoPurgePost'], 10, 1);
        add_action('deleted_post', [$this, 'autoPurgePost'], 10, 1);
        add_action('trashed_post', [$this, 'autoPurgePost'], 10, 1);
        
        // Hook per auto-purge commenti
        add_action('comment_post', [$this, 'autoPurgePostOnComment'], 10, 2);
        add_action('edit_comment', [$this, 'autoPurgeCommentPost'], 10, 1);
        
        // Cleanup cache scaduta periodicamente
        if (!wp_next_scheduled('fp_ps_cache_cleanup')) {
            wp_schedule_event(time(), 'hourly', 'fp_ps_cache_cleanup');
        }
        add_action('fp_ps_cache_cleanup', [$this, 'cleanupExpiredCache']);
    }
    
    /**
     * BUGFIX #8: Metodo per servire cache esistente o generarne una nuova
     * Questo Ã¨ il cuore del sistema di page caching
     */
    public function serveOrCachePage(): void
    {
        // Solo per richieste GET
        if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'GET') {
            return;
        }
        
        // Non cachare admin, login, o utenti loggati
        if (is_admin() || is_user_logged_in() || is_404()) {
            return;
        }
        
        // Non cachare richieste AJAX o POST
        if (defined('DOING_AJAX') && DOING_AJAX) {
            return;
        }
        
        // Chiave cache basata su URL completo
        $cache_key = $_SERVER['REQUEST_URI'] ?? '/';
        
        // Prova a servire da cache
        $cached_content = $this->get($cache_key);
        if ($cached_content !== false) {
            // Serve dalla cache e termina
            header('X-FP-Cache: HIT');
            echo $cached_content;
            exit;
        }
        
        // Cache miss - avvia output buffering per catturare HTML
        header('X-FP-Cache: MISS');
        ob_start(function($buffer) use ($cache_key) {
            // Salva in cache solo se HTTP 200 OK
            if (http_response_code() === 200 && !empty($buffer)) {
                $this->set($cache_key, $buffer);
            }
            return $buffer;
        });
    }
    
    /**
     * Auto-purge cache quando un post viene modificato
     */
    public function autoPurgePost(int $postId): void
    {
        $this->autoPurgeHandler->autoPurgePost($postId);
    }
    
    /**
     * Auto-purge cache quando un commento viene aggiunto
     */
    public function autoPurgePostOnComment(int $commentId, $approved): void
    {
        $this->autoPurgeHandler->autoPurgePostOnComment($commentId, $approved);
    }
    
    /**
     * Auto-purge cache quando un commento viene modificato
     */
    public function autoPurgeCommentPost(int $commentId): void
    {
        $this->autoPurgeHandler->autoPurgeCommentPost($commentId);
    }
    
    /**
     * Cleanup cache scaduta
     */
    public function cleanupExpiredCache(): void
    {
        if (!file_exists($this->cache_dir) || !is_readable($this->cache_dir)) {
            return;
        }
        
        try {
            $files = glob($this->cache_dir . '/*.cache');
            
            if ($files === false) {
                return;
            }
            
            $removed = 0;
            $now = time();
            
            foreach ($files as $file) {
                if (!$this->fileManager->isValidCacheFile($file)) {
                    continue;
                }
                
                // Leggi e controlla scadenza
                $data = @file_get_contents($file);
                if ($data === false) {
                    continue;
                }
                
                $cache_data = $this->fileManager->safeUnserialize($data);
                
                if (!is_array($cache_data) || !isset($cache_data['expires'])) {
                    // File corrotto, rimuovi
                    @unlink($file);
                    $removed++;
                    continue;
                }
                
                if ($cache_data['expires'] < $now) {
                    // Scaduto, rimuovi
                    @unlink($file);
                    $removed++;
                }
            }
            
            if ($removed > 0) {
                error_log("FP Performance Suite: Rimossi {$removed} file cache scaduti");
            }
        } catch (\Exception $e) {
            ErrorHandler::handleSilently($e, 'PageCache cleanupExpiredCache');
        }
    }
}