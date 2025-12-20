<?php

namespace FP\PerfSuite\Tests\Fixtures;

/**
 * Database fixtures for testing
 *
 * @package FP\PerfSuite\Tests\Fixtures
 */
class DatabaseFixtures
{
    /**
     * Create test posts for cleanup testing
     *
     * @param int $count Number of posts to create
     * @return array<int> Post IDs
     */
    public static function createTestPosts(int $count = 10): array
    {
        $postIds = [];
        
        for ($i = 0; $i < $count; $i++) {
            $postId = wp_insert_post([
                'post_title' => 'Test Post ' . $i,
                'post_content' => 'Test content',
                'post_status' => 'publish',
                'post_type' => 'post',
            ]);
            
            if ($postId) {
                $postIds[] = $postId;
            }
        }
        
        return $postIds;
    }

    /**
     * Create test revisions
     *
     * @param int $postId Post ID
     * @param int $count Number of revisions
     * @return array<int> Revision IDs
     */
    public static function createTestRevisions(int $postId, int $count = 5): array
    {
        $revisionIds = [];
        
        for ($i = 0; $i < $count; $i++) {
            $revisionId = wp_save_post_revision($postId);
            if ($revisionId) {
                $revisionIds[] = $revisionId;
            }
        }
        
        return $revisionIds;
    }

    /**
     * Create test transients
     *
     * @param int $count Number of transients
     * @return array<string> Transient names
     */
    public static function createTestTransients(int $count = 10): array
    {
        $transientNames = [];
        
        for ($i = 0; $i < $count; $i++) {
            $name = 'test_transient_' . $i;
            set_transient($name, 'test_value_' . $i, HOUR_IN_SECONDS);
            $transientNames[] = $name;
        }
        
        return $transientNames;
    }

    /**
     * Create expired transients
     *
     * @param int $count Number of expired transients
     * @return array<string> Transient names
     */
    public static function createExpiredTransients(int $count = 5): array
    {
        global $wpdb;
        $transientNames = [];
        
        for ($i = 0; $i < $count; $i++) {
            $name = 'test_expired_transient_' . $i;
            $wpdb->insert(
                $wpdb->options,
                [
                    'option_name' => '_transient_' . $name,
                    'option_value' => 'test_value',
                    'autoload' => 'no',
                ]
            );
            $wpdb->insert(
                $wpdb->options,
                [
                    'option_name' => '_transient_timeout_' . $name,
                    'option_value' => time() - 3600, // Expired 1 hour ago
                    'autoload' => 'no',
                ]
            );
            $transientNames[] = $name;
        }
        
        return $transientNames;
    }

    /**
     * Clean up test data
     *
     * @param array<int> $postIds Post IDs to delete
     * @return void
     */
    public static function cleanupTestPosts(array $postIds): void
    {
        foreach ($postIds as $postId) {
            wp_delete_post($postId, true);
        }
    }

    /**
     * Clean up test transients
     *
     * @param array<string> $transientNames Transient names to delete
     * @return void
     */
    public static function cleanupTestTransients(array $transientNames): void
    {
        foreach ($transientNames as $name) {
            delete_transient($name);
        }
    }
}










