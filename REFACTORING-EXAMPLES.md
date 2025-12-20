# 📚 Esempi di Refactoring - FP Performance Suite

Questo documento contiene esempi concreti di come implementare i refactoring proposti.

---

## Esempio 1: Uso di AbstractFormHandler

### Prima (Codice Duplicato)

```php
// src/Admin/Pages/Assets/FormHandler.php
class FormHandler
{
    public function handle(): string
    {
        if (!isset($_POST['fp_ps_assets_nonce']) || 
            !wp_verify_nonce(wp_unslash($_POST['fp_ps_assets_nonce']), 'fp-ps-assets')) {
            return '';
        }
        
        try {
            $enabled = !empty($_POST['enabled']);
            $ttl = (int) ($_POST['ttl'] ?? 3600);
            // ... logica
        } catch (\Exception $e) {
            error_log('Error: ' . $e->getMessage());
            return 'Errore: ' . $e->getMessage();
        }
    }
}
```

### Dopo (Con AbstractFormHandler)

```php
// src/Admin/Pages/Assets/FormHandler.php
use FP\PerfSuite\Admin\Form\AbstractFormHandler;

class FormHandler extends AbstractFormHandler
{
    public function handle(): string
    {
        if (!$this->isPost()) {
            return '';
        }
        
        if (!$this->verifyNonce('fp_ps_assets_nonce', 'fp-ps-assets')) {
            return '';
        }
        
        try {
            $enabled = $this->sanitizeInput('enabled', 'bool');
            $ttl = $this->sanitizeInput('ttl', 'int') ?? 3600;
            
            // ... logica business
            
            return $this->successMessage(__('Impostazioni salvate', 'fp-performance-suite'));
        } catch (\Throwable $e) {
            return $this->handleError($e, 'Assets form');
        }
    }
}
```

**Vantaggi:**
- ✅ Codice più pulito e leggibile
- ✅ Gestione errori consistente
- ✅ Sanitizzazione type-safe
- ✅ Meno codice duplicato

---

## Esempio 2: Uso di InputSanitizer

### Prima

```php
$title = sanitize_text_field(wp_unslash($_POST['title'] ?? ''));
$email = sanitize_email(wp_unslash($_POST['email'] ?? ''));
$enabled = !empty($_POST['enabled']);
$ttl = (int) ($_POST['ttl'] ?? 3600);
```

### Dopo

```php
use FP\PerfSuite\Utils\InputSanitizer;

$title = InputSanitizer::sanitize('title', 'text');
$email = InputSanitizer::sanitize('email', 'email');
$enabled = InputSanitizer::sanitize('enabled', 'bool');
$ttl = InputSanitizer::sanitize('ttl', 'int') ?? 3600;

// Oppure con schema
$data = InputSanitizer::sanitizeArray($_POST, [
    'title' => 'text',
    'email' => 'email',
    'enabled' => 'bool',
    'ttl' => 'int',
    'description' => 'textarea'
]);
```

**Vantaggi:**
- ✅ Consistenza
- ✅ Type safety
- ✅ Meno codice
- ✅ Facile da testare

---

## Esempio 3: Refactoring RiskMatrix

### Prima (Array Hardcoded)

```php
// src/Admin/RiskMatrix.php (1359 righe)
class RiskMatrix
{
    private static $matrix = [
        'page_cache' => [
            'risk' => 'green',
            'title' => 'Rischio Basso',
            // ... 64+ opzioni hardcoded
        ],
        // ... 1300+ righe di array
    ];
}
```

### Dopo (Config Separato)

```php
// config/risk-matrix.json
{
    "cache": {
        "page_cache": {
            "risk": "green",
            "title": "Rischio Basso",
            "description": "...",
            "risks": "...",
            "why_fails": "...",
            "advice": "..."
        }
    },
    "assets": {
        "defer_javascript": {
            "risk": "green",
            // ...
        }
    }
}

// src/Admin/RiskMatrix/RiskMatrixLoader.php
class RiskMatrixLoader
{
    public static function load(): array
    {
        $configPath = FP_PERF_SUITE_DIR . '/config/risk-matrix.json';
        
        if (!file_exists($configPath)) {
            return self::getDefaultMatrix();
        }
        
        $json = file_get_contents($configPath);
        $data = json_decode($json, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            error_log('[FP-PerfSuite] Invalid risk matrix JSON: ' . json_last_error_msg());
            return self::getDefaultMatrix();
        }
        
        return self::flattenMatrix($data);
    }
    
    private static function flattenMatrix(array $categorized): array
    {
        $flat = [];
        foreach ($categorized as $category => $options) {
            $flat = array_merge($flat, $options);
        }
        return $flat;
    }
}

// src/Admin/RiskMatrix.php (semplificato)
class RiskMatrix
{
    private static ?array $matrix = null;
    
    public static function getRiskLevel(string $key): string
    {
        $matrix = self::getMatrix();
        return $matrix[$key]['risk'] ?? self::RISK_AMBER;
    }
    
    private static function getMatrix(): array
    {
        if (self::$matrix === null) {
            self::$matrix = RiskMatrixLoader::load();
        }
        return self::$matrix;
    }
}
```

**Vantaggi:**
- ✅ Configurazione separata dal codice
- ✅ Facile da modificare
- ✅ Possibilità di versioning
- ✅ Testabilità migliorata

---

## Esempio 4: Refactoring ThirdPartyTab

### Prima (God Method)

```php
// src/Admin/Pages/Assets/Tabs/ThirdPartyTab.php (966 righe)
class ThirdPartyTab
{
    public function render(array $data): string
    {
        // 966 righe di codice in un solo metodo!
        ob_start();
        // ... tutto il rendering qui
        return ob_get_clean();
    }
}
```

### Dopo (Componenti Separati)

```php
// src/Admin/Pages/Assets/Tabs/ThirdParty/ThirdPartyTabRenderer.php
class ThirdPartyTabRenderer
{
    private ScriptDetectorSection $scriptDetector;
    private ConfigurationSection $configSection;
    private Http2PushSection $http2Section;
    private SmartDeliverySection $smartDeliverySection;
    
    public function __construct(
        ScriptDetectorSection $scriptDetector,
        ConfigurationSection $configSection,
        Http2PushSection $http2Section,
        SmartDeliverySection $smartDeliverySection
    ) {
        $this->scriptDetector = $scriptDetector;
        $this->configSection = $configSection;
        $this->http2Section = $http2Section;
        $this->smartDeliverySection = $smartDeliverySection;
    }
    
    public function render(array $data): string
    {
        ob_start();
        $this->renderHeader();
        echo $this->scriptDetector->render($data);
        echo $this->configSection->render($data);
        echo $this->http2Section->render($data);
        echo $this->smartDeliverySection->render($data);
        return ob_get_clean();
    }
    
    private function renderHeader(): void
    {
        echo '<div class="fp-ps-tab-content" data-tab="thirdparty">';
        echo RiskLegend::renderLegend();
    }
}

// src/Admin/Pages/Assets/Tabs/ThirdParty/Sections/ScriptDetectorSection.php
class ScriptDetectorSection
{
    public function render(array $data): string
    {
        // Solo logica per script detector
        // ~150 righe invece di 966
    }
}

// src/Admin/Pages/Assets/Tabs/ThirdPartyTab.php (semplificato)
class ThirdPartyTab
{
    private ThirdPartyTabRenderer $renderer;
    
    public function __construct(ServiceContainer $container)
    {
        $this->renderer = new ThirdPartyTabRenderer(
            new ScriptDetectorSection($container),
            new ConfigurationSection($container),
            new Http2PushSection($container),
            new SmartDeliverySection($container)
        );
    }
    
    public function render(array $data): string
    {
        return $this->renderer->render($data);
    }
}
```

**Vantaggi:**
- ✅ Metodi più piccoli e focalizzati
- ✅ Testabilità per componente
- ✅ Riusabilità
- ✅ Manutenibilità

---

## Esempio 5: Refactoring UnusedCSSOptimizer

### Prima (Tutto in Una Classe)

```php
// src/Services/Assets/UnusedCSSOptimizer.php (1309 righe)
class UnusedCSSOptimizer
{
    public function optimize(string $html): string
    {
        // Analisi, cache, rimozione, reporting tutto insieme
    }
    
    private function analyze(string $html): array { /* ... */ }
    private function findUnused(array $analysis): array { /* ... */ }
    private function remove(array $unused): string { /* ... */ }
    private function cacheResult(string $key, array $data): void { /* ... */ }
    private function generateReport(array $data): string { /* ... */ }
}
```

### Dopo (Separazione Responsabilità)

```php
// src/Services/Assets/UnusedCSS/UnusedCSSAnalyzer.php
class UnusedCSSAnalyzer
{
    public function analyze(string $html, array $cssFiles): AnalysisResult
    {
        // Solo analisi
    }
    
    public function findUnused(AnalysisResult $analysis): array
    {
        // Solo ricerca unused
    }
}

// src/Services/Assets/UnusedCSS/UnusedCSSRemover.php
class UnusedCSSRemover
{
    public function remove(array $unusedRules, string $html): string
    {
        // Solo rimozione
    }
}

// src/Services/Assets/UnusedCSS/UnusedCSSCache.php
class UnusedCSSCache
{
    public function get(string $key): ?array { /* ... */ }
    public function set(string $key, array $data): void { /* ... */ }
    public function invalidate(string $key): void { /* ... */ }
}

// src/Services/Assets/UnusedCSSOptimizer.php (orchestrator)
class UnusedCSSOptimizer
{
    public function __construct(
        private UnusedCSSAnalyzer $analyzer,
        private UnusedCSSRemover $remover,
        private UnusedCSSCache $cache
    ) {}
    
    public function optimize(string $html): string
    {
        $cssFiles = $this->getCssFiles($html);
        $cacheKey = md5($html . serialize($cssFiles));
        
        $unused = $this->cache->get($cacheKey);
        
        if ($unused === null) {
            $analysis = $this->analyzer->analyze($html, $cssFiles);
            $unused = $this->analyzer->findUnused($analysis);
            $this->cache->set($cacheKey, $unused);
        }
        
        return $this->remover->remove($unused, $html);
    }
}
```

**Vantaggi:**
- ✅ Single Responsibility Principle
- ✅ Dependency Injection
- ✅ Testabilità per componente
- ✅ Riusabilità

---

## 🎯 Checklist Implementazione

Quando implementi un refactoring:

- [ ] Scrivere test prima del refactoring
- [ ] Creare branch separato
- [ ] Refactoring incrementale (piccoli step)
- [ ] Verificare che i test passino dopo ogni step
- [ ] Code review
- [ ] Test di regressione
- [ ] Documentazione aggiornata

---

**Nota:** Questi esempi sono template. Adatta alle esigenze specifiche del tuo codice.




