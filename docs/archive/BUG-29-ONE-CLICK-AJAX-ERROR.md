# 🐛 BUG #29: One-Click - Errore di Comunicazione AJAX

**Data:** 5 Novembre 2025, 23:38 CET  
**Severità:** 🔴 ALTA  
**Status:** 🔍 IN ANALISI

---

## 📊 SINTOMO

**User Report:** "Errore di comunicazione:"

**Cosa succede:**
1. ✅ Click sul bottone "Attiva 40 Opzioni Sicure" funziona
2. ✅ Confirm dialog appare (probabilmente)
3. ✅ AJAX POST call viene inviata a `admin-ajax.php`
4. ❌ **ERRORE di comunicazione** - risposta fallisce

---

## 🔍 EVIDENZE

**Network Log:**
```
[POST] http://fp-development.local/wp-admin/admin-ajax.php
```
→ La chiamata AJAX è stata FATTA (click handler funziona!)

**Console (da verificare):**
- Possibile CORS error
- Possibile timeout
- Possibile errore HTTP (500, 404, etc.)

---

## 🎯 POSSIBILI CAUSE

### **Causa A: CORS Error (MOLTO PROBABILE)**

**Problema:**
- AJAX chiama `http://fp-development.local/wp-admin/admin-ajax.php` (senza porta)
- Ma siamo su `http://fp-development.local:10005` (CON porta)
- Origin mismatch → CORS block

**Fix:**
```javascript
// In Overview.php, usare ajaxurl corretto
$.ajax({
    url: ajaxurl, // WordPress global, include porta
    // OPPURE
    url: fpPerfSuite.ajaxUrl, // Se configurato correttamente
});
```

### **Causa B: AJAX Handler Non Registrato**

**Problema:**
- `SafeOptimizationsAjax` non registrato correttamente
- `wp_ajax_fp_ps_apply_all_safe_optimizations` non esiste

**Verifica:**
```bash
grep -r "wp_ajax_fp_ps_apply_all_safe" src/
```

### **Causa C: Nonce Invalido**

**Problema:**
- Nonce scaduto o non valido
- WordPress rifiuta la richiesta

---

## 🔧 DEBUG PLAN

### **STEP 1: Verifica Console Errors**
Cercare:
- CORS policy errors
- Failed to load resource
- HTTP status code (500, 403, 404)

### **STEP 2: Verifica URL AJAX**
```javascript
console.log('AJAX URL:', fpPerfSuite.ajaxUrl);
// Deve essere: http://fp-development.local:10005/wp-admin/admin-ajax.php
```

### **STEP 3: Verifica Handler Registrato**
```bash
# Cerca registration in Plugin.php
grep -A 5 "SafeOptimizationsAjax" src/Plugin.php
```

### **STEP 4: Test con cURL**
```bash
curl -X POST "http://fp-development.local:10005/wp-admin/admin-ajax.php" \
  -d "action=fp_ps_apply_all_safe_optimizations" \
  -d "nonce=XXXXX"
```

---

## 💡 FIX RAPIDO (Se è CORS)

**File:** `src/Admin/Assets.php`

**Assicurati che `fpPerfSuite.ajaxUrl` includa la porta:**
```php
wp_localize_script('fp-performance-suite-admin', 'fpPerfSuite', [
    'ajaxUrl' => admin_url('admin-ajax.php'), // Questo dovrebbe già includere porta
    'nonce' => wp_create_nonce('fp_ps_nonce'),
]);
```

---

## 🎯 NEXT STEPS

1. ⏭️ Leggere console errors
2. ⏭️ Verificare URL AJAX completo
3. ⏭️ Testare handler con cURL
4. ⏭️ Fix e re-test

---

**Status:** 🔍 DEBUGGING...

