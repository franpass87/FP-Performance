<?php

namespace FP\PerfSuite\Services\Cache;

class PageCache
{
    private $cache_dir;
    private $ttl;
    
    public function __construct($cache_dir = null, $ttl = 3600)
    {
        $this->cache_dir = $cache_dir ?: WP_CONTENT_DIR . '/cache/fp-performance/page-cache';
        $this->ttl = $ttl;
        
        if (!file_exists($this->cache_dir)) {
            wp_mkdir_p($this->cache_dir);
        }
    }
    
    public function get($key)
    {
        // SICUREZZA: Validiamo la chiave per prevenire path traversal
        if (empty($key) || !is_string($key)) {
            return false;
        }
        
        $file = $this->getCacheFile($key);
        
        // SICUREZZA: Verifichiamo che il file sia nella directory cache
        if (!$this->isValidCacheFile($file)) {
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
            $cache_data = $this->safeUnserialize($data);
            
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
            error_log('FP Performance Suite: Cache read error: ' . $e->getMessage());
            return false;
        }
    }
    
    public function set($key, $content)
    {
        // SICUREZZA: Validiamo la chiave per prevenire path traversal
        if (empty($key) || !is_string($key)) {
            return false;
        }
        
        $file = $this->getCacheFile($key);
        
        // SICUREZZA: Verifichiamo che il file sia nella directory cache
        if (!$this->isValidCacheFile($file)) {
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
            error_log('FP Performance Suite: Cache write error: ' . $e->getMessage());
            return false;
        }
    }
    
    public function delete($key)
    {
        // SECURITY FIX: Validazione chiave e path
        if (empty($key) || !is_string($key)) {
            return false;
        }
        
        $file = $this->getCacheFile($key);
        
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
            if (!is_file($file) || !$this->isValidCacheFile($file)) {
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
    
    private function getCacheFile($key)
    {
        return $this->cache_dir . '/' . md5($key) . '.cache';
    }
    
    /**
     * SICUREZZA: Verifica che il file sia nella directory cache autorizzata
     */
    private function isValidCacheFile($file): bool
    {
        $realCacheDir = realpath($this->cache_dir);
        $realFile = realpath(dirname($file));
        
        if ($realCacheDir === false || $realFile === false) {
            return false;
        }
        
        return strpos($realFile, $realCacheDir) === 0;
    }
    
    /**
     * SICUREZZA: Unserialize sicuro per prevenire object injection
     * 
     * @param string $data Dati serializzati da deserializzare
     * @return mixed|false Dati deserializzati o false in caso di errore
     */
    private function safeUnserialize($data)
    {
        if (empty($data) || !is_string($data)) {
            error_log('FP Performance Suite: Invalid data type for unserialize');
            return false;
        }
        
        // SECURITY FIX: Usa allowed_classes => false per prevenire object injection
        // PHP 7.0+ supporta questo parametro
        try {
            // Usa unserialize con opzioni sicure
            $result = @unserialize($data, ['allowed_classes' => false]);
            
            // Verifica che il risultato sia un array valido
            if (!is_array($result)) {
                error_log('FP Performance Suite: Invalid cache data format - expected array');
                return false;
            }
            
            // Verifica struttura attesa
            if (!isset($result['content']) || !isset($result['expires'])) {
                error_log('FP Performance Suite: Invalid cache data structure');
                return false;
            }
            
            // Valida il tipo di expires
            if (!is_numeric($result['expires'])) {
                error_log('FP Performance Suite: Invalid expires value');
                return false;
            }
            
            return $result;
            
        } catch (\Throwable $e) {
            error_log('FP Performance Suite: Unserialize error: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Verifica se la page cache Ã¨ abilitata
     * 
     * @return bool
     */
    public function isEnabled(): bool
    {
        $settings = get_option('fp_ps_page_cache_settings', []);
        return isset($settings['enabled']) && $settings['enabled'];
    }
    
    /**
     * Restituisce le impostazioni della page cache
     * 
     * @return array Array con le impostazioni
     */
    public function settings(): array
    {
        $settings = get_option('fp_ps_page_cache_settings', []);
        
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
        $currentSettings = get_option('fp_ps_page_cache_settings', []);
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
        
        $result = update_option('fp_ps_page_cache_settings', $newSettings, false);
        
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
        if (empty($url)) {
            return false;
        }
        
        // Genera la chiave cache dall'URL
        $key = $this->urlToKey($url);
        
        return $this->delete($key);
    }
    
    /**
     * Purga la cache per un post specifico
     * 
     * @param int $postId ID del post
     * @return int Numero di URL purgati
     */
    public function purgePost(int $postId): int
    {
        if ($postId <= 0) {
            return 0;
        }
        
        $urls = $this->getPostUrls($postId);
        $purged = 0;
        
        foreach ($urls as $url) {
            if ($this->purgeUrl($url)) {
                $purged++;
            }
        }
        
        return $purged;
    }
    
    /**
     * Purga la cache usando un pattern
     * 
     * @param string $pattern Pattern regex o wildcard
     * @return int Numero di file purgati
     */
    public function purgePattern(string $pattern): int
    {
        if (empty($pattern)) {
            return 0;
        }
        
        if (!file_exists($this->cache_dir) || !is_readable($this->cache_dir)) {
            return 0;
        }
        
        try {
            $files = glob($this->cache_dir . '/*.cache');
            
            if ($files === false) {
                return 0;
            }
            
            $purged = 0;
            
            // Converti wildcard in regex se necessario
            $regex = $this->patternToRegex($pattern);
            
            foreach ($files as $file) {
                if (!$this->isValidCacheFile($file)) {
                    continue;
                }
                
                $basename = basename($file, '.cache');
                
                if (preg_match($regex, $basename)) {
                    if (@unlink($file)) {
                        $purged++;
                    }
                }
            }
            
            return $purged;
        } catch (\Exception $e) {
            error_log('FP Performance Suite: Error in purgePattern: ' . $e->getMessage());
            return 0;
        }
    }
    
    /**
     * Ottiene gli URL associati a un post
     * 
     * @param int $postId ID del post
     * @return array Array di URL
     */
    private function getPostUrls(int $postId): array
    {
        $urls = [];
        
        // URL principale del post
        $permalink = get_permalink($postId);
        if ($permalink) {
            $urls[] = $permalink;
        }
        
        // URL home (potrebbe contenere il post in archivi)
        $urls[] = home_url('/');
        
        // URL delle tassonomie associate
        $post = get_post($postId);
        if ($post) {
            // Categorie
            $categories = get_the_category($postId);
            foreach ($categories as $category) {
                $categoryUrl = get_category_link($category->term_id);
                if ($categoryUrl) {
                    $urls[] = $categoryUrl;
                }
            }
            
            // Tags
            $tags = get_the_tags($postId);
            if ($tags) {
                foreach ($tags as $tag) {
                    $tagUrl = get_tag_link($tag->term_id);
                    if ($tagUrl) {
                        $urls[] = $tagUrl;
                    }
                }
            }
            
            // Custom taxonomies
            $taxonomies = get_object_taxonomies($post);
            foreach ($taxonomies as $taxonomy) {
                if (in_array($taxonomy, ['category', 'post_tag'], true)) {
                    continue; // GiÃ  gestiti sopra
                }
                
                $terms = get_the_terms($postId, $taxonomy);
                if ($terms && !is_wp_error($terms)) {
                    foreach ($terms as $term) {
                        $termUrl = get_term_link($term);
                        if ($termUrl && !is_wp_error($termUrl)) {
                            $urls[] = $termUrl;
                        }
                    }
                }
            }
            
            // Feed
            $urls[] = get_post_comments_feed_link($postId);
        }
        
        // Rimuovi duplicati
        $urls = array_unique(array_filter($urls));
        
        return $urls;
    }
    
    /**
     * Converte un URL in chiave cache
     * 
     * @param string $url URL da convertire
     * @return string Chiave cache
     */
    private function urlToKey(string $url): string
    {
        // Normalizza l'URL
        $url = $this->normalizeUrl($url);
        
        // Usa lo stesso metodo di generazione chiave usato altrove
        return md5($url);
    }
    
    /**
     * Normalizza un URL per coerenza nella cache
     * 
     * @param string $url URL da normalizzare
     * @return string URL normalizzato
     */
    private function normalizeUrl(string $url): string
    {
        // Rimuovi schema per evitare duplicati http/https
        $url = preg_replace('#^https?://#i', '', $url);
        
        // Rimuovi trailing slash
        $url = rtrim($url, '/');
        
        // Converti in lowercase per case-insensitive matching
        $url = strtolower($url);
        
        return $url;
    }
    
    /**
     * Converte un pattern wildcard in regex
     * 
     * @param string $pattern Pattern wildcard o regex
     * @return string Pattern regex
     */
    private function patternToRegex(string $pattern): string
    {
        // Se giÃ  un regex (inizia con / o #), usa direttamente
        if (preg_match('/^[\/\#]/', $pattern)) {
            return $pattern;
        }
        
        // Converti wildcard in regex
        $pattern = preg_quote($pattern, '/');
        $pattern = str_replace('\*', '.*', $pattern);
        $pattern = str_replace('\?', '.', $pattern);
        
        return '/^' . $pattern . '$/i';
    }
    
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
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
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
     * 
     * @param int $postId ID del post
     */
    public function autoPurgePost(int $postId): void
    {
        // Verifica che auto-purge sia abilitato
        $settings = $this->settings();
        if (empty($settings['auto_purge'])) {
            return;
        }
        
        // Verifica che non sia una revisione o auto-draft
        if (wp_is_post_revision($postId) || wp_is_post_autosave($postId)) {
            return;
        }
        
        $this->purgePost($postId);
    }
    
    /**
     * Auto-purge cache quando un commento viene aggiunto
     * 
     * @param int $commentId ID del commento
     * @param int|string $approved Stato approvazione
     */
    public function autoPurgePostOnComment(int $commentId, $approved): void
    {
        if ($approved !== 1 && $approved !== 'approve') {
            return; // Solo commenti approvati
        }
        
        $comment = get_comment($commentId);
        if ($comment && $comment->comment_post_ID) {
            $this->autoPurgePost((int) $comment->comment_post_ID);
        }
    }
    
    /**
     * Auto-purge cache quando un commento viene modificato
     * 
     * @param int $commentId ID del commento
     */
    public function autoPurgeCommentPost(int $commentId): void
    {
        $comment = get_comment($commentId);
        if ($comment && $comment->comment_post_ID) {
            $this->autoPurgePost((int) $comment->comment_post_ID);
        }
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
                if (!$this->isValidCacheFile($file)) {
                    continue;
                }
                
                // Leggi e controlla scadenza
                $data = @file_get_contents($file);
                if ($data === false) {
                    continue;
                }
                
                $cache_data = $this->safeUnserialize($data);
                
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
            error_log('FP Performance Suite: Error in cleanupExpiredCache: ' . $e->getMessage());
        }
    }
}