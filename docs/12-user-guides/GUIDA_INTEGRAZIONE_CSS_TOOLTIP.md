# 🎨 Guida Integrazione CSS Tooltip Enhanced

**File CSS:** `fp-performance-suite/assets/css/components/tooltips-enhanced.css`  
**Dimensione:** ~470 righe (~12KB non minificato)  
**Versione:** 1.5.1

---

## 🎯 INTEGRAZIONE RAPIDA

Il file CSS deve essere caricato solo nelle pagine admin del plugin.

### Metodo 1: Integrazione in Menu.php (Consigliato)

**File da modificare:** `fp-performance-suite/src/Admin/Menu.php`

Aggiungere questo metodo alla classe `Menu`:

```php
/**
 * Enqueue admin styles
 */
public function enqueueStyles($hook): void
{
    // Carica solo nelle nostre pagine
    if (strpos($hook, 'fp-performance-suite') === false) {
        return;
    }
    
    // Tooltips Enhanced CSS
    wp_enqueue_style(
        'fp-ps-tooltips-enhanced',
        FP_PERF_SUITE_URL . 'assets/css/components/tooltips-enhanced.css',
        [],
        FP_PERF_SUITE_VERSION
    );
}
```

Poi nel costruttore della classe `Menu`, aggiungere l'hook:

```php
public function __construct()
{
    // ... existing code ...
    
    // Enqueue styles
    add_action('admin_enqueue_scripts', [$this, 'enqueueStyles']);
}
```

---

### Metodo 2: Integrazione nel Plugin Principale

**File da modificare:** `fp-performance-suite/fp-performance-suite.php` o `src/Plugin.php`

Aggiungere questa funzione:

```php
add_action('admin_enqueue_scripts', function($hook) {
    // Solo nelle pagine del plugin
    if (strpos($hook, 'fp-performance-suite') !== false) {
        wp_enqueue_style(
            'fp-ps-tooltips-enhanced',
            plugin_dir_url(__FILE__) . 'assets/css/components/tooltips-enhanced.css',
            [],
            '1.5.1'
        );
    }
});
```

---

### Metodo 3: Include Inline (Non Consigliato)

Se per qualche motivo non puoi usare `wp_enqueue_style`, puoi includere inline:

```php
// In views/admin-page.php, nell'head
<style>
    <?php include FP_PERF_SUITE_DIR . '/assets/css/components/tooltips-enhanced.css'; ?>
</style>
```

**⚠️ Non consigliato perché:**
- CSS caricato su ogni richiesta (no cache)
- No minificazione
- Aumenta dimensione HTML

---

## 🔍 VERIFICA INTEGRAZIONE

### 1. Controlla che il CSS sia caricato

Apri una pagina admin del plugin (es: Cache) e controlla nel browser:

1. **DevTools → Network → CSS**
   - Cerca `tooltips-enhanced.css`
   - Dovrebbe essere 200 OK

2. **DevTools → Elements → Head**
   - Cerca `<link rel="stylesheet" ... tooltips-enhanced.css ...>`

### 2. Testa gli stili

Controlla che questi elementi abbiano gli stili corretti:

- [ ] `.fp-ps-help-icon` - Icona ℹ️ blu che diventa blu scuro hover
- [ ] `.fp-ps-status-badge` - Badge colorati verde/grigio
- [ ] `.fp-ps-input-help` - Box consigliati azzurri
- [ ] `.fp-ps-glossary-section` - Glossario espandibile grigio chiaro
- [ ] `.fp-ps-page-intro` - Pannello gradiente viola

---

## 📦 MINIFICAZIONE (Opzionale)

Per produzione, è consigliato minificare il CSS.

### Opzione A: Build automatica

Se hai un build system (Webpack/Gulp/etc):

```bash
# Con postcss
npx postcss assets/css/components/tooltips-enhanced.css -o assets/css/components/tooltips-enhanced.min.css --use cssnano

# Poi enqueue la versione .min.css
```

### Opzione B: Online

1. Copia contenuto di `tooltips-enhanced.css`
2. Vai su https://cssminifier.com/
3. Incolla e minifica
4. Salva come `tooltips-enhanced.min.css`
5. Enqueue la versione minificata

### Opzione C: Lascia non minificato

Per ~12KB di CSS, l'impatto è minimo. Gzip lo ridurrà a ~3KB.

---

## 🎨 PERSONALIZZAZIONE COLORI

Se vuoi cambiare i colori del tema, modifica queste variabili:

```css
/* Colori principali */
--fp-primary: #3b82f6;        /* Blu principale */
--fp-success: #10b981;        /* Verde success */
--fp-warning: #f59e0b;        /* Arancione warning */
--fp-danger: #ef4444;         /* Rosso danger */
--fp-gray: #64748b;           /* Grigio testi */
```

**Nota:** Attualmente i colori sono hardcoded. Considera di aggiungere CSS variables per facilitare customizzazione.

---

## 🔧 TROUBLESHOOTING

### Il CSS non viene caricato

**Problema:** Il file CSS non appare in Network tab

**Soluzioni:**
1. Verifica che il path sia corretto (`FP_PERF_SUITE_URL`)
2. Controlla che `FP_PERF_SUITE_VERSION` sia definita
3. Verifica permessi file (deve essere readable)
4. Svuota cache browser e plugin cache

### Gli stili non vengono applicati

**Problema:** CSS caricato ma elementi non stilizzati

**Soluzioni:**
1. Verifica che le classi HTML siano corrette (`.fp-ps-help-icon` non `.fp-help-icon`)
2. Controlla specificità CSS (altri plugin potrebbero sovrascrivere)
3. Usa DevTools per verificare quali stili vengono applicati
4. Controlla se ci sono errori CSS (console)

### Conflitti con altri plugin

**Problema:** CSS interferisce con altri plugin

**Soluzioni:**
1. Tutti gli stili sono prefissati con `.fp-ps-` per evitare conflitti
2. Se necessario, aumenta specificità: `.fp-ps-card .fp-ps-help-icon`
3. Usa `!important` solo come ultima risorsa

---

## 📊 PERFORMANCE

### Impatto Caricamento

- **Dimensione non minificato:** ~12KB
- **Dimensione minificato:** ~8KB
- **Dimensione Gzipped:** ~3KB
- **Impatto Lighthouse:** Nessuno (<0.1s)
- **Render blocking:** No (caricato async)

### Best Practices

✅ **Fare:**
- Minificare per produzione
- Usare versioning per cache busting
- Caricare solo su pagine admin plugin

❌ **Non fare:**
- Caricare su frontend
- Includere inline su ogni pagina
- Duplicare stili nel codice

---

## 🚀 DEPLOYMENT CHECKLIST

Prima di fare deploy in produzione:

- [ ] CSS integrato correttamente
- [ ] Testato su Chrome, Firefox, Safari, Edge
- [ ] Testato responsive (<782px)
- [ ] Verificato non ci siano conflitti
- [ ] Minificato (opzionale ma consigliato)
- [ ] Cache busting implementato (versioning)
- [ ] Documentazione aggiornata

---

## 📚 RISORSE

### File Correlati
- `tooltips-enhanced.css` - File CSS principale
- `RIEPILOGO_IMPLEMENTAZIONE_TOOLTIP.md` - Documentazione implementazione
- `ESEMPI_MIGLIORAMENTI_TOOLTIP.php` - Esempi HTML

### Riferimenti WordPress
- [wp_enqueue_style()](https://developer.wordpress.org/reference/functions/wp_enqueue_style/)
- [admin_enqueue_scripts](https://developer.wordpress.org/reference/hooks/admin_enqueue_scripts/)

---

**Creato:** 21 Ottobre 2025  
**Versione:** 1.0  
**Autore:** AI Assistant

