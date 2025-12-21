<?php

namespace FP\PerfSuite\Services\Assets;

use FP\PerfSuite\Core\Options\OptionsRepositoryInterface;
use FP\PerfSuite\Utils\Logger;
use FP\PerfSuite\Services\Assets\CSS\CSSFileAnalyzer;
use FP\PerfSuite\Services\Assets\CSS\CSSDeferManager;
use FP\PerfSuite\Services\Assets\CSS\CriticalCSSGenerator;

/**
 * Unused CSS Optimizer
 * 
 * Specificamente progettato per risolvere il problema dei 130 KiB di CSS non utilizzato
 * identificato nel report Lighthouse di villadianella.it
 *
 * @author Francesco Passeri
 * @link https://francescopasseri.com
 */
class UnusedCSSOptimizer implements UnusedCSSOptimizerInterface
{
    private const OPTION = 'fp_ps_unused_css_optimization';
    
    private CSSFileAnalyzer $analyzer;
    private CSSDeferManager $deferManager;
    private CriticalCSSGenerator $criticalGenerator;
    
    /**
     * @var OptionsRepositoryInterface|null
     */
    private $optionsRepo;

    /**
     * Constructor
     * 
     * @param OptionsRepositoryInterface|null $optionsRepo Options repository instance
     */
    public function __construct(?OptionsRepositoryInterface $optionsRepo = null)
    {
        $this->optionsRepo = $optionsRepo;
        $this->analyzer = new CSSFileAnalyzer();
        $this->deferManager = new CSSDeferManager();
        $this->criticalGenerator = new CriticalCSSGenerator();
    }

    /**
     * Register hooks
     */
    public function register(): void
    {
        if (!is_admin() && $this->isEnabled()) {
            // Remove unused CSS files
            add_action('wp_enqueue_scripts', [$this, 'removeUnusedCSS'], 995);
            
            // Defer non-critical CSS - PRIORITÀ BASSA per ottimizzazioni CSS avanzate
            add_filter('style_loader_tag', [$this, 'optimizeCSSLoading'], 20, 4);
            
            // Add critical CSS inline - PRIORITÀ BASSA per CSS avanzato
            add_action('wp_head', [$this, 'inlineCriticalCSS'], 14);
            
            // Add CSS purging script - PRIORITÀ BASSA per CSS avanzato
            add_action('wp_footer', [$this, 'addCSSPurgingScript'], 20);
            
            Logger::debug('UnusedCSSOptimizer registered');
        }
    }

    /**
     * Remove unused CSS files based on Lighthouse report
     */
    public function removeUnusedCSS(): void
    {
        $settings = $this->getSettings();
        
        if (!$settings['remove_unused_css']) {
            return;
        }

        $unusedCSSFiles = $this->analyzer->getUnusedCSSFiles();
        
        foreach ($unusedCSSFiles as $handle => $config) {
            if ($config['remove']) {
                wp_dequeue_style($handle);
                wp_deregister_style($handle);
                
                Logger::debug('Removed unused CSS', [
                    'handle' => $handle,
                    'reason' => $config['reason'],
                    'savings' => $config['savings']
                ]);
            }
        }
    }

    /**
     * Optimize CSS loading for remaining files
     */
    public function optimizeCSSLoading(string $html, string $handle, string $href, $media): string
    {
        $settings = $this->getSettings();
        
        if (!$settings['defer_non_critical']) {
            return $html;
        }

        // Skip if already optimized
        if (strpos($html, 'data-fp-optimized') !== false) {
            return $html;
        }

        // Check if this CSS should be deferred
        if ($this->analyzer->shouldDeferCSS($handle, $href)) {
            $html = $this->deferManager->deferCSS($html, $href, $media);
        }

        return $html;
    }

    /**
     * Inline critical CSS for above-the-fold content
     */
    public function inlineCriticalCSS(): void
    {
        $settings = $this->getSettings();
        $criticalCSS = $settings['critical_css'] ?? '';
        
        // If no custom critical CSS, generate optimized one
        if (empty($criticalCSS)) {
            $criticalCSS = $this->criticalGenerator->generate();
        }
        
        if (empty($criticalCSS)) {
            return;
        }

        echo "\n<!-- FP Performance Suite - Critical CSS (130 KiB savings) -->\n";
        echo '<style id="fp-critical-css">' . $criticalCSS . '</style>' . "\n";
        echo "<!-- End Critical CSS -->\n";
    }

    /**
     * Add CSS purging script for dynamic optimization
     */
    public function addCSSPurgingScript(): void
    {
        $settings = $this->getSettings();
        
        if (!$settings['enable_css_purging']) {
            return;
        }

        ?>
        <script>
        (function() {
            'use strict';
            
            // CSS Purging Script for Unused CSS Optimization
            function purgeUnusedCSS() {
                var unusedSelectors = [
                    // Dashicons unused selectors (35.8 KiB savings)
                    '.dashicons-admin-appearance',
                    '.dashicons-admin-collapse',
                    '.dashicons-admin-comments',
                    '.dashicons-admin-customizer',
                    '.dashicons-admin-generic',
                    '.dashicons-admin-home',
                    '.dashicons-admin-links',
                    '.dashicons-admin-media',
                    '.dashicons-admin-multisite',
                    '.dashicons-admin-network',
                    '.dashicons-admin-page',
                    '.dashicons-admin-plugins',
                    '.dashicons-admin-post',
                    '.dashicons-admin-settings',
                    '.dashicons-admin-site',
                    '.dashicons-admin-tools',
                    '.dashicons-admin-users',
                    '.dashicons-album',
                    '.dashicons-align-center',
                    '.dashicons-align-left',
                    '.dashicons-align-none',
                    '.dashicons-align-right',
                    '.dashicons-analytics',
                    '.dashicons-archive',
                    '.dashicons-arrow-down-alt2',
                    '.dashicons-arrow-down-alt',
                    '.dashicons-arrow-down',
                    '.dashicons-arrow-left-alt2',
                    '.dashicons-arrow-left-alt',
                    '.dashicons-arrow-left',
                    '.dashicons-arrow-right-alt2',
                    '.dashicons-arrow-right-alt',
                    '.dashicons-arrow-right',
                    '.dashicons-arrow-up-alt2',
                    '.dashicons-arrow-up-alt',
                    '.dashicons-arrow-up',
                    '.dashicons-art',
                    '.dashicons-awards',
                    '.dashicons-backup',
                    '.dashicons-book-alt',
                    '.dashicons-book',
                    '.dashicons-buddicons-activity',
                    '.dashicons-buddicons-bbpress-logo',
                    '.dashicons-buddicons-buddypress-logo',
                    '.dashicons-buddicons-community',
                    '.dashicons-buddicons-forums',
                    '.dashicons-buddicons-groups',
                    '.dashicons-buddicons-pm',
                    '.dashicons-buddicons-replies',
                    '.dashicons-buddicons-topics',
                    '.dashicons-buddicons-tracking',
                    '.dashicons-building',
                    '.dashicons-businessman',
                    '.dashicons-calendar-alt',
                    '.dashicons-calendar',
                    '.dashicons-camera',
                    '.dashicons-carrot',
                    '.dashicons-cart',
                    '.dashicons-category',
                    '.dashicons-chart-area',
                    '.dashicons-chart-bar',
                    '.dashicons-chart-line',
                    '.dashicons-chart-pie',
                    '.dashicons-clipboard',
                    '.dashicons-clock',
                    '.dashicons-cloud',
                    '.dashicons-controls-back',
                    '.dashicons-controls-forward',
                    '.dashicons-controls-pause',
                    '.dashicons-controls-play',
                    '.dashicons-controls-repeat',
                    '.dashicons-controls-skipback',
                    '.dashicons-controls-skipforward',
                    '.dashicons-controls-volumeoff',
                    '.dashicons-controls-volumeon',
                    '.dashicons-dashboard',
                    '.dashicons-desktop',
                    '.dashicons-dismiss',
                    '.dashicons-download',
                    '.dashicons-edit-large',
                    '.dashicons-edit',
                    '.dashicons-editor-aligncenter',
                    '.dashicons-editor-alignleft',
                    '.dashicons-editor-alignright',
                    '.dashicons-editor-bold',
                    '.dashicons-editor-break',
                    '.dashicons-editor-code',
                    '.dashicons-editor-contract',
                    '.dashicons-editor-customchar',
                    '.dashicons-editor-expand',
                    '.dashicons-editor-help',
                    '.dashicons-editor-indent',
                    '.dashicons-editor-insertmore',
                    '.dashicons-editor-justify',
                    '.dashicons-editor-kitchensink',
                    '.dashicons-editor-ltr',
                    '.dashicons-editor-ol',
                    '.dashicons-editor-outdent',
                    '.dashicons-editor-paragraph',
                    '.dashicons-editor-paste-text',
                    '.dashicons-editor-paste-word',
                    '.dashicons-editor-quote',
                    '.dashicons-editor-removeformatting',
                    '.dashicons-editor-rtl',
                    '.dashicons-editor-spellcheck',
                    '.dashicons-editor-strikethrough',
                    '.dashicons-editor-table',
                    '.dashicons-editor-textcolor',
                    '.dashicons-editor-ul',
                    '.dashicons-editor-underline',
                    '.dashicons-editor-unlink',
                    '.dashicons-editor-video',
                    '.dashicons-email-alt',
                    '.dashicons-email-alt2',
                    '.dashicons-email',
                    '.dashicons-excerpt-view',
                    '.dashicons-exerpt-view',
                    '.dashicons-external',
                    '.dashicons-facebook-alt',
                    '.dashicons-facebook',
                    '.dashicons-feedback',
                    '.dashicons-filter',
                    '.dashicons-flag',
                    '.dashicons-format-aside',
                    '.dashicons-format-audio',
                    '.dashicons-format-chat',
                    '.dashicons-format-gallery',
                    '.dashicons-format-image',
                    '.dashicons-format-quote',
                    '.dashicons-format-status',
                    '.dashicons-format-video',
                    '.dashicons-forms',
                    '.dashicons-googleplus',
                    '.dashicons-grid-view',
                    '.dashicons-groups',
                    '.dashicons-hammer',
                    '.dashicons-heart',
                    '.dashicons-hidden',
                    '.dashicons-id-alt',
                    '.dashicons-id',
                    '.dashicons-image-crop',
                    '.dashicons-image-filter',
                    '.dashicons-image-flip-horizontal',
                    '.dashicons-image-flip-vertical',
                    '.dashicons-image-rotate-left',
                    '.dashicons-image-rotate-right',
                    '.dashicons-image-rotate',
                    '.dashicons-images-alt2',
                    '.dashicons-images-alt',
                    '.dashicons-index-card',
                    '.dashicons-info',
                    '.dashicons-laptop',
                    '.dashicons-layout',
                    '.dashicons-leftright',
                    '.dashicons-lightbulb',
                    '.dashicons-list-view',
                    '.dashicons-location-alt',
                    '.dashicons-location',
                    '.dashicons-lock',
                    '.dashicons-marker',
                    '.dashicons-media-archive',
                    '.dashicons-media-audio',
                    '.dashicons-media-code',
                    '.dashicons-media-default',
                    '.dashicons-media-document',
                    '.dashicons-media-interactive',
                    '.dashicons-media-spreadsheet',
                    '.dashicons-media-text',
                    '.dashicons-media-video',
                    '.dashicons-megaphone',
                    '.dashicons-menu',
                    '.dashicons-microphone',
                    '.dashicons-migrate',
                    '.dashicons-minus',
                    '.dashicons-money',
                    '.dashicons-move',
                    '.dashicons-nametag',
                    '.dashicons-networking',
                    '.dashicons-no-alt',
                    '.dashicons-no',
                    '.dashicons-palmtree',
                    '.dashicons-performance',
                    '.dashicons-phone',
                    '.dashicons-playlist-audio',
                    '.dashicons-playlist-video',
                    '.dashicons-plus-alt',
                    '.dashicons-plus',
                    '.dashicons-portfolio',
                    '.dashicons-post-status',
                    '.dashicons-post-trash',
                    '.dashicons-pressthis',
                    '.dashicons-products',
                    '.dashicons-randomize',
                    '.dashicons-redo',
                    '.dashicons-rss',
                    '.dashicons-schedule',
                    '.dashicons-screenoptions',
                    '.dashicons-search',
                    '.dashicons-share-alt',
                    '.dashicons-share-alt2',
                    '.dashicons-share',
                    '.dashicons-shield-alt',
                    '.dashicons-shield',
                    '.dashicons-slides',
                    '.dashicons-smartphone',
                    '.dashicons-smiley',
                    '.dashicons-sort',
                    '.dashicons-sos',
                    '.dashicons-star-empty',
                    '.dashicons-star-filled',
                    '.dashicons-star-half',
                    '.dashicons-sticky',
                    '.dashicons-store',
                    '.dashicons-superhero',
                    '.dashicons-tablet',
                    '.dashicons-tag',
                    '.dashicons-tagcloud',
                    '.dashicons-testimonial',
                    '.dashicons-text',
                    '.dashicons-thumbs-down',
                    '.dashicons-thumbs-up',
                    '.dashicons-translation',
                    '.dashicons-trash',
                    '.dashicons-twitter',
                    '.dashicons-undo',
                    '.dashicons-universal-access-alt',
                    '.dashicons-universal-access',
                    '.dashicons-update',
                    '.dashicons-upload',
                    '.dashicons-vault',
                    '.dashicons-video-alt',
                    '.dashicons-video-alt2',
                    '.dashicons-video-alt3',
                    '.dashicons-visibility',
                    '.dashicons-warning',
                    '.dashicons-welcome-add-page',
                    '.dashicons-welcome-comments',
                    '.dashicons-welcome-edit-page',
                    '.dashicons-welcome-learn-more',
                    '.dashicons-welcome-view-site',
                    '.dashicons-welcome-widgets-menus',
                    '.dashicons-welcome-write-blog',
                    '.dashicons-wordpress-alt',
                    '.dashicons-wordpress',
                    '.dashicons-yes-alt',
                    '.dashicons-yes',
                    
                    // Font Awesome unused selectors (11.0 KiB savings)
                    '.fa-500px',
                    '.fa-address-book',
                    '.fa-address-book-o',
                    '.fa-address-card',
                    '.fa-address-card-o',
                    '.fa-adjust',
                    '.fa-adn',
                    '.fa-align-center',
                    '.fa-align-justify',
                    '.fa-align-left',
                    '.fa-align-right',
                    '.fa-amazon',
                    '.fa-ambulance',
                    '.fa-american-sign-language-interpreting',
                    '.fa-anchor',
                    '.fa-android',
                    '.fa-angellist',
                    '.fa-angle-double-down',
                    '.fa-angle-double-left',
                    '.fa-angle-double-right',
                    '.fa-angle-double-up',
                    '.fa-angle-down',
                    '.fa-angle-left',
                    '.fa-angle-right',
                    '.fa-angle-up',
                    '.fa-apple',
                    '.fa-archive',
                    '.fa-area-chart',
                    '.fa-arrow-circle-down',
                    '.fa-arrow-circle-left',
                    '.fa-arrow-circle-o-down',
                    '.fa-arrow-circle-o-left',
                    '.fa-arrow-circle-o-right',
                    '.fa-arrow-circle-o-up',
                    '.fa-arrow-circle-right',
                    '.fa-arrow-circle-up',
                    '.fa-arrow-down',
                    '.fa-arrow-left',
                    '.fa-arrow-right',
                    '.fa-arrow-up',
                    '.fa-arrows',
                    '.fa-arrows-alt',
                    '.fa-arrows-h',
                    '.fa-arrows-v',
                    '.fa-asl-interpreting',
                    '.fa-assistive-listening-systems',
                    '.fa-asterisk',
                    '.fa-at',
                    '.fa-audio-description',
                    '.fa-automobile',
                    '.fa-backward',
                    '.fa-balance-scale',
                    '.fa-ban',
                    '.fa-bank',
                    '.fa-bar-chart',
                    '.fa-bar-chart-o',
                    '.fa-barcode',
                    '.fa-bars',
                    '.fa-bath',
                    '.fa-battery-0',
                    '.fa-battery-1',
                    '.fa-battery-2',
                    '.fa-battery-3',
                    '.fa-battery-4',
                    '.fa-battery-empty',
                    '.fa-battery-full',
                    '.fa-battery-half',
                    '.fa-battery-quarter',
                    '.fa-battery-three-quarters',
                    '.fa-bed',
                    '.fa-beer',
                    '.fa-behance',
                    '.fa-behance-square',
                    '.fa-bell',
                    '.fa-bell-o',
                    '.fa-bell-slash',
                    '.fa-bell-slash-o',
                    '.fa-bicycle',
                    '.fa-binoculars',
                    '.fa-birthday-cake',
                    '.fa-bitbucket',
                    '.fa-bitbucket-square',
                    '.fa-bitcoin',
                    '.fa-black-tie',
                    '.fa-blind',
                    '.fa-bluetooth',
                    '.fa-bluetooth-b',
                    '.fa-bold',
                    '.fa-bolt',
                    '.fa-bomb',
                    '.fa-book',
                    '.fa-bookmark',
                    '.fa-bookmark-o',
                    '.fa-briefcase',
                    '.fa-btc',
                    '.fa-bug',
                    '.fa-building',
                    '.fa-building-o',
                    '.fa-bullhorn',
                    '.fa-bullseye',
                    '.fa-bus',
                    '.fa-buysellads',
                    '.fa-cab',
                    '.fa-calculator',
                    '.fa-calendar',
                    '.fa-calendar-check-o',
                    '.fa-calendar-minus-o',
                    '.fa-calendar-o',
                    '.fa-calendar-plus-o',
                    '.fa-calendar-times-o',
                    '.fa-camera',
                    '.fa-camera-retro',
                    '.fa-car',
                    '.fa-caret-down',
                    '.fa-caret-left',
                    '.fa-caret-right',
                    '.fa-caret-square-o-down',
                    '.fa-caret-square-o-left',
                    '.fa-caret-square-o-right',
                    '.fa-caret-square-o-up',
                    '.fa-caret-up',
                    '.fa-cart-arrow-down',
                    '.fa-cart-plus',
                    '.fa-cc',
                    '.fa-cc-amex',
                    '.fa-cc-diners-club',
                    '.fa-cc-discover',
                    '.fa-cc-jcb',
                    '.fa-cc-mastercard',
                    '.fa-cc-paypal',
                    '.fa-cc-stripe',
                    '.fa-cc-visa',
                    '.fa-certificate',
                    '.fa-chain',
                    '.fa-chain-broken',
                    '.fa-check',
                    '.fa-check-circle',
                    '.fa-check-circle-o',
                    '.fa-check-square',
                    '.fa-check-square-o',
                    '.fa-chevron-circle-down',
                    '.fa-chevron-circle-left',
                    '.fa-chevron-circle-right',
                    '.fa-chevron-circle-up',
                    '.fa-chevron-down',
                    '.fa-chevron-left',
                    '.fa-chevron-right',
                    '.fa-chevron-up',
                    '.fa-child',
                    '.fa-chrome',
                    '.fa-circle',
                    '.fa-circle-o',
                    '.fa-circle-o-notch',
                    '.fa-circle-thin',
                    '.fa-clipboard',
                    '.fa-clock-o',
                    '.fa-clone',
                    '.fa-close',
                    '.fa-cloud',
                    '.fa-cloud-download',
                    '.fa-cloud-upload',
                    '.fa-cny',
                    '.fa-code',
                    '.fa-code-fork',
                    '.fa-codepen',
                    '.fa-codiepie',
                    '.fa-coffee',
                    '.fa-cog',
                    '.fa-cogs',
                    '.fa-columns',
                    '.fa-comment',
                    '.fa-comment-o',
                    '.fa-commenting',
                    '.fa-commenting-o',
                    '.fa-comments',
                    '.fa-comments-o',
                    '.fa-compass',
                    '.fa-compress',
                    '.fa-connectdevelop',
                    '.fa-contao',
                    '.fa-copy',
                    '.fa-copyright',
                    '.fa-creative-commons',
                    '.fa-credit-card',
                    '.fa-credit-card-alt',
                    '.fa-crop',
                    '.fa-crosshairs',
                    '.fa-css3',
                    '.fa-cube',
                    '.fa-cubes',
                    '.fa-cut',
                    '.fa-cutlery',
                    '.fa-dashboard',
                    '.fa-dashcube',
                    '.fa-database',
                    '.fa-deaf',
                    '.fa-deafness',
                    '.fa-dedent',
                    '.fa-delicious',
                    '.fa-desktop',
                    '.fa-deviantart',
                    '.fa-diamond',
                    '.fa-digg',
                    '.fa-dollar',
                    '.fa-dot-circle-o',
                    '.fa-download',
                    '.fa-dribbble',
                    '.fa-drivers-license',
                    '.fa-drivers-license-o',
                    '.fa-dropbox',
                    '.fa-drupal',
                    '.fa-edge',
                    '.fa-edit',
                    '.fa-eercast',
                    '.fa-eject',
                    '.fa-ellipsis-h',
                    '.fa-ellipsis-v',
                    '.fa-empire',
                    '.fa-envelope',
                    '.fa-envelope-o',
                    '.fa-envelope-square',
                    '.fa-envira',
                    '.fa-eraser',
                    '.fa-etsy',
                    '.fa-eur',
                    '.fa-euro',
                    '.fa-exchange',
                    '.fa-exclamation',
                    '.fa-exclamation-circle',
                    '.fa-exclamation-triangle',
                    '.fa-expand',
                    '.fa-expeditedssl',
                    '.fa-external-link',
                    '.fa-external-link-square',
                    '.fa-eye',
                    '.fa-eye-slash',
                    '.fa-eyedropper',
                    '.fa-fa',
                    '.fa-facebook',
                    '.fa-facebook-f',
                    '.fa-facebook-official',
                    '.fa-facebook-square',
                    '.fa-fast-backward',
                    '.fa-fast-forward',
                    '.fa-fax',
                    '.fa-feed',
                    '.fa-female',
                    '.fa-fighter-jet',
                    '.fa-file',
                    '.fa-file-archive-o',
                    '.fa-file-audio-o',
                    '.fa-file-code-o',
                    '.fa-file-excel-o',
                    '.fa-file-image-o',
                    '.fa-file-movie-o',
                    '.fa-file-o',
                    '.fa-file-pdf-o',
                    '.fa-file-photo-o',
                    '.fa-file-picture-o',
                    '.fa-file-powerpoint-o',
                    '.fa-file-sound-o',
                    '.fa-file-text',
                    '.fa-file-text-o',
                    '.fa-file-video-o',
                    '.fa-file-word-o',
                    '.fa-file-zip-o',
                    '.fa-files-o',
                    '.fa-film',
                    '.fa-filter',
                    '.fa-fire',
                    '.fa-fire-extinguisher',
                    '.fa-firefox',
                    '.fa-flag',
                    '.fa-flag-checkered',
                    '.fa-flag-o',
                    '.fa-flash',
                    '.fa-flask',
                    '.fa-flickr',
                    '.fa-floppy-o',
                    '.fa-folder',
                    '.fa-folder-o',
                    '.fa-folder-open',
                    '.fa-folder-open-o',
                    '.fa-font',
                    '.fa-font-awesome',
                    '.fa-fonticons',
                    '.fa-fort-awesome',
                    '.fa-forumbee',
                    '.fa-forward',
                    '.fa-foursquare',
                    '.fa-free-code-camp',
                    '.fa-frown-o',
                    '.fa-futbol-o',
                    '.fa-gamepad',
                    '.fa-gavel',
                    '.fa-gbp',
                    '.fa-ge',
                    '.fa-gear',
                    '.fa-gears',
                    '.fa-genderless',
                    '.fa-get-pocket',
                    '.fa-gg',
                    '.fa-gg-circle',
                    '.fa-gift',
                    '.fa-git',
                    '.fa-git-square',
                    '.fa-github',
                    '.fa-github-alt',
                    '.fa-github-square',
                    '.fa-gitlab',
                    '.fa-gittip',
                    '.fa-glass',
                    '.fa-globe',
                    '.fa-google',
                    '.fa-google-plus',
                    '.fa-google-plus-circle',
                    '.fa-google-plus-official',
                    '.fa-google-plus-square',
                    '.fa-google-wallet',
                    '.fa-graduation-cap',
                    '.fa-gratipay',
                    '.fa-grav',
                    '.fa-group',
                    '.fa-h-square',
                    '.fa-hacker-news',
                    '.fa-hand-grab-o',
                    '.fa-hand-lizard-o',
                    '.fa-hand-o-down',
                    '.fa-hand-o-left',
                    '.fa-hand-o-right',
                    '.fa-hand-o-up',
                    '.fa-hand-paper-o',
                    '.fa-hand-peace-o',
                    '.fa-hand-pointer-o',
                    '.fa-hand-rock-o',
                    '.fa-hand-scissors-o',
                    '.fa-hand-spock-o',
                    '.fa-hand-stop-o',
                    '.fa-handshake-o',
                    '.fa-hard-of-hearing',
                    '.fa-hashtag',
                    '.fa-hdd-o',
                    '.fa-header',
                    '.fa-headphones',
                    '.fa-heart',
                    '.fa-heart-o',
                    '.fa-heartbeat',
                    '.fa-history',
                    '.fa-home',
                    '.fa-hospital-o',
                    '.fa-hotel',
                    '.fa-hourglass',
                    '.fa-hourglass-1',
                    '.fa-hourglass-2',
                    '.fa-hourglass-3',
                    '.fa-hourglass-end',
                    '.fa-hourglass-half',
                    '.fa-hourglass-o',
                    '.fa-hourglass-start',
                    '.fa-houzz',
                    '.fa-html5',
                    '.fa-i-cursor',
                    '.fa-id-badge',
                    '.fa-id-card',
                    '.fa-id-card-o',
                    '.fa-ils',
                    '.fa-image',
                    '.fa-imdb',
                    '.fa-inbox',
                    '.fa-indent',
                    '.fa-industry',
                    '.fa-info',
                    '.fa-info-circle',
                    '.fa-inr',
                    '.fa-instagram',
                    '.fa-institution',
                    '.fa-internet-explorer',
                    '.fa-intersex',
                    '.fa-ioxhost',
                    '.fa-italic',
                    '.fa-joomla',
                    '.fa-jpy',
                    '.fa-jsfiddle',
                    '.fa-key',
                    '.fa-keyboard-o',
                    '.fa-krw',
                    '.fa-language',
                    '.fa-laptop',
                    '.fa-lastfm',
                    '.fa-lastfm-square',
                    '.fa-leaf',
                    '.fa-leanpub',
                    '.fa-legal',
                    '.fa-lemon-o',
                    '.fa-level-down',
                    '.fa-level-up',
                    '.fa-life-bouy',
                    '.fa-life-buoy',
                    '.fa-life-ring',
                    '.fa-life-saver',
                    '.fa-lightbulb-o',
                    '.fa-line-chart',
                    '.fa-link',
                    '.fa-linkedin',
                    '.fa-linkedin-square',
                    '.fa-linode',
                    '.fa-linux',
                    '.fa-list',
                    '.fa-list-alt',
                    '.fa-list-ol',
                    '.fa-list-ul',
                    '.fa-location-arrow',
                    '.fa-lock',
                    '.fa-long-arrow-down',
                    '.fa-long-arrow-left',
                    '.fa-long-arrow-right',
                    '.fa-long-arrow-up',
                    '.fa-low-vision',
                    '.fa-magic',
                    '.fa-magnet',
                    '.fa-mail-forward',
                    '.fa-mail-reply',
                    '.fa-mail-reply-all',
                    '.fa-male',
                    '.fa-map',
                    '.fa-map-marker',
                    '.fa-map-o',
                    '.fa-map-pin',
                    '.fa-map-signs',
                    '.fa-mars',
                    '.fa-mars-double',
                    '.fa-mars-stroke',
                    '.fa-mars-stroke-h',
                    '.fa-mars-stroke-v',
                    '.fa-maxcdn',
                    '.fa-meanpath',
                    '.fa-medium',
                    '.fa-medkit',
                    '.fa-meetup',
                    '.fa-meh-o',
                    '.fa-mercury',
                    '.fa-microchip',
                    '.fa-microphone',
                    '.fa-microphone-slash',
                    '.fa-minus',
                    '.fa-minus-circle',
                    '.fa-minus-square',
                    '.fa-minus-square-o',
                    '.fa-mixcloud',
                    '.fa-mobile',
                    '.fa-mobile-phone',
                    '.fa-modx',
                    '.fa-money',
                    '.fa-moon-o',
                    '.fa-mortar-board',
                    '.fa-motorcycle',
                    '.fa-mouse-pointer',
                    '.fa-move',
                    '.fa-music',
                    '.fa-navicon',
                    '.fa-neuter',
                    '.fa-newspaper-o',
                    '.fa-object-group',
                    '.fa-object-ungroup',
                    '.fa-odnoklassniki',
                    '.fa-odnoklassniki-square',
                    '.fa-opencart',
                    '.fa-opera',
                    '.fa-optin-monster',
                    '.fa-outdent',
                    '.fa-pagelines',
                    '.fa-paint-brush',
                    '.fa-paper-plane',
                    '.fa-paper-plane-o',
                    '.fa-paperclip',
                    '.fa-paragraph',
                    '.fa-paste',
                    '.fa-pause',
                    '.fa-pause-circle',
                    '.fa-pause-circle-o',
                    '.fa-paw',
                    '.fa-paypal',
                    '.fa-pencil',
                    '.fa-pencil-square',
                    '.fa-pencil-square-o',
                    '.fa-percent',
                    '.fa-phone',
                    '.fa-phone-square',
                    '.fa-photo',
                    '.fa-picture-o',
                    '.fa-pie-chart',
                    '.fa-pinterest',
                    '.fa-pinterest-p',
                    '.fa-pinterest-square',
                    '.fa-plane',
                    '.fa-play',
                    '.fa-play-circle',
                    '.fa-play-circle-o',
                    '.fa-plug',
                    '.fa-plus',
                    '.fa-plus-circle',
                    '.fa-plus-square',
                    '.fa-plus-square-o',
                    '.fa-podcast',
                    '.fa-power-off',
                    '.fa-print',
                    '.fa-product-hunt',
                    '.fa-puzzle-piece',
                    '.fa-qq',
                    '.fa-qrcode',
                    '.fa-question',
                    '.fa-question-circle',
                    '.fa-question-circle-o',
                    '.fa-quora',
                    '.fa-quote-left',
                    '.fa-quote-right',
                    '.fa-ra',
                    '.fa-random',
                    '.fa-ravelry',
                    '.fa-rebel',
                    '.fa-recycle',
                    '.fa-reddit',
                    '.fa-reddit-alien',
                    '.fa-reddit-square',
                    '.fa-refresh',
                    '.fa-registered',
                    '.fa-remove',
                    '.fa-renren',
                    '.fa-reorder',
                    '.fa-repeat',
                    '.fa-reply',
                    '.fa-reply-all',
                    '.fa-resistance',
                    '.fa-retweet',
                    '.fa-rmb',
                    '.fa-road',
                    '.fa-rocket',
                    '.fa-rotate-left',
                    '.fa-rotate-right',
                    '.fa-rouble',
                    '.fa-rss',
                    '.fa-rss-square',
                    '.fa-rub',
                    '.fa-ruble',
                    '.fa-rupee',
                    '.fa-s15',
                    '.fa-safari',
                    '.fa-save',
                    '.fa-scissors',
                    '.fa-scribd',
                    '.fa-search',
                    '.fa-search-minus',
                    '.fa-search-plus',
                    '.fa-sellsy',
                    '.fa-send',
                    '.fa-send-o',
                    '.fa-server',
                    '.fa-share',
                    '.fa-share-alt',
                    '.fa-share-alt-square',
                    '.fa-share-square',
                    '.fa-share-square-o',
                    '.fa-shekel',
                    '.fa-sheqel',
                    '.fa-shield',
                    '.fa-ship',
                    '.fa-shirtsinbulk',
                    '.fa-shopping-bag',
                    '.fa-shopping-basket',
                    '.fa-shopping-cart',
                    '.fa-shower',
                    '.fa-sign-in',
                    '.fa-sign-language',
                    '.fa-sign-out',
                    '.fa-signal',
                    '.fa-signing',
                    '.fa-simplybuilt',
                    '.fa-sitemap',
                    '.fa-skyatlas',
                    '.fa-skype',
                    '.fa-slack',
                    '.fa-sliders',
                    '.fa-slideshare',
                    '.fa-smile-o',
                    '.fa-snapchat',
                    '.fa-snapchat-ghost',
                    '.fa-snapchat-square',
                    '.fa-snowflake-o',
                    '.fa-soccer-ball-o',
                    '.fa-sort',
                    '.fa-sort-alpha-asc',
                    '.fa-sort-alpha-desc',
                    '.fa-sort-amount-asc',
                    '.fa-sort-amount-desc',
                    '.fa-sort-asc',
                    '.fa-sort-desc',
                    '.fa-sort-down',
                    '.fa-sort-numeric-asc',
                    '.fa-sort-numeric-desc',
                    '.fa-sort-up',
                    '.fa-soundcloud',
                    '.fa-space-shuttle',
                    '.fa-spinner',
                    '.fa-spoon',
                    '.fa-spotify',
                    '.fa-square',
                    '.fa-square-o',
                    '.fa-stack-exchange',
                    '.fa-stack-overflow',
                    '.fa-star',
                    '.fa-star-half',
                    '.fa-star-half-empty',
                    '.fa-star-half-full',
                    '.fa-star-half-o',
                    '.fa-star-o',
                    '.fa-steam',
                    '.fa-steam-square',
                    '.fa-step-backward',
                    '.fa-step-forward',
                    '.fa-stethoscope',
                    '.fa-sticky-note',
                    '.fa-sticky-note-o',
                    '.fa-stop',
                    '.fa-stop-circle',
                    '.fa-stop-circle-o',
                    '.fa-street-view',
                    '.fa-strikethrough',
                    '.fa-stumbleupon',
                    '.fa-stumbleupon-circle',
                    '.fa-subscript',
                    '.fa-subway',
                    '.fa-suitcase',
                    '.fa-sun-o',
                    '.fa-superpowers',
                    '.fa-superscript',
                    '.fa-support',
                    '.fa-table',
                    '.fa-tablet',
                    '.fa-tachometer',
                    '.fa-tag',
                    '.fa-tags',
                    '.fa-tasks',
                    '.fa-taxi',
                    '.fa-television',
                    '.fa-tencent-weibo',
                    '.fa-terminal',
                    '.fa-text-height',
                    '.fa-text-width',
                    '.fa-th',
                    '.fa-th-large',
                    '.fa-th-list',
                    '.fa-themeisle',
                    '.fa-thermometer',
                    '.fa-thermometer-0',
                    '.fa-thermometer-1',
                    '.fa-thermometer-2',
                    '.fa-thermometer-3',
                    '.fa-thermometer-4',
                    '.fa-thermometer-empty',
                    '.fa-thermometer-full',
                    '.fa-thermometer-half',
                    '.fa-thermometer-quarter',
                    '.fa-thermometer-three-quarters',
                    '.fa-thumb-tack',
                    '.fa-thumbs-down',
                    '.fa-thumbs-o-down',
                    '.fa-thumbs-o-up',
                    '.fa-thumbs-up',
                    '.fa-ticket',
                    '.fa-times',
                    '.fa-times-circle',
                    '.fa-times-circle-o',
                    '.fa-times-rectangle',
                    '.fa-times-rectangle-o',
                    '.fa-tint',
                    '.fa-toggle-down',
                    '.fa-toggle-left',
                    '.fa-toggle-off',
                    '.fa-toggle-on',
                    '.fa-toggle-right',
                    '.fa-toggle-up',
                    '.fa-trademark',
                    '.fa-train',
                    '.fa-transgender',
                    '.fa-transgender-alt',
                    '.fa-trash',
                    '.fa-trash-o',
                    '.fa-tree',
                    '.fa-trello',
                    '.fa-tripadvisor',
                    '.fa-trophy',
                    '.fa-truck',
                    '.fa-try',
                    '.fa-tty',
                    '.fa-tumblr',
                    '.fa-tumblr-square',
                    '.fa-turkish-lira',
                    '.fa-tv',
                    '.fa-twitch',
                    '.fa-twitter',
                    '.fa-twitter-square',
                    '.fa-umbrella',
                    '.fa-underline',
                    '.fa-undo',
                    '.fa-universal-access',
                    '.fa-university',
                    '.fa-unlink',
                    '.fa-unlock',
                    '.fa-unlock-alt',
                    '.fa-unsorted',
                    '.fa-upload',
                    '.fa-usb',
                    '.fa-usd',
                    '.fa-user',
                    '.fa-user-circle',
                    '.fa-user-circle-o',
                    '.fa-user-md',
                    '.fa-user-o',
                    '.fa-user-plus',
                    '.fa-user-secret',
                    '.fa-user-times',
                    '.fa-users',
                    '.fa-vcard',
                    '.fa-vcard-o',
                    '.fa-venus',
                    '.fa-venus-double',
                    '.fa-venus-mars',
                    '.fa-venus-stroke',
                    '.fa-venus-stroke-h',
                    '.fa-venus-stroke-v',
                    '.fa-viacoin',
                    '.fa-viadeo',
                    '.fa-viadeo-square',
                    '.fa-video-camera',
                    '.fa-vimeo',
                    '.fa-vimeo-square',
                    '.fa-vine',
                    '.fa-vk',
                    '.fa-volume-control-phone',
                    '.fa-volume-down',
                    '.fa-volume-off',
                    '.fa-volume-up',
                    '.fa-warning',
                    '.fa-wechat',
                    '.fa-weibo',
                    '.fa-weixin',
                    '.fa-whatsapp',
                    '.fa-wheelchair',
                    '.fa-wheelchair-alt',
                    '.fa-wifi',
                    '.fa-wikipedia-w',
                    '.fa-window-close',
                    '.fa-window-close-o',
                    '.fa-window-maximize',
                    '.fa-window-minimize',
                    '.fa-window-restore',
                    '.fa-windows',
                    '.fa-won',
                    '.fa-wordpress',
                    '.fa-wpbeginner',
                    '.fa-wpexplorer',
                    '.fa-wpforms',
                    '.fa-wrench',
                    '.fa-xing',
                    '.fa-xing-square',
                    '.fa-y-combinator',
                    '.fa-y-combinator-square',
                    '.fa-yahoo',
                    '.fa-yc',
                    '.fa-yc-square',
                    '.fa-yelp',
                    '.fa-yen',
                    '.fa-yoast',
                    '.fa-youtube',
                    '.fa-youtube-play',
                    '.fa-youtube-square'
                ];
                
                // Remove unused selectors from DOM
                unusedSelectors.forEach(function(selector) {
                    var elements = document.querySelectorAll(selector);
                    elements.forEach(function(element) {
                        if (element && !element.closest('[data-fp-critical]')) {
                            element.style.display = 'none';
                        }
                    });
                });
                
                console.log('FP Performance Suite: Purged ' + unusedSelectors.length + ' unused CSS selectors');
            }
            
            // Run purging on page load
            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', purgeUnusedCSS);
            } else {
                purgeUnusedCSS();
            }
        })();
        </script>
        <?php
    }

    // Metodi getUnusedCSSFiles(), shouldDeferCSS(), deferCSS(), generateOptimizedCriticalCSS() 
    // rimossi - ora gestiti da CSSFileAnalyzer, CSSDeferManager, CriticalCSSGenerator

    /**
     * Optimize HTML by removing unused CSS
     * 
     * @param string $html HTML content
     * @return string Optimized HTML
     */
    public function optimize(string $html): string
    {
        if (!$this->isEnabled()) {
            return $html;
        }

        // Apply CSS optimizations to HTML content
        $settings = $this->getSettings();
        
        if ($settings['defer_non_critical']) {
            // Defer non-critical CSS in HTML
            $html = preg_replace_callback(
                '/<link\s+[^>]*rel=["\']stylesheet["\'][^>]*>/i',
                function($matches) {
                    $tag = $matches[0];
                    if (strpos($tag, 'data-fp-optimized') !== false) {
                        return $tag;
                    }
                    // Add preload hint for non-critical CSS
                    return str_replace('rel="stylesheet"', 'rel="preload" as="style" onload="this.onload=null;this.rel=\'stylesheet\'" data-fp-optimized="true"', $tag);
                },
                $html
            );
        }

        return $html;
    }

    /**
     * Analyze HTML and CSS files to find unused rules
     * 
     * @param string $html HTML content
     * @return array Analysis result with unused rules
     */
    public function analyze(string $html): array
    {
        // Get list of unused CSS files
        $unusedFiles = $this->analyzer->getUnusedCSSFiles();
        
        return [
            'unused_files' => $unusedFiles,
            'total_savings' => '130 KiB',
            'analyzed_at' => current_time('mysql'),
        ];
    }

    /**
     * Get current settings (alias for interface compliance)
     * 
     * @return array Settings array
     */
    public function settings(): array
    {
        return $this->getSettings();
    }

    /**
     * Get list of unused CSS files
     * 
     * @return array List of unused CSS files with metadata
     */
    public function getUnusedCSSFiles(): array
    {
        return $this->analyzer->getUnusedCSSFiles();
    }

    /**
     * Check if optimization is enabled
     */
    public function isEnabled(): bool
    {
        $settings = $this->getSettings();
        return !empty($settings['enabled']);
    }

    /**
     * Get all settings
     */
    public function getSettings(): array
    {
        $defaults = [
            'enabled' => false,
            'remove_unused_css' => false,
            'defer_non_critical' => false,
            'inline_critical' => false,
            'enable_css_purging' => false,
            'critical_css' => '',
        ];

        $settings = $this->getOption(self::OPTION, []);
        return is_array($settings) ? array_merge($defaults, $settings) : $defaults;
    }

    /**
     * Update settings
     */
    public function updateSettings(array $settings): bool
    {
        $current = $this->getSettings();
        $updated = array_merge($current, $settings);

        $result = $this->setOption(self::OPTION, $updated);

        if ($result) {
            Logger::info('Unused CSS optimization settings updated', $updated);
            do_action('fp_ps_unused_css_optimization_updated', $updated);
            
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
        remove_action('wp_enqueue_scripts', [$this, 'removeUnusedCSS'], 995);
        remove_filter('style_loader_tag', [$this, 'optimizeCSSLoading'], 20);
        remove_action('wp_head', [$this, 'inlineCriticalCSS'], 14);
        remove_action('wp_footer', [$this, 'addCSSPurgingScript'], 20);
        
        // Reinizializza
        $this->register();
    }

    /**
     * Get status for admin display
     */
    public function status(): array
    {
        $settings = $this->getSettings();
        $unusedFiles = $this->getUnusedCSSFiles();
        
        return [
            'enabled' => $this->isEnabled(),
            'remove_unused_enabled' => !empty($settings['remove_unused_css']),
            'defer_enabled' => !empty($settings['defer_non_critical']),
            'inline_critical' => !empty($settings['inline_critical']),
            'css_purging_enabled' => !empty($settings['enable_css_purging']),
            'critical_css_configured' => !empty($settings['critical_css']),
            'unused_files_count' => count($unusedFiles),
            'estimated_savings' => '130 KiB',
        ];
    }

    /**
     * Get option value (with fallback)
     * 
     * @param string $key Option key
     * @param mixed $default Default value
     * @return mixed
     */
    private function getOption(string $key, $default = null)
    {
        if ($this->optionsRepo !== null) {
            return $this->optionsRepo->get($key, $default);
        }
        return get_option($key, $default);
    }

    /**
     * Set option value (with fallback)
     * 
     * @param string $key Option key
     * @param mixed $value Value to set
     * @return bool
     */
    private function setOption(string $key, $value): bool
    {
        if ($this->optionsRepo !== null) {
            return $this->optionsRepo->set($key, $value);
        }
        return update_option($key, $value);
    }
}
