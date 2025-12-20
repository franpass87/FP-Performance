<?php

namespace FP\PerfSuite\Services\Cache\PageCache;

use function wp_is_post_revision;
use function wp_is_post_autosave;
use function get_comment;

/**
 * Gestisce l'auto-purge della cache basata su eventi WordPress
 * 
 * @package FP\PerfSuite\Services\Cache\PageCache
 * @author Francesco Passeri
 */
class AutoPurgeHandler
{
    private CachePurger $purger;
    /** @var callable */
    private $settingsCallback;
    /** @var callable */
    private $purgePostCallback;

    public function __construct(
        CachePurger $purger,
        callable $settingsCallback,
        callable $purgePostCallback
    ) {
        $this->purger = $purger;
        $this->settingsCallback = $settingsCallback;
        $this->purgePostCallback = $purgePostCallback;
    }

    /**
     * Auto-purge cache quando un post viene modificato
     */
    public function autoPurgePost(int $postId): void
    {
        // Verifica che auto-purge sia abilitato
        $settings = call_user_func($this->settingsCallback);
        if (empty($settings['auto_purge'])) {
            return;
        }
        
        // Verifica che non sia una revisione o auto-draft
        if (wp_is_post_revision($postId) || wp_is_post_autosave($postId)) {
            return;
        }
        
        call_user_func($this->purgePostCallback, $postId);
    }

    /**
     * Auto-purge cache quando un commento viene aggiunto
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
     */
    public function autoPurgeCommentPost(int $commentId): void
    {
        $comment = get_comment($commentId);
        if ($comment && $comment->comment_post_ID) {
            $this->autoPurgePost((int) $comment->comment_post_ID);
        }
    }
}
















