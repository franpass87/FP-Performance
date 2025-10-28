# Asset Optimization Modules

Moduli per l'ottimizzazione degli asset del plugin FP Performance Suite.

## 📁 Struttura

```
Services/Assets/
├── Optimizer.php                    # Orchestratore principale
├── HtmlMinifier.php                 # Minificazione HTML
├── ScriptOptimizer.php              # Ottimizzazione script tag
├── WordPressOptimizer.php           # Ottimizzazioni WordPress core
├── CriticalCss.php                  # Critical CSS generation
├── Combiners/                       # Combinazione asset
│   ├── DependencyResolver.php       # Risoluzione dipendenze
│   ├── AssetCombinerBase.php        # Classe base combinatori
│   ├── CssCombiner.php              # Combinatore CSS
│   └── JsCombiner.php               # Combinatore JavaScript
└── ResourceHints/                   # Resource hints
    └── ResourceHintsManager.php     # DNS prefetch & preload
```

## 🎯 Componenti

### Optimizer (Orchestratore)

Coordina tutti i moduli di ottimizzazione asset.

**Uso:**
```php
$optimizer = $container->get(Optimizer::class);
$optimizer->register(); // Registra tutti gli hook
```

**Accesso ai sotto-moduli:**
```php
$htmlMinifier = $optimizer->getHtmlMinifier();
$cssCombiner = $optimizer->getCssCombiner();
```

### HtmlMinifier

Minifica l'output HTML rimuovendo spazi bianchi non necessari.

**Uso diretto:**
```php
$minifier = new HtmlMinifier();
$minifier->startBuffer(); // Avvia output buffering
// ... codice che genera HTML ...
$minifier->endBuffer(); // Termina e minifica

// Oppure minificazione diretta
$minified = $minifier->minify($htmlString);
```

**Funzionalità:**
- Output buffering automatico
- Rimozione spazi bianchi e newline
- Preserva funzionalità HTML

### ScriptOptimizer

Aggiunge attributi `defer` e `async` ai tag script.

**Uso:**
```php
$scriptOpt = new ScriptOptimizer();
$tag = $scriptOpt->filterScriptTag($tag, $handle, $src, true, false);
// $defer = true, $async = false

// Configurare script da escludere
$scriptOpt->setSkipHandles(['jquery', 'critical-script']);
```

**Script esclusi di default:**
- `jquery`
- `jquery-core`  
- `jquery-migrate`

### WordPressOptimizer

Ottimizzazioni specifiche per WordPress core.

**Uso:**
```php
$wpOpt = new WordPressOptimizer();

// Rimuovere emoji
$wpOpt->disableEmojis();

// Configurare heartbeat
$wpOpt->registerHeartbeat(60); // 60 secondi
```

**Funzionalità:**
- Rimozione script emoji
- Controllo intervallo heartbeat API
- Riduzione richieste HTTP non necessarie

### ResourceHintsManager

Gestisce hint per DNS prefetch e preload delle risorse.

**Uso:**
```php
$hintsManager = new ResourceHintsManager();

// DNS Prefetch
$hintsManager->setDnsPrefetchUrls([
    'https://fonts.googleapis.com',
    'https://cdn.example.com'
]);

// Preload
$hintsManager->setPreloadUrls([
    'https://example.com/critical.css',
    'https://example.com/main.js'
]);

// Registrare filtri WordPress
add_filter('wp_resource_hints', [$hintsManager, 'addDnsPrefetch'], 10, 2);
add_filter('wp_resource_hints', [$hintsManager, 'addPreloadHints'], 10, 2);
```

**Rilevamento automatico tipo risorsa:**
- `.css` → `style`
- `.js` → `script`
- `.woff`, `.woff2` → `font`
- `.jpg`, `.png`, `.gif` → `image`

### DependencyResolver

Risolve dipendenze tra asset usando ordinamento topologico (algoritmo di Kahn).

**Uso:**
```php
$resolver = new DependencyResolver();

$candidates = [
    'style-a' => ['handle' => 'style-a', 'deps' => []],
    'style-b' => ['handle' => 'style-b', 'deps' => ['style-a']],
    'style-c' => ['handle' => 'style-c', 'deps' => ['style-b']]
];

$positions = ['style-a' => 0, 'style-b' => 1, 'style-c' => 2];

// Risolve ordine rispettando dipendenze
$ordered = $resolver->resolveDependencies($candidates, $positions);
// Risultato: ['style-a', 'style-b', 'style-c']
```

**Caratteristiche:**
- Rileva dipendenze circolari
- Rispetta ordine originale quando possibile
- Filtra dipendenze esterne

### CssCombiner

Combina file CSS multipli in un unico file.

**Uso:**
```php
$resolver = new DependencyResolver();
$cssCombiner = new CssCombiner($resolver);

// Combina CSS durante wp_enqueue_scripts
add_action('wp_enqueue_scripts', function() use ($cssCombiner) {
    $cssCombiner->combine();
}, PHP_INT_MAX);

// Verifica stato
if ($cssCombiner->isCombined()) {
    // CSS combinato con successo
}
```

**Processo:**
1. Analizza queue WordPress CSS
2. Identifica file combinabili (locali, senza condizioni)
3. Risolve dipendenze
4. Crea file combinato
5. Sostituisce file originali con combinato

### JsCombiner

Combina file JavaScript multipli in file separati per head e footer.

**Uso:**
```php
$resolver = new DependencyResolver();
$jsCombiner = new JsCombiner($resolver);

// Combina script head e footer
add_action('wp_enqueue_scripts', function() use ($jsCombiner) {
    $jsCombiner->combine(false); // Head scripts
    $jsCombiner->combine(true);  // Footer scripts
}, PHP_INT_MAX);

// Verifica stato specifico
if ($jsCombiner->isCombined(true)) {
    // Footer scripts combinati
}
```

**Caratteristiche:**
- Gestione separata head/footer
- Rispetta attributi `in_footer`
- Mantiene ordine dipendenze

## 🔌 Integrazione ServiceContainer

Tutti i moduli sono registrati nel ServiceContainer:

```php
// Componenti base
$container->set(HtmlMinifier::class, fn() => new HtmlMinifier());
$container->set(ScriptOptimizer::class, fn() => new ScriptOptimizer());
$container->set(WordPressOptimizer::class, fn() => new WordPressOptimizer());
$container->set(ResourceHintsManager::class, fn() => new ResourceHintsManager());
$container->set(DependencyResolver::class, fn() => new DependencyResolver());

// Optimizer con dependency injection
$container->set(Optimizer::class, function(ServiceContainer $c) {
    return new Optimizer(
        $c->get(Semaphore::class),
        $c->get(HtmlMinifier::class),
        $c->get(ScriptOptimizer::class),
        $c->get(WordPressOptimizer::class),
        $c->get(ResourceHintsManager::class),
        $c->get(DependencyResolver::class)
    );
});
```

## 🧪 Testing

Ogni modulo può essere testato indipendentemente:

```php
class HtmlMinifierTest extends TestCase
{
    public function testMinify()
    {
        $minifier = new HtmlMinifier();
        $html = "  <div>  Hello  </div>  ";
        $minified = $minifier->minify($html);
        $this->assertEquals('<div> Hello </div>', $minified);
    }
}
```

## 🔧 Estensione

### Creare un Nuovo Combinatore

```php
use FP\PerfSuite\Services\Assets\Combiners\AssetCombinerBase;

class SvgCombiner extends AssetCombinerBase
{
    protected function getExtension(): string 
    { 
        return 'svg'; 
    }
    
    protected function getType(): string 
    { 
        return 'svg'; 
    }
    
    public function combine(): bool
    {
        // Implementazione specifica per SVG
        // Usa metodi ereditati da AssetCombinerBase:
        // - isDependencyCombinable()
        // - resolveDependencySource()
        // - writeCombinedAsset()
    }
}
```

### Personalizzare ScriptOptimizer

```php
$scriptOpt = new ScriptOptimizer();

// Aggiungere script personalizzati da escludere
$customSkip = array_merge(
    $scriptOpt->getSkipHandles(),
    ['my-critical-script', 'inline-script']
);
$scriptOpt->setSkipHandles($customSkip);
```

## 📊 Performance

### HtmlMinifier
- **Risparmio:** ~15-25% dimensione HTML
- **Overhead:** Minimo (regex semplici)

### Asset Combiners  
- **Risparmio:** Riduzione richieste HTTP (fino a 80%)
- **Overhead:** Generazione file una tantum (cached)

### ResourceHints
- **Risparmio:** Riduzione latency DNS (~20-120ms per dominio)
- **Overhead:** Nessuno (hint browser nativi)

## ⚠️ Limitazioni

### CssCombiner/JsCombiner
- Combina solo file **locali** (stesso dominio)
- Esclude file con **conditional comments**
- Esclude file con **inline scripts/styles**
- Non compatibile con **dynamic asset loading**

### HtmlMinifier
- Non minifica contenuto `<pre>`, `<code>`, `<textarea>`
- Non comprime inline JavaScript/CSS
- Preserva spazi necessari (es. tra parole)

## 📚 Riferimenti

- [WordPress Enqueue Scripts](https://developer.wordpress.org/reference/functions/wp_enqueue_script/)
- [Resource Hints](https://www.w3.org/TR/resource-hints/)
- [Topological Sort](https://en.wikipedia.org/wiki/Topological_sorting)
- [SOLID Principles](https://en.wikipedia.org/wiki/SOLID)

## 📝 Licenza

Stesso del plugin principale FP Performance Suite.

---

**Versione:** 1.1.0  
**Ultima modifica:** 2025-10-07  
**Autore:** Francesco Passeri