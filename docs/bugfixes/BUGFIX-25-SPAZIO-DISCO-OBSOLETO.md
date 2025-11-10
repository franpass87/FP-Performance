# 🐛 BUGFIX #25 - SPAZIO DISCO MOSTRA DATI OBSOLETI

**Data:** 5 Novembre 2025, 23:04 CET  
**Severità:** 🟡 MEDIA  
**Status:** ✅ RISOLTO

---

## 📊 PROBLEMA RISCONTRATO

### **Sintomo Iniziale:**
User ha chiesto: *"sicuro che questo conteggio sia corretto?"* mostrando screenshot del widget Spazio Disco.

### **Screenshot Utente (Dati Obsoleti):**
- **Usato:** 855.9 GB
- **Libero:** 74.8 GB
- **Totale:** 930.7 GB
- **% Usato:** 92.0%

### **Sistema Reale (PowerShell):**
```powershell
$ Get-PSDrive C
Usato:  867.5 GB
Libero: 63.2 GB
Totale: 930.7 GB
```

### **❌ DISCREPANZA: ~11.6 GB!**

Il widget mostrava dati **vecchi** di ~11.6 GB rispetto al sistema reale!

---

## 🔍 ROOT CAUSE ANALYSIS

### **File:** `src/Services/Monitoring/SystemMonitor.php` (linee 334-340)

**Codice Problematico:**

```php
// ❌ PRIMA (SBAGLIATO):
private function calculateStats(array $data): array
{
    $memoryUsage = array_column($data, 'memory_usage');
    $diskUsage = array_column($data, 'disk_usage');  // Array con tutti i campioni
    $loadAverage = array_column($data, 'load_average');
    
    // ...
    
    return [
        'disk' => [
            'total_gb' => $diskUsage[0]['total_gb'] ?? 0,    // ❌ [0] = PRIMO = PIÙ VECCHIO!
            'free_gb' => $diskUsage[0]['free_gb'] ?? 0,      // ❌ PRIMO elemento
            'used_gb' => $diskUsage[0]['used_gb'] ?? 0,      // ❌ PRIMO elemento
            'usage_percent' => $diskUsage[0]['usage_percent'] ?? 0,  // ❌ PRIMO elemento
        ],
    ];
}
```

**Problema:**

1. `$diskUsage` è un array con **TUTTI i campioni** raccolti (fino a 100 via `MAX_ENTRIES`)
2. Il codice prendeva `$diskUsage[0]` = **PRIMO elemento** = **campione PIÙ VECCHIO**
3. I dati vengono appendati con `$stored[] = $metrics;` (riga 279), quindi:
   - `[0]` = campione più vecchio (7 giorni fa)
   - `[99]` = campione più recente (adesso)

**Risultato:** Widget mostrava dati **di 7 giorni fa** invece di quelli **attuali**!

---

## ✅ FIX APPLICATO

### **Modifica:** `src/Services/Monitoring/SystemMonitor.php`

```php
// ✅ DOPO (CORRETTO):
// BUGFIX #25: Usa l'ULTIMO elemento (più recente) invece del PRIMO (più vecchio)
'disk' => [
    'total_gb' => !empty($diskUsage) ? end($diskUsage)['total_gb'] : 0,
    'free_gb' => !empty($diskUsage) ? end($diskUsage)['free_gb'] : 0,
    'used_gb' => !empty($diskUsage) ? end($diskUsage)['used_gb'] : 0,
    'usage_percent' => !empty($diskUsage) ? end($diskUsage)['usage_percent'] : 0,
],
```

**Cambio Chiave:**
- ❌ `$diskUsage[0]` → Primo elemento (più vecchio)
- ✅ `end($diskUsage)` → **Ultimo elemento (più recente)**

**Nota:** Aggiunto anche check `!empty($diskUsage)` per sicurezza.

---

## 📊 VERIFICA POST-FIX

### **Widget PRIMA (Screenshot):**
```
Usato:  855.9 GB
Libero:  74.8 GB
% Usato: 92.0%
```

### **Widget DOPO (Aggiornato):**
```
Usato:  867.4 GB  ✅
Libero:  63.3 GB  ✅
% Usato: 93.2%    ✅
```

### **Sistema REALE (PowerShell):**
```
Usato:  867.5 GB
Libero:  63.2 GB
```

### **✅ DIFFERENZA: < 0.1 GB!**

La piccola differenza (0.1 GB) è **normale** dovuta a:
- Arrotondamenti decimali
- File temporanei creati/eliminati tra le letture
- Cache PHP

**Verdetto:** Widget ora mostra dati **AGGIORNATI e CORRETTI!** ✅

---

## 🎯 IMPATTO

**Prima del fix:**
- ❌ Widget mostrava dati di **~7 giorni fa**
- ❌ Differenza di **11.6 GB** rispetto al reale
- ❌ Utenti vedevano informazioni **obsolete e ingannevoli**

**Dopo il fix:**
- ✅ Widget mostra dati **attuali** (ultimi raccolti)
- ✅ Differenza < **0.1 GB** (arrotondamento)
- ✅ Informazioni **accurate e affidabili**

---

## 💡 PERCHÉ È SUCCESSO?

### **Logica Array:**

Il metodo `storeMetrics()` (riga 271-287) appende i dati cronologicamente:

```php
private function storeMetrics(array $metrics): void
{
    $stored = get_option(self::OPTION . '_data', []);
    
    $stored[] = $metrics;  // ← APPEND alla fine
    
    // Mantieni solo ultimi MAX_ENTRIES
    if (count($stored) > self::MAX_ENTRIES) {
        $stored = array_slice($stored, -self::MAX_ENTRIES);
    }
    
    update_option(self::OPTION . '_data', $stored, false);
}
```

**Ordine Array:**
```
[0] → Campione più vecchio (es. 7 giorni fa)
[1] → 6 giorni fa
[2] → 5 giorni fa
...
[98] → 2 ore fa
[99] → ADESSO (più recente)
```

Il codice sbagliava prendendo `[0]` invece di `[99]`!

---

## 🚀 ALTRI BUG SIMILI?

**Verificato:** Altri campi (`memory`, `load`) usano **medie** o **max** su TUTTO l'array:

```php
'memory' => [
    'avg_usage_mb' => round(array_sum(...) / $count, 2),  // ✅ Media
    'max_usage_mb' => max(...),                            // ✅ Massimo
],
'load' => [
    'avg_1min' => round(array_sum(...) / $count, 2),      // ✅ Media
    'max_1min' => max(...),                                // ✅ Massimo
],
```

Solo `disk` aveva il bug perché voleva il **valore attuale** (non media/max) ma prendeva quello **sbagliato**!

---

## 📝 FILES MODIFICATI

1. **`src/Services/Monitoring/SystemMonitor.php`**
   - Linee 334-340: Cambiato da `$diskUsage[0]` a `end($diskUsage)`
   - Aggiunto check `!empty($diskUsage)` per sicurezza

---

## 🎯 TESTING CHECKLIST

- [x] Verificato widget mostra dati corretti (867.4 GB vs 867.5 GB reale)
- [x] Verificato differenza < 0.1 GB (arrotondamento normale)
- [x] Verificato percentuale corretta (93.2%)
- [x] Verificato nessun PHP error
- [x] Confrontato con sistema reale (PowerShell)

---

## 💡 LESSON LEARNED

**Quando lavori con array temporali:**

1. ✅ **SEMPRE chiaro quale elemento serve:**
   - Primo (`[0]`) = più vecchio
   - Ultimo (`end()` o `[count-1]`) = più recente
   
2. ✅ **Documenta l'ordine:**
   ```php
   // Array ordinato cronologicamente: [0] = oldest, [last] = newest
   ```

3. ✅ **Usa nomi chiari:**
   ```php
   $latestDisk = end($diskUsage);  // ✅ CHIARO
   $oldestDisk = $diskUsage[0];    // ✅ CHIARO
   $disk = $diskUsage[0];          // ❌ AMBIGUO!
   ```

---

**Status:** ✅ RISOLTO  
**Fix Duration:** 10 minuti  
**Lines Changed:** ~6 lines (1 file)  
**Regression Risk:** ❌ ZERO (fix corretto e sicuro)

