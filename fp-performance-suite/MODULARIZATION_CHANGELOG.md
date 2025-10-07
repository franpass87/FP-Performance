# Changelog Modularizzazione

## [Unreleased] - 2025-10-07

### 🎉 Modularizzazione Fase 2 - WebP Converter

#### ✨ Aggiunto

**Services/Media/WebP/**
- `WebPImageConverter.php` - Motore conversione immagini (Imagick/GD)
- `WebPQueue.php` - Gestione coda conversioni bulk
- `WebPBatchProcessor.php` - Processamento batch via cron
- `WebPAttachmentProcessor.php` - Processamento attachment WordPress
- `WebPPathHelper.php` - Utilità manipolazione path WebP

**Documentazione**
- `src/Services/Media/WebP/README.md` - Guida moduli WebP

#### 🔄 Modificato

**Services/Media/WebPConverter.php**
- **Ridotto da 506 a 239 righe** (-53%)
- Convertito da classe monolitica a orchestratore
- Aggiunto supporto dependency injection per tutti i moduli
- Aggiunti metodi getter per accedere ai componenti modulari:
  - `getImageConverter()`
  - `getQueue()`
  - `getBatchProcessor()`
  - `getAttachmentProcessor()`
  - `getPathHelper()`
- Mantenuta piena retrocompatibilità con API esistente

**src/Plugin.php**
- Aggiornato `ServiceContainer` per registrare nuovi moduli WebP
- Modificata registrazione `WebPConverter::class` per usare dependency injection
- Aggiunte registrazioni per:
  - `WebPImageConverter::class`
  - `WebPQueue::class`
  - `WebPBatchProcessor::class`
  - `WebPAttachmentProcessor::class`
  - `WebPPathHelper::class`

#### 🗑️ Deprecato

```php
/**
 * @deprecated Use WebPImageConverter::convert() directly
 */
public function convert(string $file, array $settings, bool $force = false): bool
```

#### 📊 Metriche Modularizzazione WebP

| Metrica | Prima | Dopo | Δ |
|---------|-------|------|---|
| File WebPConverter.php | 506 righe | 239 righe | -53% |
| Classi modulo WebP | 1 | 6 | +500% |
| Responsabilità/classe | Molte | 1 | SRP ✅ |

---

## [Modularizzazione Fase 1] - 2025-10-07

### ✨ Aggiunto

#### Nuove Classi Modulari

**Services/Assets/**
- `HtmlMinifier.php` - Gestione minificazione HTML
- `ScriptOptimizer.php` - Ottimizzazione tag script (defer/async)
- `WordPressOptimizer.php` - Ottimizzazioni WordPress core (emoji, heartbeat)

**Services/Assets/Combiners/**
- `DependencyResolver.php` - Risoluzione dipendenze con algoritmo topologico di Kahn
- `AssetCombinerBase.php` - Classe base astratta per combinatori asset
- `CssCombiner.php` - Combinatore specifico per file CSS
- `JsCombiner.php` - Combinatore specifico per file JavaScript

**Services/Assets/ResourceHints/**
- `ResourceHintsManager.php` - Gestione DNS prefetch e preload hints

#### Documentazione
- `docs/MODULARIZATION_REPORT.md` - Report completo della modularizzazione
- `MODULARIZATION_CHANGELOG.md` - Questo file

### 🔄 Modificato

#### Services/Assets/Optimizer.php
- **Ridotto da 944 a ~360 righe** (-62%)
- Convertito da classe monolitica a orchestratore
- Aggiunto supporto dependency injection per tutti i moduli
- Aggiunti metodi getter per accedere ai componenti modulari:
  - `getHtmlMinifier()`
  - `getScriptOptimizer()`
  - `getWordPressOptimizer()`
  - `getResourceHintsManager()`
  - `getCssCombiner()`
  - `getJsCombiner()`
- Mantenuta piena retrocompatibilità con API esistente

#### src/Plugin.php
- Aggiornato `ServiceContainer` per registrare i nuovi moduli
- Modificata registrazione `Optimizer::class` per usare dependency injection
- Aggiunte registrazioni per:
  - `HtmlMinifier::class`
  - `ScriptOptimizer::class`
  - `WordPressOptimizer::class`
  - `ResourceHints\ResourceHintsManager::class`
  - `Combiners\DependencyResolver::class`

### 🗑️ Deprecato

I seguenti metodi di `Optimizer` sono ora deprecati ma rimangono funzionanti:

```php
/**
 * @deprecated Use HtmlMinifier::minify() directly
 */
public function minifyHtml(string $html): string

/**
 * @deprecated Use ResourceHintsManager directly  
 */
public function dnsPrefetch(array $hints, string $relation): array

/**
 * @deprecated Use ResourceHintsManager directly
 */
public function preloadResources(array $hints, string $relation): array

/**
 * @deprecated Use WordPressOptimizer directly
 */
public function heartbeatSettings(array $settings): array
```

### 🔒 Retrocompatibilità

✅ **Nessun breaking change**
- Tutti i metodi pubblici esistenti funzionano come prima
- L'interfaccia pubblica rimane invariata
- I metodi deprecati continuano a funzionare delegando ai nuovi moduli
- ServiceContainer gestisce automaticamente le dipendenze

### 📊 Metriche di Miglioramento

| Aspetto | Prima | Dopo | Δ |
|---------|-------|------|---|
| File Optimizer.php | 944 righe | ~360 righe | -62% |
| Classi modulo Assets | 2 | 10 | +400% |
| Linee codice massime/file | 944 | ~250 | -73% |
| Responsabilità/classe | Molte | 1 | SRP ✅ |

### 🎯 Principi SOLID Applicati

- ✅ **Single Responsibility** - Ogni classe ha una sola responsabilità
- ✅ **Open/Closed** - Aperto all'estensione, chiuso alla modifica
- ✅ **Liskov Substitution** - I combinatori sono sostituibili
- ✅ **Interface Segregation** - Interfacce focalizzate
- ✅ **Dependency Inversion** - Dipendenze iniettate, non create

### 🧪 Testing

I nuovi moduli sono progettati per essere facilmente testabili:
- Ogni componente può essere testato in isolamento
- Mock/stub semplificati
- Dependency injection facilita i test
- Nessuna dipendenza da stato globale

### 📝 Note per Sviluppatori

#### Uso dei Nuovi Moduli

**Accesso diretto via Container:**
```php
$htmlMinifier = $container->get(\FP\PerfSuite\Services\Assets\HtmlMinifier::class);
$htmlMinifier->startBuffer();
```

**Accesso via Optimizer (retrocompatibile):**
```php
$optimizer = $container->get(Optimizer::class);
$optimizer->startBuffer(); // Delega a HtmlMinifier
```

**Accesso ai componenti via getter:**
```php
$optimizer = $container->get(Optimizer::class);
$cssCombin = $optimizer->getCssCombiner();
$cssCombin->combine();
```

#### Estensione

Per creare un nuovo tipo di combinatore:

```php
class CustomCombiner extends AssetCombinerBase
{
    protected function getExtension(): string { return 'custom'; }
    protected function getType(): string { return 'custom'; }
    
    public function combine(): bool
    {
        // Implementazione personalizzata
    }
}
```

### 🔮 Prossimi Passi

#### Priorità Alta
- ✅ Modularizzazione `Optimizer.php` - **COMPLETATO**

#### Priorità Media  
- ⏳ Modularizzazione `WebPConverter.php` (506 righe)
  - Separare `WebPQueue.php`
  - Separare `WebPBatchProcessor.php`
  - Mantenere `WebPConverter.php` come orchestratore

#### Priorità Bassa
- ⏳ Refactoring Admin Pages (se superano 500 righe)
- ⏳ Aggiungere Unit Tests per i nuovi moduli
- ⏳ Creare interfacce formali (CombinerInterface, etc.)
- ⏳ Documentazione API completa

### ⚠️ Breaking Changes

**NESSUNO** - La modularizzazione è stata progettata per mantenere completa retrocompatibilità.

### 🐛 Bug Fix

Nessun bug fix in questa modularizzazione - focus su architettura e manutenibilità.

---

**Tipo di Modifica:** Refactoring architetturale  
**Impatto:** Interno - Nessun impatto su funzionalità utente  
**Versione Target:** 1.1.0 o 2.0.0 (da decidere)  
**Autore:** Francesco Passeri  
**Data:** 2025-10-07