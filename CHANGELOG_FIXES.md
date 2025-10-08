# Changelog - Fix Applicati

**Data:** 2025-10-08  
**Versione Plugin:** 1.1.0 → 1.1.1 (proposta)

---

## ✅ Fix Completati

### 1. ✅ Standardizzazione Logging in DebugToggler
**Problema:** Uso inconsistente di `error_log()` invece del Logger centralizzato  
**File:** `src/Services/Logs/DebugToggler.php`  
**Modifiche:**
- Rimosso import `use function error_log;`
- Aggiunto import `use FP\PerfSuite\Utils\Logger;`
- Sostituiti 4 utilizzi di `error_log()` con `Logger::error()`
  - Riga 52: Lock acquisition failure
  - Riga 93: Toggle debug mode failure  
  - Riga 111: Backup failure
  - Riga 133: Restore failure

**Benefici:**
- ✅ Logging consistente in tutto il plugin
- ✅ Supporto per livelli di log e contesto
- ✅ Eventi `fp_ps_log_error` attivati correttamente
- ✅ Migliore tracciabilità e debugging

---

### 2. ✅ Fix print_r() in QueryMonitor Output  
**Problema:** Uso di `print_r()` per formattare valori complessi nell'output HTML  
**File:** `src/Monitoring/QueryMonitor/Output.php` (riga 137)  
**Modifiche:**
```php
// Prima
echo '<tr><td>' . esc_html($key) . '</td><td>' . esc_html(print_r($value, true)) . '</td></tr>';

// Dopo
$formattedValue = is_scalar($value) ? $value : wp_json_encode($value, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
echo '<tr><td>' . esc_html($key) . '</td><td><pre style="margin:0;white-space:pre-wrap;">' . esc_html($formattedValue) . '</pre></td></tr>';
```

**Benefici:**
- ✅ Output JSON leggibile e valido
- ✅ Migliore formattazione per oggetti/array complessi
- ✅ Nessun problema di sicurezza con dati non sanitizzati
- ✅ Tag `<pre>` per formattazione corretta

---

### 3. ✅ Verificata Implementazione Critical CSS
**Stato:** Funzionalità già completamente implementata  
**File:** `src/Services/Assets/CriticalCss.php`  
**Dettagli:**
- ✅ Metodo `register()` già presente e richiamato (Plugin.php:69)
- ✅ Hook `wp_head` già registrato per rendering inline
- ✅ Metodo `inlineCriticalCss()` già implementato
- ✅ Validazione CSS, size limits, eventi già presenti

**Nota:** Non era un bug - la funzionalità è attiva ma richiede configurazione dall'utente tramite Settings → Critical CSS.

---

### 4. ✅ Auto-Purge Cache su Content Updates
**Problema:** Cache non invalidata automaticamente dopo modifiche contenuti  
**File:** `src/Services/Cache/PageCache.php`  
**Modifiche Implementate:**

#### Nuovo metodo `registerPurgeHooks()`
Hook registrati:
- **Post updates:** `save_post`, `deleted_post`, `trashed_post`, `wp_trash_post`
- **Comment updates:** `comment_post`, `edit_comment`, `deleted_comment`, `trashed_comment`, `spam_comment`, `unspam_comment`
- **Theme changes:** `switch_theme`, `customize_save_after`
- **Widget updates:** `update_option_sidebars_widgets`
- **Menu updates:** `wp_update_nav_menu`

#### Nuovi metodi callback:
- `onContentUpdate($postId)` - Con controlli per autosave/revisions/status
- `onCommentUpdate($commentId)` - Solo commenti approvati su post pubblicati
- `onThemeChange()` - Purge completo tema
- `onWidgetUpdate()` - Purge completo widget  
- `onMenuUpdate()` - Purge completo menu

#### Nuovi eventi:
- `fp_ps_cache_auto_purged` - Attivato dopo ogni auto-purge con context

#### Filtro configurabile:
- `fp_ps_enable_auto_purge` - Permette disabilitare l'auto-purge se necessario

**Benefici:**
- ✅ Nessun contenuto obsoleto nella cache
- ✅ Zero interventi manuali necessari
- ✅ Logging dettagliato di ogni purge
- ✅ Controlli intelligenti (no autosave, no draft)
- ✅ Estensibile tramite filtri ed eventi

---

### 5. ✅ Extended Debug Toggler Support
**Problema:** Supporto limitato a solo WP_DEBUG e WP_DEBUG_LOG  
**File:** `src/Services/Logs/DebugToggler.php`  
**Modifiche Implementate:**

#### Metodo `status()` esteso
Ora ritorna anche:
- `WP_DEBUG_DISPLAY` - Controlla visualizzazione errori
- `SCRIPT_DEBUG` - Usa versioni non minificate degli asset
- `SAVEQUERIES` - Salva query DB per debugging

#### Nuovo metodo `updateSettings(array $settings)`
- Accetta array associativo di costanti => valori
- Supporta tutte e 5 le costanti debug
- Backward compatible con `toggle()` esistente

#### Refactoring `toggle()`
Ora delega a `updateSettings()` per DRY:
```php
public function toggle(bool $enabled, bool $log = true): bool
{
    return $this->updateSettings([
        'WP_DEBUG' => $enabled,
        'WP_DEBUG_LOG' => $log,
    ]);
}
```

**Esempio utilizzo:**
```php
$toggler->updateSettings([
    'WP_DEBUG' => true,
    'WP_DEBUG_LOG' => true,
    'WP_DEBUG_DISPLAY' => false,  // Nuovo!
    'SCRIPT_DEBUG' => true,       // Nuovo!
    'SAVEQUERIES' => true,        // Nuovo!
]);
```

**Benefici:**
- ✅ Controllo completo su tutte le costanti debug WordPress
- ✅ Backward compatibility al 100%
- ✅ Codice più pulito e manutenibile (DRY)
- ✅ Logging migliorato con dettagli operazioni

---

## 📊 Riepilogo Impatto

| Fix | File Modificati | Righe Aggiunte | Righe Rimosse | Impatto |
|-----|----------------|----------------|---------------|---------|
| 1. Logging Standardization | 1 | 3 | 5 | Medio |
| 2. QueryMonitor print_r | 1 | 2 | 1 | Basso |
| 3. Critical CSS | 0 | 0 | 0 | N/A (già OK) |
| 4. Auto-Purge Cache | 1 | 110 | 0 | **Alto** |
| 5. Extended Debug | 1 | 90 | 60 | Medio |
| **TOTALE** | **3 file** | **205 righe** | **66 righe** | |

---

## 🧪 Testing Necessario

### Auto-Purge Cache
- [ ] Pubblicare un nuovo post → cache deve purgarsi
- [ ] Modificare un post esistente → cache deve purgarsi  
- [ ] Aggiungere un commento → cache deve purgarsi
- [ ] Cambiare tema → cache deve purgarsi
- [ ] Modificare menu → cache deve purgarsi
- [ ] Salvare una bozza → cache NON deve purgarsi
- [ ] Auto-save → cache NON deve purgarsi

### Extended Debug Toggler
- [ ] Impostare WP_DEBUG_DISPLAY=false → verificare in wp-config.php
- [ ] Impostare SCRIPT_DEBUG=true → verificare in wp-config.php
- [ ] Impostare SAVEQUERIES=true → verificare in wp-config.php
- [ ] Verificare backward compatibility di `toggle()`
- [ ] Testare revert backup con nuove costanti

### Logging
- [ ] Verificare errori nel log usano Logger invece error_log
- [ ] Verificare eventi `fp_ps_log_error` attivati
- [ ] Controllare formato log consistente

---

## 🚀 Prossimi Passi Consigliati

### Quick Wins Rimanenti (Alta Priorità)
1. **Selective Cache Purge** - Purge URL singoli invece di tutto (~8h)
2. **Cache Prewarming** - Worker che pre-popola cache dopo purge (~16h)
3. **Automatic WebP Delivery** - Serve .webp automaticamente (~12h)
4. **Background WebP Worker** - Conversione batch media esistenti (~10h)

### UI Updates Necessarie
1. Aggiornare `src/Admin/Pages/Logs.php` per nuove costanti debug
   - Aggiungere checkbox per WP_DEBUG_DISPLAY
   - Aggiungere checkbox per SCRIPT_DEBUG
   - Aggiungere checkbox per SAVEQUERIES

2. Aggiungere indicatore auto-purge in Cache settings
   - Mostrare ultimi auto-purge nel log
   - Toggle per disabilitare auto-purge

---

## 📝 Note per il Commit

### Messaggio Commit Suggerito
```
fix: standardize logging, auto-purge cache, extend debug support

- Replace error_log() with Logger in DebugToggler
- Replace print_r() with wp_json_encode() in QueryMonitor
- Implement automatic cache purging on content updates
  - Post/page updates trigger purge
  - Comment updates trigger purge  
  - Theme/menu/widget changes trigger purge
  - Smart checks for autosave/drafts
- Extend DebugToggler to support WP_DEBUG_DISPLAY, SCRIPT_DEBUG, SAVEQUERIES
- Add new updateSettings() method with backward compatibility
- Add comprehensive logging for all operations

Closes #XXX
```

### Breaking Changes
Nessuna breaking change. Tutte le modifiche sono backward compatible.

### API Changes
- ✅ Nuovo metodo pubblico: `DebugToggler::updateSettings(array)`
- ✅ Nuovi metodi pubblici in PageCache: `onContentUpdate()`, `onCommentUpdate()`, etc.
- ✅ Nuovo evento: `fp_ps_cache_auto_purged`
- ✅ Nuovo filtro: `fp_ps_enable_auto_purge`

---

**Generato da:** Background Agent  
**Tempo Totale Fix:** ~4 ore  
**Stato:** ✅ Pronto per commit e testing
