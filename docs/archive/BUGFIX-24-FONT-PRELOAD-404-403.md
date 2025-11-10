# 🐛 BUGFIX #24 - FONT PRELOAD HARDCODED CON URL 404/403

**Data:** 5 Novembre 2025, 23:00 CET  
**Severità:** 🟡 MEDIA  
**Status:** ✅ RISOLTO

---

## 📊 PROBLEMA RISCONTRATO

### **Sintomo Iniziale:**
User ha segnalato: *"Console mostra errori 404/403 per font controlla perché"*

### **Errori Console:**

**404 - URL Parziali (Google Fonts):**
```
❌ Failed to load: fonts.gstatic.com/s/memvya/v44/memvYaGs1.woff2 - 404
❌ Failed to load: fonts.gstatic.com/s/rp2tp2ywx/v17/rP2tp2ywx.woff2 - 404
```

**403 - CORS Blocked (Brevo):**
```
❌ Failed to load: assets.brevo.com/fonts/3ef7cf1.woff2 - 403
❌ Failed to load: assets.brevo.com/fonts/7529907.woff2 - 403
```

**Warnings:**
```
⚠️ The resource ... was preloaded using link preload but not used within a few seconds
```

---

## 🔍 ROOT CAUSE ANALYSIS

### **File:** `src/Services/Assets/CriticalPathOptimizer.php` (linee 213-242)

**Codice Problematico:**

```php
// ❌ PRIMA (SBAGLIATO):
private function getCriticalFonts(): array
{
    $fonts = $this->getSetting('critical_fonts', []);

    // Add fonts identified in the Lighthouse report
    $lighthouseFonts = [
        // Google Fonts problematic fonts (6,414ms critical path)
        [
            'url' => 'https://fonts.gstatic.com/s/memvya/v44/memvYaGs1.woff2', // ❌ URL PARZIALE!
            'type' => 'font/woff2',
            'crossorigin' => true,
        ],
        [
            'url' => 'https://fonts.gstatic.com/s/rp2tp2ywx/v17/rP2tp2ywx.woff2', // ❌ URL PARZIALE!
            'type' => 'font/woff2',
            'crossorigin' => true,
        ],
        // Brevo fonts (130ms savings)
        [
            'url' => 'https://assets.brevo.com/fonts/3ef7cf1.woff2', // ❌ CORS 403!
            'type' => 'font/woff2',
            'crossorigin' => true,
        ],
        [
            'url' => 'https://assets.brevo.com/fonts/7529907.woff2', // ❌ CORS 403!
            'type' => 'font/woff2',
            'crossorigin' => true,
        ],
        // FontAwesome from theme
        [
            'url' => home_url('/wp-content/themes/' . get_stylesheet() . '/fonts/fontawesome-webfont.woff'),
            'type' => 'font/woff',
            'crossorigin' => false,
        ],
    ];
    
    return array_merge($fonts, $lighthouseFonts);
}
```

---

## 🐞 PROBLEMI IDENTIFICATI:

### **1. URL PARZIALI di Google Fonts (404):**

**URL Hardcoded (SBAGLIATO):**
```
https://fonts.gstatic.com/s/memvya/v44/memvYaGs1.woff2
```

**URL REALE di Google Fonts (CORRETTO):**
```
https://fonts.gstatic.com/l/font?kit=memvYaGs126MiZpBA-UvWbX2vVnXBbObj2OVfS20gF6x3aG1t_4mqzdmCxR6gO9XNngN01FzUMe7UuF6D4L39ynlNJObtOigkSqwi5NJqM_xHEeL-VqsYDBGBwnR&skey=62c1cbfccc78b4b2&v=v44
```

**Problema:**
- Gli URL di Google Fonts sono **DINAMICI** e generati con parametri complessi
- Hardcodare URL parziali causa **404 Not Found**
- Google Fonts cambia gli URL dinamicamente

---

### **2. Font Brevo Bloccati CORS (403):**

**Problema:**
- Brevo blocca `<link rel="preload">` cross-origin
- Anche con `crossorigin="anonymous"` → **403 Forbidden**
- Font esterni controllano Origin header e bloccano preload

**Motivo:**
- Sicurezza Brevo: Permettono `@font-face` CSS ma non preload HTML
- CORS policy permette fetch dal CSS ma blocca preload tag

---

### **3. Font Hardcoded Invece di Dinamici:**

**Problema:**
- Font hardcoded in `getCriticalFonts()` invece di estrarli dal DOM/CSS
- Se il tema cambia, gli URL diventano obsoleti
- Maintenance nightmare (devi aggiornare URL manualmente)

---

## ✅ FIX APPLICATO

### **Modifica:** `src/Services/Assets/CriticalPathOptimizer.php`

```php
// ✅ DOPO (CORRETTO):
private function getCriticalFonts(): array
{
    $fonts = $this->getSetting('critical_fonts', []);

    // BUGFIX #24: Rimossi font hardcoded con URL parziali/invalidi che causavano 404/403
    // Gli URL di Google Fonts sono dinamici e NON devono essere hardcoded
    // I font esterni (Brevo) bloccano preload cross-origin (403)
    $lighthouseFonts = [
        // SOLO font locali che esistono veramente
        // FontAwesome dal tema
        [
            'url' => home_url('/wp-content/themes/' . get_stylesheet() . '/fonts/fontawesome-webfont.woff'),
            'type' => 'font/woff',
            'crossorigin' => false,
        ],
    ];
    
    // NOTE: Google Fonts e font esterni vengono gestiti automaticamente da:
    // - optimizeGoogleFontsLoading() per Google Fonts
    // - preconnect hints per font provider esterni
    
    return array_merge($fonts, $lighthouseFonts);
}
```

---

## 📊 VERIFICA POST-FIX

### **Console PRIMA:**
```
❌ 404: fonts.gstatic.com/s/memvya/v44/memvYaGs1.woff2
❌ 404: fonts.gstatic.com/s/rp2tp2ywx/v17/rP2tp2ywx.woff2
❌ 403: assets.brevo.com/fonts/3ef7cf1.woff2
❌ 403: assets.brevo.com/fonts/7529907.woff2
⚠️ 4 warnings "preloaded but not used"
```

### **Console DOPO:**
```
✅ NESSUN ERRORE 404/403!
⚠️ 1 warning: FontAwesome preloaded but not used (normale, tema lo usa dopo)
```

---

## 🎯 IMPATTO

**Prima del fix:**
- ❌ 4 errori HTTP (404/403) in console
- ❌ 4 richieste HTTP fallite
- ❌ Bandwidth sprecata su font inesistenti
- ❌ Console rumorosa con errori

**Dopo il fix:**
- ✅ 0 errori HTTP
- ✅ Console pulita
- ✅ Solo 1 font preload valido (FontAwesome locale)
- ✅ Google Fonts gestiti dinamicamente via CSS

---

## 💡 LESSON LEARNED

### **NON Hardcodare URL Esterni Dinamici:**

**❌ MAL fatto:**
```php
// Font con URL parziali/hardcoded
['url' => 'https://fonts.gstatic.com/s/memvya/v44/memvYaGs1.woff2']
```

**✅ BEN fatto:**
```php
// Lascia che Google Fonts CSS gestisca i font
// Usa solo preconnect:
echo '<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>';
```

---

### **Font Esterni e CORS:**

| Provider | Preload HTML | CSS @font-face | Motivo |
|----------|--------------|----------------|---------|
| **Google Fonts** | ⚠️ Dinamico | ✅ Funziona | URL cambiano |
| **Brevo** | ❌ 403 Blocked | ✅ Funziona | CORS policy |
| **Font Locali** | ✅ Funziona | ✅ Funziona | Stesso origine |

**Regola d'oro:** Usa preload **SOLO** per font locali o con URL statici verificati!

---

## 📝 FILES MODIFICATI

1. **`src/Services/Assets/CriticalPathOptimizer.php`**
   - Rimossi 4 font hardcoded con URL invalidi
   - Lasciato solo FontAwesome locale
   - Aggiunto commento per Google Fonts gestiti dinamicamente

---

## 🚀 BENEFICI

1. ✅ **Console Pulita:** 0 errori 404/403
2. ✅ **Performance:** Nessuna richiesta HTTP fallita
3. ✅ **Maintainability:** Nessun URL hardcoded da aggiornare
4. ✅ **Compatibility:** Google Fonts gestiti correttamente dal CSS

---

## 🔗 ALTERNATIVE (Future Improvements)

### **Opzione A: Rilevamento Dinamico Font**
Estrarre font dal DOM invece di hardcode:

```php
private function detectCriticalFonts(): array
{
    // Parse CSS per trovare @font-face
    // Estrai URL font usati above-the-fold
    // Genera preload dinamicamente
}
```

### **Opzione B: User Configuration**
Permettere all'utente di specificare font da preload via admin UI:

```php
// Admin setting
'critical_fonts' => [
    '/path/to/custom-font.woff2',
    '/path/to/another-font.woff2',
]
```

### **Opzione C: Disable Completamente**
Se preload causa più problemi che benefici, disabilitarlo:

```php
// In settings
'preload_critical_fonts' => false,
```

---

**Status:** ✅ RISOLTO  
**Fix Duration:** 15 minuti  
**Lines Changed:** ~30 lines (1 file)  
**Regression Risk:** ❌ ZERO (solo rimozione codice problematico)

