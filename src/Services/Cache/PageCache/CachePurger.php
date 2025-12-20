<?php

namespace FP\PerfSuite\Services\Cache\PageCache;

use FP\PerfSuite\Utils\ErrorHandler;
use function get_permalink;
use function home_url;
use function get_post;
use function get_the_category;
use function get_category_link;
use function get_the_tags;
use function get_tag_link;
use function get_object_taxonomies;
use function get_the_terms;
use function get_term_link;
use function get_post_comments_feed_link;
use function is_wp_error;
use function array_unique;
use function array_filter;
use function glob;
use function basename;
use function preg_match;
use function unlink;
use function file_exists;
use function is_readable;
use function error_log;

/**
 * Gestisce le operazioni di purge della cache
 * 
 * @package FP\PerfSuite\Services\Cache\PageCache
 * @author Francesco Passeri
 */
class CachePurger
{
    private string $cacheDir;
    private CacheFileManager $fileManager;
    private UrlNormalizer $urlNormalizer;
    /** @var callable */
    private $deleteCallback;

    public function __construct(
        string $cacheDir,
        CacheFileManager $fileManager,
        UrlNormalizer $urlNormalizer,
        callable $deleteCallback
    ) {
        $this->cacheDir = $cacheDir;
        $this->fileManager = $fileManager;
        $this->urlNormalizer = $urlNormalizer;
        $this->deleteCallback = $deleteCallback;
    }

    /**
     * Purga la cache per un URL specifico
     */
    public function purgeUrl(string $url): bool
    {
        if (empty($url)) {
            return false;
        }
        
        // Genera la chiave cache dall'URL
        $key = $this->urlNormalizer->urlToKey($url);
        
        return call_user_func($this->deleteCallback, $key);
    }

    /**
     * Purga la cache per un post specifico
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
     */
    public function purgePattern(string $pattern): int
    {
        if (empty($pattern)) {
            return 0;
        }
        
        if (!file_exists($this->cacheDir) || !is_readable($this->cacheDir)) {
            return 0;
        }
        
        try {
            $files = glob($this->cacheDir . '/*.cache');
            
            if ($files === false) {
                return 0;
            }
            
            $purged = 0;
            
            // Converti wildcard in regex se necessario
            $regex = $this->urlNormalizer->patternToRegex($pattern);
            
            foreach ($files as $file) {
                if (!$this->fileManager->isValidCacheFile($file)) {
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
        } catch (\Throwable $e) {
            ErrorHandler::handleSilently($e, 'CachePurger purgePattern');
            return 0;
        }
    }

    /**
     * Ottiene gli URL associati a un post
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
                    continue; // Già gestiti sopra
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
}
















