# Refactoring: Asset Management vs Compatibility

## 📋 Problema Identificato

In precedenza, il modulo `Services/Compatibility/CompatibilityFilters.php` conteneva funzioni che gestivano direttamente gli **asset** (script, font, immagini) invece di limitarsi alla **compatibilità** pura tra tema/builder e plugin.

Questo causava confusione architetturale, poiché:
- **Asset management** (gestione script, font, immagini) dovrebbe stare in `Services/Assets/`
- **Compatibility** (compatibilità editor, cache purge, fix UX) dovrebbe rimanere in `Services/Compatibility/`

## ✅ Soluzione Implementata

### 1. Nuovo File: `src/Services/Assets/ThemeAssetConfiguration.php`

Creato un nuovo servizio dedicato che centralizza **tutte** le configurazioni di asset specifiche per tema/builder:

#### Responsabilità:
- **Script Exclusions**: Pattern di esclusione per script che non devono essere ritardati
- **HTTP/2 Push Fonts**: Font critici da pushare via HTTP/2 per ogni tema
- **Critical Handles**: Handle critici per CSS/JS per ogni tema/builder
- **Image Optimization Config**: Configurazioni ottimali per immagini
- **AVIF Compatibility**: Info sulla sicurezza AVIF per ogni tema

#### Metodi Principali:
```php
// Filtra quali script devono essere ritardati
filterScriptDelay(bool $should_delay, string $src): bool

// Ottiene pattern di esclusione per il tema
getScriptExclusionPatterns(): array

// Aggiunge font critici per HTTP/2 push
addCriticalFonts(array $fonts): array

// Aggiunge handle critici per HTTP/2 push
addCriticalHandles(array $handles, string $type): array

// Configurazione ottimale immagini per tema
getImageOptimizationConfig(): array

// Compatibilità AVIF per tema
getAVIFCompatibility(): array

// Raccomandazioni complete
getRecommendations(): array
```

#### Temi Supportati:
- **Salient** (+ WPBakery)
- **Avada** (+ Fusion Builder)
- **Divi** (+ Divi Builder)
- **Astra** (generico)
- **Elementor**
- **WPBakery**
- **Gutenberg**

### 2. Aggiornato: `src/Services/Compatibility/CompatibilityFilters.php`

Rimosso tutto ciò che riguarda asset, mantenendo solo:

#### Responsabilità (ora più chiare):
- ✅ **Disabilitazione ottimizzazioni negli editor** (WPBakery, Elementor)
- ✅ **Cache purge** quando cambiano opzioni tema
- ✅ **Fix UX specifici** (es: disabilitazione parallax su connessioni lente per Salient)

#### Cosa è stato rimosso:
- ❌ Esclusioni script (ora in `ThemeAssetConfiguration`)
- ❌ HTTP/2 push font (ora in `ThemeAssetConfiguration`)
- ❌ Gestione dimensioni immagini (già gestita da `ImageOptimizer`)

### 3. Registrazione nel Sistema

#### In `src/Plugin.php`:

**Import aggiunto:**
```php
use FP\PerfSuite\Services\Assets\ThemeAssetConfiguration;
```

**Registrazione nel ServiceContainer:**
```php
// Theme Asset Configuration (gestisce asset specifici per tema/builder)
$container->set(ThemeAssetConfiguration::class, 
    static fn(ServiceContainer $c) => 
        new ThemeAssetConfiguration($c->get(ThemeDetector::class))
);
```

**Boot del servizio:**
```php
$container->get(ThemeAssetConfiguration::class)->register();
```

## 🎯 Vantaggi

### 1. **Separazione delle Responsabilità**
- Asset management in `Services/Assets/`
- Compatibility management in `Services/Compatibility/`
- Ogni modulo ha responsabilità chiare

### 2. **Riusabilità**
- `ThemeAssetConfiguration` può essere usato da qualsiasi servizio che gestisce asset
- Configurazioni centralizzate in un unico punto

### 3. **Manutenibilità**
- Più facile trovare dove aggiungere supporto per un nuovo tema
- Codice più pulito e organizzato

### 4. **Estendibilità**
- Facile aggiungere nuovi temi/builder
- Filtri WordPress per personalizzazioni esterne:
  - `fp_ps_theme_script_exclusions`
  - `fp_ps_theme_image_config`

## 🔄 Flusso di Funzionamento

### Prima (❌ Confuso):
```
CompatibilityFilters
  ├─ Esclusioni script (ASSET!)
  ├─ HTTP/2 push font (ASSET!)
  ├─ Dimensioni immagini (ASSET!)
  ├─ Cache purge (OK)
  └─ Disabilitazione editor (OK)
```

### Dopo (✅ Chiaro):
```
ThemeAssetConfiguration (in Services/Assets/)
  ├─ Esclusioni script
  ├─ HTTP/2 push font
  ├─ Handle critici
  ├─ Config immagini
  └─ AVIF compatibility

CompatibilityFilters (in Services/Compatibility/)
  ├─ Cache purge automatico
  ├─ Disabilitazione ottimizzazioni in editor
  └─ Fix UX specifici tema (parallax, ecc)
```

## 📝 Integrazione con Altri Servizi

### ThirdPartyScriptManager
Usa il filtro `fp_ps_third_party_script_delay` che viene popolato da `ThemeAssetConfiguration`

### Http2ServerPush
Usa il filtro `fp_ps_http2_critical_fonts` che viene popolato da `ThemeAssetConfiguration`

### ImageOptimizer
Può usare `getImageOptimizationConfig()` per configurazioni specifiche tema

## 🧪 Testing

### Nessun Breaking Change
- I filtri WordPress esistenti continuano a funzionare
- La logica è identica, solo riorganizzata
- Tutti i servizi esistenti funzionano normalmente

### Temi Testati
- ✅ Salient + WPBakery
- ✅ Avada + Fusion Builder
- ✅ Divi + Divi Builder
- ✅ Generic + Elementor
- ✅ Generic + Gutenberg

## 📚 Utilizzo per Nuovi Temi

Per aggiungere supporto per un nuovo tema, basta editare `ThemeAssetConfiguration.php`:

```php
// In getScriptExclusionPatterns()
if ($this->detector->isMyNewTheme()) {
    $patterns = array_merge($patterns, [
        'mynewtheme-',
        'mynewtheme-critical-',
    ]);
}

// In addCriticalFonts()
if ($this->detector->isMyNewTheme()) {
    $fonts[] = [
        'url' => $theme_uri . '/fonts/main.woff2',
        'as' => 'font',
        'type' => 'font/woff2',
    ];
}
```

## 🎉 Conclusione

Questa riorganizzazione migliora significativamente l'architettura del plugin:
- ✅ Codice più organizzato
- ✅ Responsabilità più chiare
- ✅ Più facile da estendere
- ✅ Più facile da mantenere
- ✅ Nessun breaking change

---

**Autore**: Francesco Passeri  
**Data**: 2025-10-18  
**Versione Plugin**: 1.3.0+
