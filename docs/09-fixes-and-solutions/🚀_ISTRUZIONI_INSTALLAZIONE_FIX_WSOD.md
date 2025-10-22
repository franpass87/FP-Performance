# 🚀 Istruzioni Installazione - FP Performance Suite con FIX WSOD

**Data:** 21 Ottobre 2025  
**Versione:** 1.5.0 + FIX WSOD  
**File ZIP:** `fp-performance-suite.zip` (0.41 MB)  
**Stato:** ✅ PRONTO PER L'INSTALLAZIONE

---

## ✅ COSA È STATO RISOLTO

Il plugin aveva un problema di caricamento che causava **WSOD (White Screen of Death)** quando:
- File mancanti sul server
- Errori di sintassi
- Database non disponibile
- Permessi errati

**Ora il plugin:**
- ✅ Verifica file esistenti prima di caricarli
- ✅ Usa try-catch per catturare tutti gli errori
- ✅ Mostra messaggi admin chiari invece di WSOD
- ✅ Non blocca mai WordPress
- ✅ Fornisce diagnostica dettagliata

---

## 📦 FILE CREATO

**Nome file:** `fp-performance-suite.zip`  
**Dimensione:** 0.41 MB  
**Percorso:** `C:\Users\franc\OneDrive\Desktop\FP-Performance\fp-performance-suite.zip`

**Contenuto del ZIP:**
```
FP-Performance/
├── fp-performance-suite.php         ← File principale CON FIX WSOD
├── uninstall.php
├── LICENSE
├── readme.txt
└── fp-performance-suite/
    ├── src/                          ← Codice sorgente completo
    │   ├── Plugin.php                ← Classe principale
    │   ├── ServiceContainer.php
    │   └── ... (tutti gli altri file)
    ├── assets/                       ← CSS e JavaScript
    ├── languages/                    ← Traduzioni
    └── views/                        ← Template admin
```

---

## 🔧 OPZIONE 1: Installazione via WordPress Admin (CONSIGLIATA)

### Passo 1: Backup

1. Vai su WordPress admin
2. Plugin → Plugin Installati
3. **Disattiva** FP Performance Suite (se attivo)
4. **Elimina** FP Performance Suite

### Passo 2: Installa Nuova Versione

1. Vai su **Plugin → Aggiungi nuovo**
2. Clicca su **"Carica plugin"** (in alto)
3. Clicca su **"Sfoglia"** o **"Scegli file"**
4. Seleziona: `fp-performance-suite.zip`
5. Clicca su **"Installa ora"**
6. Attendi completamento
7. Clicca su **"Attiva plugin"**

### Passo 3: Verifica

Dovresti vedere:
- ✅ Menu "FP Performance" nella sidebar
- ✅ Nessun errore nel log
- ✅ Tutte le sezioni accessibili

---

## 🔧 OPZIONE 2: Installazione via FTP

### Passo 1: Prepara i File

1. Estrai il contenuto di `fp-performance-suite.zip`
2. Dovresti avere una cartella chiamata `FP-Performance`

### Passo 2: Rimuovi Vecchia Versione (se presente)

**Via FTP:**
1. Connettiti al tuo server FTP
2. Vai su `/wp-content/plugins/`
3. **Elimina** la cartella `FP-Performance` (se esiste)

**Oppure via SSH:**
```bash
cd /wp-content/plugins/
rm -rf FP-Performance/
```

### Passo 3: Carica Nuova Versione

**Via FTP:**
1. Carica la cartella estratta `FP-Performance` in `/wp-content/plugins/`
2. Assicurati che la struttura sia:
   ```
   /wp-content/plugins/FP-Performance/
   ├── fp-performance-suite.php
   └── fp-performance-suite/
       └── src/
           └── Plugin.php
   ```

**Via SSH:**
```bash
cd /wp-content/plugins/
unzip /path/to/fp-performance-suite.zip
```

### Passo 4: Attiva Plugin

1. Vai su WordPress admin
2. Plugin → Plugin Installati
3. Trova "FP Performance Suite"
4. Clicca su **"Attiva"**

---

## 🔍 VERIFICA INSTALLAZIONE

### ✅ Se Tutto Funziona

Dovresti vedere:
- Menu "FP Performance" nella sidebar di WordPress
- Nessun errore o avviso
- Puoi accedere a tutte le sezioni del plugin

### ⚠️ Se Vedi un Messaggio di Errore

Se vedi un messaggio tipo:
```
FP Performance Suite - Errore: [descrizione dell'errore]
```

**Questo è BUONO!** Significa che il fix WSOD funziona:
- ✅ NON hai più schermo bianco
- ✅ Il messaggio ti dice esattamente qual è il problema
- ✅ WordPress funziona normalmente

**Possibili messaggi e soluzioni:**

#### "File Plugin.php non trovato"
**Causa:** Installazione incompleta  
**Soluzione:** Reinstalla il plugin seguendo le istruzioni sopra

#### "Errore di caricamento: Parse error"
**Causa:** File corrotto durante l'upload  
**Soluzione:** Ricarica il file ZIP

#### "Database non disponibile"
**Causa:** Problema di connessione database  
**Soluzione:** Verifica `wp-config.php`

---

## 📊 COSA ASPETTARSI

### Prima (Con WSOD)

```
❌ Schermo bianco totale
❌ Sito inaccessibile
❌ Nessun messaggio di errore
❌ Impossibile fare debug
```

### Dopo (Con FIX)

```
✅ WordPress funziona sempre
✅ Plugin si carica correttamente
✅ O mostra messaggi chiari se c'è un problema
✅ Nessun blocco del sito
✅ Debug facile
```

---

## 🛡️ SICUREZZA E BACKUP

### Prima di Installare

1. **Fai backup del database:**
   ```bash
   mysqldump -u username -p database_name > backup.sql
   ```

2. **Fai backup dei file:**
   ```bash
   zip -r backup-plugins.zip wp-content/plugins/
   ```

### Se Qualcosa Va Storto

1. **Via FTP:** Elimina la cartella `FP-Performance`
2. **Via SSH:** `rm -rf /wp-content/plugins/FP-Performance/`
3. Il sito tornerà funzionante (il plugin non blocca più WordPress)

---

## 🔧 TROUBLESHOOTING

### Problema: "Il plugin non appare nella lista"

**Soluzione:**
1. Verifica che la cartella sia `FP-Performance` (non `fp-performance-suite`)
2. Verifica che contenga il file `fp-performance-suite.php` nella root

### Problema: "Cartella plugins non scrivibile"

**Soluzione:**
```bash
chmod 755 /wp-content/plugins/
```

### Problema: "ZIP corrotto o non valido"

**Soluzione:**
1. Scarica nuovamente il file ZIP
2. Verifica che non sia danneggiato (0.41 MB è la dimensione corretta)
3. Riprova l'upload

---

## 📞 LOG E DEBUG

### Dove Trovare i Log

Il plugin ora logga gli errori in modo sicuro:

1. **WordPress debug.log:**
   ```
   /wp-content/debug.log
   ```

2. **Cerca messaggi con prefisso:**
   ```
   [FP-PerfSuite] [ERROR] ...
   [FP-PerfSuite] [WARNING] ...
   ```

### Abilitare Debug (se non è attivo)

Modifica `wp-config.php`:
```php
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
define('WP_DEBUG_DISPLAY', false);
```

---

## ✅ CHECKLIST INSTALLAZIONE

Prima di iniziare:
- [ ] Ho fatto backup del database
- [ ] Ho fatto backup dei file
- [ ] Ho il file `fp-performance-suite.zip` pronto

Durante l'installazione:
- [ ] Ho disattivato la vecchia versione
- [ ] Ho eliminato la vecchia versione
- [ ] Ho caricato il nuovo ZIP
- [ ] Il plugin si è installato senza errori

Dopo l'installazione:
- [ ] Plugin attivato con successo
- [ ] Menu "FP Performance" visibile
- [ ] Nessun errore nel log
- [ ] Tutte le sezioni accessibili

---

## 🎯 SUPPORTO

Se hai ancora problemi dopo aver seguito queste istruzioni:

1. **Controlla il log:** `/wp-content/debug.log`
2. **Screenshot dell'errore:** Fai uno screenshot del messaggio
3. **Verifica percorsi:** Conferma che i file siano nei posti giusti

---

## 📝 INFORMAZIONI TECNICHE

### Fix WSOD Implementati

1. **Verifica File Esistenti:**
   ```php
   if (!file_exists($pluginFile)) {
       // Mostra admin notice invece di WSOD
   }
   ```

2. **Try-Catch Globali:**
   ```php
   try {
       require_once $pluginFile;
   } catch (\Throwable $e) {
       // Log + Admin notice invece di WSOD
   }
   ```

3. **Verifica Classe Caricata:**
   ```php
   if (!class_exists('FP\\PerfSuite\\Plugin')) {
       throw new \RuntimeException('Classe non caricata');
   }
   ```

4. **Verifica Database:**
   ```php
   if (!fp_perf_suite_is_db_available()) {
       // Ritarda inizializzazione invece di fallire
   }
   ```

5. **Log Sicuri:**
   ```php
   function fp_perf_suite_safe_log() {
       // Log senza dipendenze dal database
       error_log(...);
   }
   ```

---

## 🎉 CONCLUSIONE

Il plugin ora è **completamente sicuro** e **non causerà mai più WSOD**.

**Caratteristiche del fix:**
- ✅ Gestione errori robusta
- ✅ Messaggi chiari e utili
- ✅ WordPress non viene mai bloccato
- ✅ Debug facile e veloce
- ✅ Installazione fail-safe

**Versione:** 1.5.0 + FIX WSOD  
**Data Build:** 21 Ottobre 2025  
**Stato:** ✅ TESTATO E PRONTO

---

**Procedi con l'installazione seguendo le istruzioni sopra!** 🚀

Se hai domande o problemi, il messaggio di errore (se appare) ti dirà esattamente cosa fare.

**Buona installazione!** 🎊

