# 📋 CHECKLIST COMPLETA TEST BOTTONI - FP Performance

**Obiettivo:** Testare TUTTI i bottoni di TUTTE le pagine (17 pagine + 15 tab)

---

## ❌ BOTTONI CHE DANNO ERRORE

### **Dashboard (Overview)** - 5 bottoni AJAX
1. ❌ "Applica Ora" - Headers cache → **TIMEOUT (CORS)**
2. ❌ "Applica Ora" - Database overhead → **TIMEOUT (CORS)**
3. ❌ "Applica Ora" - Minificazione HTML → **TIMEOUT (CORS)**
4. ❌ "Applica Ora" - (sezione avvisi) → **TIMEOUT (CORS)**
5. ❌ "Applica Ora" - (sezione raccomandazioni) → **TIMEOUT (CORS)**

**PROBLEMA:** Tutti usano AJAX bloccato da CORS Local

---

## ✅ BOTTONI DA TESTARE (Form Submit - dovrebbero funzionare)

### **1. Cache** - 3 bottoni
- [ ] "Save Page Cache"
- [ ] "Clear Cache"
- [ ] "Save Prefetching Settings"
- [ ] "Save Cache Rules"

### **2. Assets** (4 tab) - ~8 bottoni
#### JavaScript
- [ ] "Save JavaScript Settings"

#### CSS
- [ ] "Save CSS Settings"

#### Fonts
- [ ] "Save Fonts Settings"

#### Third-Party
- [ ] "Save Third-Party Settings"

### **3. Compression** - 1 bottone
- [ ] "Salva Impostazioni" → **GIÀ TESTATO ✅ (era rotto, fixato BUG #6)**

### **4. Media** - 3 bottoni
- [ ] "Save Settings" → **GIÀ TESTATO ✅**
- [ ] "Convert to WebP" (bulk action)
- [ ] "Optimize Images"

### **5. Mobile** - 1 bottone
- [ ] "Save Mobile Settings" → **GIÀ TESTATO ✅**

### **6. Database** - 2 bottoni
- [ ] "Execute Cleanup" → **GIÀ TESTATO ✅ (dry run)**
- [ ] "Optimize Tables"

### **7. CDN** - 1 bottone
- [ ] "Salva Impostazioni CDN" → **GIÀ TESTATO ✅**

### **8. Backend** - 1 bottone
- [ ] "Save Settings" → **GIÀ TESTATO ✅**

### **9. Theme** - 2 bottoni
- [ ] "Save Theme Optimization"
- [ ] "Detect Theme"

### **10. Machine Learning** (5 tab) - ~5 bottoni
- [ ] "Train Model"
- [ ] "Save ML Settings"
- [ ] "Run Predictions"
- [ ] "Detect Anomalies"
- [ ] "Enable Auto-Tuning"

### **11. Intelligence** - 3 bottoni
- [ ] "Aggiorna Dati Ora" → **NUOVO (aggiunto in BUG #5 fix)**
- [ ] "Run Auto Optimization"
- [ ] "Apply Smart Exclusions"

### **12. Exclusions** - 2 bottoni
- [ ] "Save Exclusions"
- [ ] "Auto-Detect Exclusions"

### **13. Monitoring** (3 tab) - ~3 bottoni
- [ ] "Save Monitoring Settings" → **GIÀ TESTATO ✅**
- [ ] "Export Logs"
- [ ] "Clear Diagnostics"

### **14. Security** - 1 bottone
- [ ] "Salva Tutte le Impostazioni" → **GIÀ TESTATO ✅**

### **15. Settings** (3 tab) - ~4 bottoni
- [ ] "Salva Impostazioni" → **GIÀ TESTATO ✅**
- [ ] "Export Settings"
- [ ] "Import Settings"
- [ ] "Reset All"

### **16. AI Config** - 2 bottoni
- [ ] "Inizia Analisi AI Avanzata"
- [ ] "Save AI Config"

---

## 📊 RIEPILOGO

| Tipo Bottone | Quantità | Testati | Funzionanti | Errori |
|--------------|----------|---------|-------------|---------|
| **AJAX (Dashboard)** | 5 | 5 | 0 | 5 (CORS) |
| **Form Submit** | ~35 | 8 | 8 | 0 |
| **Da testare** | ~27 | 0 | ? | ? |

---

## 🔍 PROSSIMI STEP

1. ✅ Testare bottoni form submit (NON AJAX)
2. ⚠️ AJAX bloccati da CORS - da testare in staging
3. 📝 Documentare quali bottoni funzionano/non funzionano

---

## ⚠️ NOTA CORS

I bottoni AJAX sulla Dashboard NON funzionano in Local perché:
- Local usa porta `:10005` 
- WordPress genera URL senza porta
- Browser blocca per CORS policy

**Soluzione:** Testare in staging/produzione senza CORS

