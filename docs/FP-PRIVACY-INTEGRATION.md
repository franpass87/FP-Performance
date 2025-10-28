# Integrazione FP Privacy & Cookie Policy

## Panoramica

FP Performance Suite è stato aggiornato per **escludere automaticamente** il plugin **FP Privacy & Cookie Policy** dalle sue ottimizzazioni quando il banner cookie è attivo.

Questo garantisce che il banner funzioni perfettamente e che il consenso venga salvato correttamente.

## Modifiche Implementate

### 1. **Assets Optimizer** (`src/Services/Assets/Optimizer.php`)

#### Esclusione Globale Quando Banner Attivo

```php
public function register(): void
{
    // ESCLUSIONE AUTOMATICA: FP Privacy & Cookie Policy Plugin
    // Non applicare ottimizzazioni se il consenso non è stato dato
    if ($this->shouldExcludeForPrivacyPlugin()) {
        return; // Banner cookie attivo, disabilita ottimizzazioni aggressive
    }
    
    // ... resto del codice
}
```

**Quando si attiva**:
- Plugin FP Privacy è attivo (`FP_PRIVACY_VERSION` è definito)
- Cookie di consenso NON esiste (`fp_consent_state_id` non trovato)
- Utente non ha ancora dato il consenso

**Cosa fa**:
- **Disabilita** minificazione HTML
- **Disabilita** defer/async JS
- **Disabilita** async CSS
- **Disabilita** combine CSS/JS
- **Mantiene attivi** solo gli hint DNS/preload (sicuri)

#### Esclusione Script Specifici

```php
public function filterScriptTag(string $tag, string $handle, string $src): string
{
    // ESCLUSIONE: Script del plugin FP Privacy & Cookie Policy
    if ($this->isPrivacyPluginAsset($handle, $src)) {
        return $tag; // Non modificare gli script del plugin privacy
    }
    // ... ottimizzazioni normali
}
```

**Asset esclusi**:
- Handle che contengono: `fp-privacy`, `fp_privacy`
- URL che contengono: `FP-Privacy-and-Cookie-Policy`, `fp-privacy-cookie-policy`, `/plugins/fp-privacy`

#### Esclusione CSS Specifici

```php
public function filterStyleTag(string $html, string $handle, string $href, $media): string
{
    // ESCLUSIONE: CSS del plugin FP Privacy & Cookie Policy
    if ($this->isPrivacyPluginAsset($handle, $href)) {
        return $html; // Non modificare i CSS del plugin privacy
    }
    // ... ottimizzazioni normali
}
```

### 2. **HTML Minifier** (`src/Services/Assets/HtmlMinifier.php`)

#### Protezione Banner e Modal

```php
public function minify(string $html): string
{
    // PROTEZIONE: Banner cookie FP Privacy Plugin
    // Proteggi il banner e il modal per evitare interferenze
    
    // 1. Protegge <div id="fp-privacy-banner">
    // 2. Protegge <div id="fp-privacy-modal">
    // 3. Protegge <div data-fp-privacy-banner>
    
    // ... resto della minificazione
}
```

**Elementi protetti**:
- Banner cookie completo
- Modal delle preferenze
- Root container del banner
- Tutti i loro contenuti (nested HTML)

## Funzionamento

### Scenario 1: Utente Nuovo (Nessun Consenso)

```
1. Utente visita il sito
2. FP Performance rileva: cookie `fp_consent_state_id` NON esiste
3. FP Performance si disattiva completamente
4. Banner cookie viene mostrato normalmente
5. Utente clicca "Accetta Tutti"
6. Cookie viene salvato
7. Nella prossima pagina: FP Performance riprende a funzionare
```

### Scenario 2: Utente con Consenso Già Dato

```
1. Utente visita il sito
2. FP Performance rileva: cookie `fp_consent_state_id` ESISTE
3. FP Performance funziona normalmente
4. Banner NON viene mostrato
5. Ottimizzazioni attive:
   - Minificazione HTML ✅
   - Defer/Async JS ✅
   - Async CSS ✅
   - Combine assets ✅
```

### Scenario 3: Assets del Plugin Privacy

Anche con consenso dato, gli asset del plugin Privacy vengono sempre esclusi:

```
✅ Script: `fp-privacy-banner.js` → NON defer/async
✅ CSS: `fp-privacy-banner.css` → NON async
✅ HTML: Banner HTML → NON minificato
```

## Benefici

### 1. **Nessuna Interferenza**
- Banner cookie funziona al 100%
- JavaScript eseguito immediatamente
- CSS caricato subito
- HTML non alterato

### 2. **Persistenza Cookie Garantita**
- Cookie salvato correttamente
- localStorage come backup
- Nessun problema con defer/async

### 3. **Performance Ottimale**
- Dopo il consenso, FP Performance riprende
- Utenti con consenso hanno sito veloce
- Solo il primo caricamento è "lento"

### 4. **Compatibilità GDPR**
- Rispetta le preferenze utente
- Non interferisce con tracking consent
- Supporta Google Consent Mode

## Test

### Test 1: Verifica Esclusione Banner

1. **Pulisci cookie** del browser
2. **Vai al sito**
3. **Apri DevTools** → Network
4. **Verifica**:
   - `fp-privacy-banner.js` caricato **senza** `defer` o `async`
   - `fp-privacy-banner.css` caricato **immediatamente**
   - Banner si apre **istantaneamente**

### Test 2: Verifica Riattivazione Dopo Consenso

1. **Clicca "Accetta Tutti"**
2. **Naviga su un'altra pagina**
3. **Apri DevTools** → Network
4. **Verifica**:
   - Altri JS hanno `defer` o `async` ✅
   - Altri CSS caricati in async ✅
   - FP Performance funziona ✅

### Test 3: Verifica Minificazione HTML

1. **Con banner attivo** (no consenso):
   ```bash
   # Visualizza sorgente pagina
   # Banner HTML deve essere leggibile, con indentazione
   ```

2. **Dopo consenso**:
   ```bash
   # Visualizza sorgente pagina
   # HTML minificato (spazi rimossi)
   # MA banner se presente è protetto
   ```

## Debugging

### Verifica Esclusione Attiva

Nel file di log PHP:

```
[FP-PerfSuite] Privacy plugin active: YES
[FP-PerfSuite] Consent cookie found: NO
[FP-PerfSuite] Optimizations disabled for privacy banner
```

### Verifica Riattivazione

Nel file di log PHP:

```
[FP-PerfSuite] Privacy plugin active: YES
[FP-PerfSuite] Consent cookie found: YES
[FP-PerfSuite] Optimizations enabled normally
```

### Log JavaScript (Console)

```javascript
// Con banner attivo
FP Privacy Debug: Cookie non trovato, banner verrà mostrato

// Dopo consenso
FP Privacy Debug: Cookie impostato: fp_consent_state_id=...
FP Privacy Debug: Consenso salvato anche in localStorage: ...
```

## Configurazione

### Nessuna Configurazione Necessaria!

L'integrazione è **completamente automatica**:
- ✅ Rileva automaticamente il plugin Privacy
- ✅ Esclude automaticamente quando necessario
- ✅ Riattiva automaticamente dopo consenso
- ✅ Protegge automaticamente gli asset

### Opzionale: Disabilitare l'Integrazione

Se per qualche motivo vuoi disabilitare questa integrazione:

```php
// Nel file wp-config.php
define('FP_PERF_DISABLE_PRIVACY_INTEGRATION', true);
```

Poi modifica `Optimizer.php`:

```php
private function shouldExcludeForPrivacyPlugin(): bool
{
    if (defined('FP_PERF_DISABLE_PRIVACY_INTEGRATION') && FP_PERF_DISABLE_PRIVACY_INTEGRATION) {
        return false;
    }
    // ... resto del codice
}
```

## File Modificati

| File | Modifiche |
|------|-----------|
| `src/Services/Assets/Optimizer.php` | Aggiunta esclusione globale e per asset |
| `src/Services/Assets/HtmlMinifier.php` | Aggiunta protezione banner HTML |
| `docs/FP-PRIVACY-INTEGRATION.md` | Documentazione |

## Compatibilità

- ✅ FP Performance Suite `v1.6.0+`
- ✅ FP Privacy & Cookie Policy `v1.0.0+`
- ✅ WordPress `5.8+`
- ✅ PHP `7.4+`

## Note Tecniche

### Cookie Utilizzato

```
Nome: fp_consent_state_id
Formato: CONSENT_ID|REVISION
Esempio: fp_consent_state_id=fpconsent7a8b9c1d2e3f|1
Durata: 180 giorni
```

### Costante Plugin Privacy

```php
FP_PRIVACY_VERSION // Definita dal plugin privacy quando attivo
```

### Priorità Hook

```php
// FP Performance
add_action('template_redirect', [$this, 'startBuffer'], 1);

// FP Privacy carica prima (default priority)
// Quindi il cookie è già disponibile quando FP Performance controlla
```

## Supporto

Per problemi con l'integrazione:

1. Verifica che entrambi i plugin siano attivi
2. Controlla i log PHP per messaggi di debug
3. Controlla la console browser per log JavaScript
4. Pulisci cache (browser + WordPress)
5. Testa in finestra incognito

---

**Versione**: 1.0  
**Data**: 2025-10-28  
**Autore**: Francesco Passeri  
**Testato su**: Chrome 120, Firefox 121, Safari 17

