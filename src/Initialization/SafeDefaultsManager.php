<?php

namespace FP\PerfSuite\Initialization;

use FP\PerfSuite\Utils\OptionHelper;

/**
 * Gestisce l'applicazione di default sicuri per le opzioni
 * 
 * @package FP\PerfSuite\Initialization
 * @author Francesco Passeri
 */
class SafeDefaultsManager
{
    /**
     * Applica default sicuri se non già applicati
     */
    public function maybeApply(): void
    {
        if (!apply_filters('fp_ps_apply_safe_defaults', true)) {
            return;
        }

        if (get_option('fp_ps_safe_defaults_applied')) {
            return;
        }

        $this->applyDatabaseDefaults();
        $this->applyAssetsDefaults();
        $this->applyLazyLoadDefaults();
        $this->applyResponsiveDefaults();

        update_option('fp_ps_safe_defaults_applied', time(), false);
    }

    /**
     * Applica default per database
     */
    private function applyDatabaseDefaults(): void
    {
        $dbSettings = OptionHelper::get('fp_ps_db', []);
        $didUpdate = false;

        if (empty($dbSettings['schedule']) || $dbSettings['schedule'] === 'manual') {
            $dbSettings['schedule'] = 'weekly';
            $didUpdate = true;
        }

        if (empty($dbSettings['cleanup_scope']) || !is_array($dbSettings['cleanup_scope'])) {
            $dbSettings['cleanup_scope'] = [
                'revisions',
                'auto_drafts',
                'trash_posts',
                'spam_comments',
                'expired_transients',
            ];
            $didUpdate = true;
        }

        if (!isset($dbSettings['batch']) || (int) $dbSettings['batch'] < 50) {
            $dbSettings['batch'] = 200;
            $didUpdate = true;
        }

        if ($didUpdate) {
            update_option('fp_ps_db', $dbSettings, false);
        }

        // Cleaner settings
        $cleanerSettings = OptionHelper::get('fp_ps_db_cleaner_settings', []);
        $cleanerDirty = false;

        if (empty($cleanerSettings['schedule']) || $cleanerSettings['schedule'] === 'manual') {
            $cleanerSettings['schedule'] = 'weekly';
            $cleanerDirty = true;
        }

        if (empty($cleanerSettings['batch']) || (int) $cleanerSettings['batch'] < 50) {
            $cleanerSettings['batch'] = 200;
            $cleanerDirty = true;
        }

        if ($cleanerDirty) {
            update_option('fp_ps_db_cleaner_settings', $cleanerSettings, false);
        }
    }

    /**
     * Applica default per assets
     */
    private function applyAssetsDefaults(): void
    {
        $assets = OptionHelper::get('fp_ps_assets', []);
        $assetsDirty = false;

        if (empty($assets['enabled'])) {
            $assets['enabled'] = true;
            $assetsDirty = true;
        }

        if (empty($assets['minify_html'])) {
            $assets['minify_html'] = true;
            $assetsDirty = true;
        }

        if (empty($assets['async_css'])) {
            $assets['async_css'] = true;
            $assetsDirty = true;
        }

        if (!isset($assets['remove_comments'])) {
            $assets['remove_comments'] = true;
            $assetsDirty = true;
        }

        if ($assetsDirty) {
            update_option('fp_ps_assets', $assets, false);
        }
    }

    /**
     * Applica default per lazy load
     */
    private function applyLazyLoadDefaults(): void
    {
        $lazy = OptionHelper::get('fp_ps_lazy_load', []);
        $lazyDirty = false;

        if (empty($lazy['enabled'])) {
            $lazy['enabled'] = true;
            $lazyDirty = true;
        }

        if (!isset($lazy['lazy_load_images'])) {
            $lazy['lazy_load_images'] = true;
            $lazyDirty = true;
        }

        if (!isset($lazy['lazy_load_videos'])) {
            $lazy['lazy_load_videos'] = true;
            $lazyDirty = true;
        }

        if (!isset($lazy['lazy_load_iframes'])) {
            $lazy['lazy_load_iframes'] = true;
            $lazyDirty = true;
        }

        if ($lazyDirty) {
            update_option('fp_ps_lazy_load', $lazy, false);
        }
    }

    /**
     * Applica default per responsive images
     */
    private function applyResponsiveDefaults(): void
    {
        $responsive = OptionHelper::get('fp_ps_responsive_images', []);
        if (!is_array($responsive)) {
            $responsive = [];
        }
        if (empty($responsive['enable_lazy_loading'])) {
            $responsive['enable_lazy_loading'] = true;
            update_option('fp_ps_responsive_images', $responsive, false);
        }
    }
}
















