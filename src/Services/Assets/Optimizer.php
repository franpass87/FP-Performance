<?php

namespace FP\PerfSuite\Services\Assets;

use FP\PerfSuite\Services\Assets\Combiners\CssCombiner;
use FP\PerfSuite\Services\Assets\Combiners\DependencyResolver;
use FP\PerfSuite\Services\Assets\Combiners\JsCombiner;
use FP\PerfSuite\Services\Assets\Optimizer\SettingsManager;
use FP\PerfSuite\Services\Assets\Optimizer\DataSanitizer;
use FP\PerfSuite\Services\Assets\Optimizer\RequestExclusionChecker;
use FP\PerfSuite\Services\Assets\ResourceHints\ResourceHintsManager;
use FP\PerfSuite\Utils\Semaphore;

use function __;
use function array_key_exists;
use function esc_url_raw;
use function filter_var;
use function is_admin;
use function is_array;
use function is_bool;
use function is_float;
use function is_int;
use function is_string;
use function preg_split;
use function trim;
use function array_unique;
use function array_values;

use const FILTER_NULL_ON_FAILURE;
use const FILTER_VALIDATE_BOOLEAN;

/**
 * Asset Optimization Orchestrator
 *
 * Coordinates various asset optimization strategies including minification,
 * combination, defer/async loading, and resource hints.
 *
 * @author Francesco Passeri
 * @link https://francescopasseri.com
 */
class Optimizer
{

    private Semaphore $semaphore;
    private HtmlMinifier $htmlMinifier;
    private ScriptOptimizer $scriptOptimizer;
    private WordPressOptimizer $wpOptimizer;
    private ResourceHintsManager $resourceHints;
    private CssCombiner $cssCombiner;
    private JsCombiner $jsCombiner;
    private SettingsManager $settingsManager;
    private DataSanitizer $sanitizer;
    private RequestExclusionChecker $exclusionChecker;

    public function __construct(
        Semaphore $semaphore,
        ?HtmlMinifier $htmlMinifier = null,
        ?ScriptOptimizer $scriptOptimizer = null,
        ?WordPressOptimizer $wpOptimizer = null,
        ?ResourceHintsManager $resourceHints = null,
        ?DependencyResolver $dependencyResolver = null
    ) {
        $this->semaphore = $semaphore;
        $this->htmlMinifier = $htmlMinifier ?? new HtmlMinifier();
        $this->scriptOptimizer = $scriptOptimizer ?? new ScriptOptimizer();
        $this->wpOptimizer = $wpOptimizer ?? new WordPressOptimizer();
        $this->resourceHints = $resourceHints ?? new ResourceHintsManager();

        $dependencyResolver = $dependencyResolver ?? new DependencyResolver();
        $this->cssCombiner = new CssCombiner($dependencyResolver);
        $this->jsCombiner = new JsCombiner($dependencyResolver);
        
        // Inizializza le dipendenze mancanti
        $this->sanitizer = new DataSanitizer();
        $this->settingsManager = new SettingsManager($this->sanitizer);
        $this->exclusionChecker = new RequestExclusionChecker();
    }

    public function register(): void
    {
        $settings = $this->settings();

        if (!is_admin() && !$this->isRestOrAjaxRequest()) {
            // ESCLUSIONE AUTOMATICA: FP Privacy & Cookie Policy Plugin
            // Non applicare ottimizzazioni se il consenso non è stato dato
            if ($this->shouldExcludeForPrivacyPlugin()) {
                // Banner cookie attivo, disabilita ottimizzazioni aggressive
                return;
            }

            // HTML Minification
            if (!empty($settings['minify_html'])) {
                add_action('template_redirect', [$this, 'startBuffer'], 1);
                add_action('shutdown', [$this, 'endBuffer'], PHP_INT_MAX);
            }

            // Script defer/async - PRIORITÀ ALTA per ottimizzazioni base
            if (!empty($settings['defer_js'])) {
                add_filter('script_loader_tag', [$this, 'filterScriptTag'], 5, 3);
            }

            // CSS async loading - PRIORITÀ ALTA per ottimizzazioni base
            if (!empty($settings['async_css'])) {
                add_filter('style_loader_tag', [$this, 'filterStyleTag'], 5, 4);
            }

            // Resource hints
            if (!empty($settings['dns_prefetch'])) {
                $this->resourceHints->setDnsPrefetchUrls($settings['dns_prefetch']);
                add_filter('wp_resource_hints', [$this->resourceHints, 'addDnsPrefetch'], 10, 2);
            }

            if (!empty($settings['preload'])) {
                $this->resourceHints->setPreloadUrls($settings['preload']);
                add_filter('wp_resource_hints', [$this->resourceHints, 'addPreloadHints'], 10, 2);
            }

            if (!empty($settings['preconnect'])) {
                $this->resourceHints->setPreconnectUrls($settings['preconnect']);
                add_filter('wp_resource_hints', [$this->resourceHints, 'addPreconnectHints'], 10, 2);
            }

            // Asset combination
            if (!empty($settings['combine_css']) || !empty($settings['combine_js'])) {
                add_action('wp_enqueue_scripts', [$this, 'applyCombination'], 992);
            }
        }

        // WordPress optimizations
        // BUGFIX #10: disableEmojis() deve essere chiamato in hook 'init' PRIMA di wp_head
        if (!empty($settings['remove_emojis'])) {
            $wpOptimizer = $this->wpOptimizer; // Cattura variabile per closure
            add_action('init', function() use ($wpOptimizer) {
                $wpOptimizer->disableEmojis();
            }, 1); // Priorità 1 = molto presto
        }

        if (!empty($settings['heartbeat_admin'])) {
            $this->wpOptimizer->registerHeartbeat((int) $settings['heartbeat_admin']);
        }
    }

    /**
     * Get current settings
     *
     * @return array{
     *  enabled:bool,
     *  minify_html:bool,
     *  defer_js:bool,
     *  async_js:bool,
     *  async_css:bool,
     *  remove_emojis:bool,
     *  dns_prefetch:array<int,string>,
     *  preload:array<int,string>,
     *  preconnect:array<int,mixed>,
     *  heartbeat_admin:int,
     *  combine_css:bool,
     *  combine_js:bool,
     *  critical_css_handles:array<int,string>
     * }
     */
    public function settings(): array
    {
        return $this->settingsManager->get();
    }

    public function update(array $settings): bool
    {
        $result = $this->settingsManager->update($settings);
        
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
        remove_action('template_redirect', [$this, 'startBuffer'], 1);
        remove_action('shutdown', [$this, 'endBuffer'], PHP_INT_MAX);
        remove_filter('script_loader_tag', [$this, 'filterScriptTag'], 5);
        remove_filter('style_loader_tag', [$this, 'filterStyleTag'], 5);
        remove_filter('wp_resource_hints', [$this->resourceHints, 'addDnsPrefetch'], 10);
        remove_filter('wp_resource_hints', [$this->resourceHints, 'addPreloadHints'], 10);
        remove_filter('wp_resource_hints', [$this->resourceHints, 'addPreconnectHints'], 10);
        remove_action('wp_enqueue_scripts', [$this, 'applyCombination'], 992);
        
        // Nota: Gli hook di wpOptimizer (closure anonime) non possono essere rimossi facilmente
        // ma questo non è critico perché register() viene chiamato solo quando necessario
        // e i filtri WordPress gestiscono correttamente i duplicati
        
        // Reinizializza
        $this->register();
    }
    
    // Metodi settings() e update() rimossi - ora gestiti da SettingsManager

    public function applyCombination(): void
    {
        if (is_admin()) {
            return;
        }

        $settings = $this->settings();

        if (!empty($settings['combine_css'])) {
            $this->cssCombiner->combine();
        }

        if (!empty($settings['combine_js'])) {
            $this->jsCombiner->combine(false);  // head scripts
            $this->jsCombiner->combine(true);   // footer scripts
        }
    }

    public function startBuffer(): void
    {
        $this->htmlMinifier->startBuffer();
    }

    public function endBuffer(): void
    {
        $this->htmlMinifier->endBuffer();
    }

    /**
     * @deprecated Use HtmlMinifier::minify() directly
     */
    public function minifyHtml(string $html): string
    {
        return $this->htmlMinifier->minify($html);
    }

    public function filterScriptTag(string $tag, string $handle, string $src): string
    {
        // ESCLUSIONE: Script del plugin FP Privacy & Cookie Policy
        if ($this->isPrivacyPluginAsset($handle, $src)) {
            return $tag; // Non modificare gli script del plugin privacy
        }

        // ESCLUSIONE: Script del plugin FP Restaurant Reservations
        if ($this->isReservationsPluginAsset($handle, $src)) {
            return $tag; // Non modificare gli script del plugin prenotazioni
        }

        $settings = $this->settings();
        $defer = !empty($settings['defer_js']);
        $async = !empty($settings['async_js']);

        return $this->scriptOptimizer->filterScriptTag($tag, $handle, $src, $defer, $async);
    }

    /**
     * Filter style tag to make CSS load asynchronously
     *
     * @param string $html Style tag HTML
     * @param string $handle Style handle
     * @param string $href Style URL
     * @param string $media Media attribute
     * @return string Modified style tag
     */
    public function filterStyleTag(string $html, string $handle, string $href, $media): string
    {
        // ESCLUSIONE: CSS del plugin FP Privacy & Cookie Policy
        if ($this->isPrivacyPluginAsset($handle, $href)) {
            return $html; // Non modificare i CSS del plugin privacy
        }

        // ESCLUSIONE: CSS del plugin FP Restaurant Reservations
        if ($this->isReservationsPluginAsset($handle, $href)) {
            return $html; // Non modificare i CSS del plugin prenotazioni
        }

        // Skip critical CSS handles (should load synchronously)
        $settings = $this->settings();
        $criticalHandles = $settings['critical_css_handles'] ?? [];
        
        if (in_array($handle, $criticalHandles, true)) {
            return $html;
        }

        // Skip admin CSS
        if (strpos($handle, 'admin') !== false) {
            return $html;
        }

        // Convert to async loading using preload trick
        // Reference: https://web.dev/defer-non-critical-css/
        $html = str_replace(
            "rel='stylesheet'",
            "rel='preload' as='style' onload=\"this.onload=null;this.rel='stylesheet'\"",
            $html
        );

        // Add noscript fallback
        $noscriptTag = str_replace(
            "rel='preload' as='style' onload=\"this.onload=null;this.rel='stylesheet'\"",
            "rel='stylesheet'",
            $html
        );
        $html .= "\n<noscript>" . $noscriptTag . "</noscript>";

        return $html;
    }

    /**
     * @deprecated Use ResourceHintsManager directly
     */
    public function dnsPrefetch(array $hints, string $relation): array
    {
        return $this->resourceHints->addDnsPrefetch($hints, $relation);
    }

    /**
     * @deprecated Use ResourceHintsManager directly
     */
    public function preloadResources(array $hints, string $relation): array
    {
        return $this->resourceHints->addPreloadHints($hints, $relation);
    }

    /**
     * @deprecated Use WordPressOptimizer directly
     */
    public function heartbeatSettings(array $settings): array
    {
        $current = $this->settings();
        return $this->wpOptimizer->configureHeartbeat($settings, (int) $current['heartbeat_admin']);
    }

    public function status(): array
    {
        $settings = $this->settings();
        // FIX: Restituisce valori booleani espliciti invece di usare !empty()
        // Questo permette di distinguere tra false esplicito e valore mancante
        return [
            'minify_html' => isset($settings['minify_html']) ? (bool) $settings['minify_html'] : false,
            'defer_js' => isset($settings['defer_js']) ? (bool) $settings['defer_js'] : false,
            'async_js' => isset($settings['async_js']) ? (bool) $settings['async_js'] : false,
            'remove_emojis' => isset($settings['remove_emojis']) ? (bool) $settings['remove_emojis'] : false,
            'heartbeat_admin' => (int) ($settings['heartbeat_admin'] ?? 60),
            'combine_css' => isset($settings['combine_css']) ? (bool) $settings['combine_css'] : false,
            'combine_js' => isset($settings['combine_js']) ? (bool) $settings['combine_js'] : false,
        ];
    }

    /**
     * @return array<int, array<string, string>>
     */
    public function riskLevels(): array
    {
        return [
            $this->semaphore->describe('remove_emojis', 'green', __('Disables emoji scripts to save requests.', 'fp-performance-suite')),
            $this->semaphore->describe('defer_js', 'amber', __('Defers non-critical JavaScript to speed rendering.', 'fp-performance-suite')),
            $this->semaphore->describe('combine_js', 'red', __('Combining scripts may break complex setups. Proceed with caution.', 'fp-performance-suite')),
        ];
    }

    /**
     * Sanitize URL list
     *
     * @param mixed $value
     * @return array<int, string>
     */
    private function sanitizeUrlList($value): array
    {
        if (is_string($value)) {
            $value = preg_split('/[\r\n,]+/', $value) ?: [];
        }

        if (!is_array($value)) {
            return [];
        }

        $urls = [];
        foreach ($value as $entry) {
            if (is_array($entry)) {
                $entry = $entry['href'] ?? $entry['url'] ?? null;
            }

            if (!is_string($entry)) {
                continue;
            }
            $trimmed = trim($entry);
            if ($trimmed === '') {
                continue;
            }
            $sanitized = esc_url_raw($trimmed);
            if ($sanitized === '') {
                continue;
            }
            $urls[] = $sanitized;
        }

        return array_values(array_unique($urls));
    }

    /**
     * Sanitize handle list (comma or newline separated)
     *
     * @param mixed $value
     * @return array<int, string>
     */
    private function sanitizeHandleList($value): array
    {
        if (is_string($value)) {
            $value = preg_split('/[\r\n,]+/', $value) ?: [];
        }

        if (!is_array($value)) {
            return [];
        }

        $handles = [];
        foreach ($value as $handle) {
            if (!is_string($handle)) {
                continue;
            }
            $trimmed = trim($handle);
            if ($trimmed === '') {
                continue;
            }
            // Sanitize handle (allow alphanumeric, dash, underscore)
            $sanitized = preg_replace('/[^a-zA-Z0-9_-]/', '', $trimmed);
            if ($sanitized === '') {
                continue;
            }
            $handles[] = $sanitized;
        }

        return array_values(array_unique($handles));
    }

    private function resolveFlag(array $settings, string $key, bool $current): bool
    {
        if (!array_key_exists($key, $settings)) {
            return $current;
        }

        return $this->interpretFlag($settings[$key], $current);
    }

    /**
     * @param mixed $value
     */
    private function interpretFlag($value, bool $fallback): bool
    {
        if (is_bool($value)) {
            return $value;
        }

        if (is_int($value) || is_float($value)) {
            return (bool) $value;
        }

        if (is_string($value)) {
            $trimmed = trim($value);
            if ($trimmed === '') {
                return false;
            }

            $filtered = filter_var($trimmed, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
            if ($filtered !== null) {
                return $filtered;
            }
        }

        return $fallback;
    }

    // Getters for modular components (useful for testing and extension)

    public function getHtmlMinifier(): HtmlMinifier
    {
        return $this->htmlMinifier;
    }

    public function getScriptOptimizer(): ScriptOptimizer
    {
        return $this->scriptOptimizer;
    }

    public function getWordPressOptimizer(): WordPressOptimizer
    {
        return $this->wpOptimizer;
    }

    public function getResourceHintsManager(): ResourceHintsManager
    {
        return $this->resourceHints;
    }

    public function getCssCombiner(): CssCombiner
    {
        return $this->cssCombiner;
    }

    public function getJsCombiner(): JsCombiner
    {
        return $this->jsCombiner;
    }

    private function isRestOrAjaxRequest(): bool
    {
        return $this->exclusionChecker->isRestOrAjaxRequest();
    }

    private function shouldExcludeForPrivacyPlugin(): bool
    {
        return $this->exclusionChecker->shouldExcludeForPrivacyPlugin();
    }

    private function isPrivacyPluginAsset(string $handle, string $src): bool
    {
        return $this->exclusionChecker->isPrivacyPluginAsset($handle, $src);
    }

    private function isReservationsPluginAsset(string $handle, string $src): bool
    {
        return $this->exclusionChecker->isReservationsPluginAsset($handle, $src);
    }
    
    // Metodi isRestOrAjaxRequest(), shouldExcludeForPrivacyPlugin(), isPrivacyPluginAsset(), isReservationsPluginAsset() rimossi - ora gestiti da RequestExclusionChecker
}