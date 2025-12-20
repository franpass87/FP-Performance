# 🚨 ISTRUZIONI RIPRISTINO EMERGENZA

## Problema
Dopo aver attivato "Attiva 40 Opzioni Sicure" in Overview, il frontend del sito mostra un errore critico.

## ⚡ SOLUZIONE RAPIDA (2 minuti)

### Opzione 1: Script di Emergenza (Raccomandato)
1. Vai su: `https://tuosito.com/wp-content/plugins/FP-Performance/EMERGENCY-DISABLE-ALL.php`
2. Lo script disabilita automaticamente tutte le ottimizzazioni
3. Verifica che il frontend funzioni
4. **Elimina il file** `EMERGENCY-DISABLE-ALL.php` dopo l'uso

### Opzione 2: Via Database (se lo script non funziona)
Esegui queste query SQL nel database WordPress:

```sql
-- Disabilita Assets (più probabile causa errore)
UPDATE wp_options SET option_value = 'a:1:{s:7:"enabled";b:0;}' WHERE option_name = 'fp_ps_assets';

-- Disabilita HTML Minification
UPDATE wp_options SET option_value = 'a:1:{s:11:"minify_html";b:0;}' WHERE option_name = 'fp_ps_assets';

-- Disabilita Page Cache
UPDATE wp_options SET option_value = 'a:1:{s:7:"enabled";b:0;}' WHERE option_name = 'fp_ps_page_cache_settings';

-- Disabilita Critical Path
UPDATE wp_options SET option_value = 'a:1:{s:7:"enabled";b:0;}' WHERE option_name = 'fp_ps_critical_path_optimization';
```

Poi pulisci la cache:
```bash
wp cache flush
```

### Opzione 3: Via WP-CLI
```bash
wp option update fp_ps_assets '{"enabled":false,"minify_html":false,"minify_css":false,"minify_js":false}'
wp option update fp_ps_page_cache_settings '{"enabled":false}'
wp cache flush
```

## 🔍 IDENTIFICAZIONE DEL PROBLEMA

### 1. Controlla i Log degli Errori
Vai in `wp-content/debug.log` e cerca l'ultimo errore PHP. Cerca:
- `Fatal error`
- `Parse error`
- `Call to undefined function`
- `Class not found`

### 2. Ottimizzazioni Più Probabili da Causare Problemi

#### 🔴 ALTA PROBABILITÀ:
1. **HTML Minification** (`minify_html`)
   - Problema: Conflitti con output buffering di altri plugin
   - Soluzione: Disabilita `fp_ps_assets['minify_html']`

2. **CSS/JS Minification** (`minify_css`, `minify_js`)
   - Problema: CSS/JS malformato che viene rotto dalla minificazione
   - Soluzione: Disabilita `fp_ps_assets['minify_css']` e `fp_ps_assets['minify_js']`

3. **Critical Path Optimization**
   - Problema: Font preload o resource hints che causano conflitti
   - Soluzione: Disabilita `fp_ps_critical_path_optimization['enabled']`

#### 🟡 MEDIA PROBABILITÀ:
4. **Lazy Loading** (`lazy_loading`)
   - Problema: Script che richiedono immagini caricate immediatamente
   - Soluzione: Disabilita `fp_ps_image_optimizer['lazy_loading']`

5. **Mobile Optimizer**
   - Problema: Disabilitazione animazioni che rompe layout
   - Soluzione: Disabilita `fp_ps_mobile_optimizer['enabled']`

## 🛠️ RIPRISTINO GRADUALE

Dopo aver ripristinato il sito:

1. **Attiva una ottimizzazione alla volta**
2. **Testa il frontend** dopo ogni attivazione
3. **Identifica quale causa il problema**
4. **Segnala il problema** con:
   - Quale ottimizzazione causa l'errore
   - Messaggio di errore completo da `debug.log`
   - Versione WordPress e PHP
   - Plugin attivi

## 📋 CHECKLIST RIPRISTINO

- [ ] Script di emergenza eseguito o ottimizzazioni disabilitate manualmente
- [ ] Frontend funzionante verificato
- [ ] Cache pulita (`wp cache flush` o via admin)
- [ ] Log errori controllati (`wp-content/debug.log`)
- [ ] Ottimizzazione problematica identificata
- [ ] File `EMERGENCY-DISABLE-ALL.php` eliminato (se usato)

## 🔧 DEBUG AVANZATO

Se il problema persiste, attiva il debug WordPress:

1. Aggiungi in `wp-config.php`:
```php
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
define('WP_DEBUG_DISPLAY', false);
```

2. Visita il frontend
3. Controlla `wp-content/debug.log` per l'errore completo
4. Condividi l'errore per supporto

## 📞 SUPPORTO

Se il problema persiste dopo aver seguito queste istruzioni:
1. Controlla `wp-content/debug.log` per l'errore completo
2. Elenca le ottimizzazioni attive: `wp option get fp_ps_assets`
3. Elenca i plugin attivi: `wp plugin list --status=active`
4. Apri una issue su GitHub con queste informazioni

---

**Ultimo aggiornamento:** 2025-11-06  
**Versione Plugin:** 1.8.0

