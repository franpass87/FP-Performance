# 🐛 BUG #28 + #29: jQuery is not defined

**Data:** 5 Novembre 2025, 23:35 CET  
**Severità:** 🟡 MEDIA (non blocca ma impatta UX)  
**Status:** 🔍 IN ANALISI

---

## 📊 PROBLEMA

### **BUG #28: Console Error "jQuery is not defined"**

**Sintomo:**
```
ReferenceError: jQuery is not defined (line 24)
```

**Impatto:**
- Errore visibile in console su TUTTE le pagine admin
- Non blocca caricamento pagina
- MA impedisce funzionamento di script jQuery inline

---

### **BUG #29: One-Click Button NON funziona** ⚠️ CRITICO

**Sintomo:**
- Click su "🎯 Attiva 40 Opzioni Sicure" → **NESSUNA REAZIONE**
- Nessun confirm dialog
- Nessun AJAX call
- Bottone non risponde

**Root Cause:**
L'errore "jQuery is not defined" (BUG #28) impedisce il caricamento del codice jQuery inline che gestisce il click handler.

**Codice Impattato:**
```javascript
$('#fp-ps-apply-all-safe').on('click', function() {
    // Questo NON viene mai eseguito se jQuery non è definito
});
```

**Status:** ⚠️ **BLOCCANTE per feature One-Click**

---

## 🔍 ANALISI TECNICA

### **Situazione Attuale:**

1. **✅ Script jQuery inline GIÀ wrappato correttamente:**
```javascript
(function waitForjQuery() {
    if (typeof jQuery === 'undefined') {
        setTimeout(waitForjQuery, 50);
        return;
    }
    jQuery(document).ready(function($) {
        // Code here...
    });
})();
```

2. **❌ MA errore "jQuery is not defined" avviene PRIMA:**
- Errore sulla "line 24" di HTML generato
- Avviene prima che `waitForjQuery()` venga eseguito
- C'è un altro script che usa jQuery troppo presto

### **Ipotesi:**

**A)** C'è un `<script>` inline PRIMA di `waitForjQuery()` che usa `$` o `jQuery` direttamente

**B)** Uno script enqueued sta cercando di eseguire prima del load di jQuery

**C)** jQuery non è caricato affatto a causa di conflitto/errore

---

## 🔧 DEBUGGING PLAN

### **STEP 1: Trova il colpevole (riga 24)**
```javascript
// Cerca tutti gli script inline senza wrapper
document.querySelectorAll('script').forEach(s => {
    if (s.textContent.includes('jQuery') && !s.textContent.includes('waitForjQuery')) {
        console.log('UNWRAPPED jQuery:', s);
    }
});
```

### **STEP 2: Verifica ordine di caricamento**
```bash
grep -n "wp_enqueue_script.*jquery" src/Admin/*.php
```

### **STEP 3: Fix Options**

**Opzione A: Wrap TUTTI gli script inline**
- Cercare OGNI uso di `$()` o `jQuery()` inline
- Aggiungere `waitForjQuery()` wrapper

**Opzione B: Spostare scripts in file .js**
- Creare `overview-handlers.js`
- Enqueue con dependency su jQuery
- Rimuovere inline scripts

**Opzione C: Force jQuery load PRIMA**
- Modificare `Assets.php`
- Enqueue jQuery con priority alta
- Assicurare che carichi in `<head>`

---

## 📊 PRIORITÀ

**BUG #29 (One-Click non funziona):** 🔴 **ALTA**
- Feature appena implementata è ROTTA
- Impatta user experience
- Fix urgente richiesto

**BUG #28 (jQuery console error):** 🟡 **MEDIA**
- Non blocca funzionamento generale
- MA rende debugging difficile
- Dovrebbe essere fixato comunque

---

## 🎯 NEXT STEPS

1. ⏭️ Identificare script sulla "riga 24"
2. ⏭️ Applicare fix appropriato
3. ⏭️ Verificare One-Click funziona
4. ⏭️ Test su tutte le pagine

---

**Status:** 📝 DOCUMENTATO, DA FIXARE

