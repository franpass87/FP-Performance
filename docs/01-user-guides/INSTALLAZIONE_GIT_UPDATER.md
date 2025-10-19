# Guida Installazione FP Performance Suite via Git Updater

## ⚠️ Problema Risolto

L'errore **"Impossibile creare la directory"** durante l'installazione tramite Git Updater è stato risolto con le seguenti modifiche al repository:

### Modifiche Implementate

1. **File `.gitattributes` aggiunto** - Esclude dal download Git:
   - Directory duplicate (`/build`, `/fp-performance-suite`)
   - File di test e documentazione
   - Directory di sviluppo non necessarie
   - Backup e archivi

2. **Header Git Updater aggiunti** al file principale:
   ```php
   * GitHub Plugin URI: https://github.com/franpass87/FP-Performance
   * Primary Branch: main
   ```

3. **File LICENSE** aggiunto nella root del repository

---

## 📋 Prerequisiti

1. **Git Updater** installato e attivo su WordPress
2. Accesso admin al sito WordPress
3. Connessione internet attiva

---

## 🚀 Procedura di Installazione

### Passo 1: Rimuovi la Versione Precedente (se presente)

Se hai già tentato l'installazione e hai ricevuto un errore:

1. Vai su **Plugin → Plugin Installati**
2. Disattiva **FP Performance Suite** (se presente)
3. Elimina **FP Performance Suite**
4. Verifica che la cartella `wp-content/plugins/FP-Performance` sia stata eliminata

### Passo 2: Installa tramite Git Updater

#### Opzione A: Installazione Diretta

1. Vai su **Impostazioni → Git Updater → Installa Plugin**
2. Inserisci l'URI del repository:
   ```
   https://github.com/franpass87/FP-Performance
   ```
3. Seleziona **Branch: main**
4. Clicca su **Installa Plugin**

#### Opzione B: Aggiornamento Automatico

Se il plugin è già installato ma con errori:

1. Vai su **Impostazioni → Git Updater**
2. Clicca su **Aggiorna ora** accanto a **FP Performance Suite**
3. Il plugin verrà aggiornato con la nuova struttura corretta

### Passo 3: Attiva il Plugin

1. Vai su **Plugin → Plugin Installati**
2. Trova **FP Performance Suite**
3. Clicca su **Attiva**

### Passo 4: Verifica l'Installazione

1. Verifica che nella sidebar admin appaia il menu **FP Performance**
2. Vai su **FP Performance → Dashboard**
3. Controlla che tutte le sezioni siano visibili:
   - Cache
   - Ottimizzazioni Assets
   - Database
   - Sicurezza
   - Diagnostica

---

## 🔧 Risoluzione Problemi

### Errore: "Impossibile creare la directory"

**Causa:** Versione vecchia del repository senza `.gitattributes`

**Soluzione:**
1. Assicurati di aver fatto il pull delle ultime modifiche dal repository GitHub
2. Verifica che il file `.gitattributes` sia presente nella root del repository GitHub
3. Riprova l'installazione

### Errore: "Could not create directory"

**Causa:** Permessi insufficienti sulla cartella `wp-content/plugins`

**Soluzione:**
```bash
# Su server Linux/Unix
chmod 755 wp-content/plugins
chown www-data:www-data wp-content/plugins -R

# Su hosting condiviso, contatta il supporto hosting
```

### Il Plugin non appare in Git Updater

**Causa:** Header `GitHub Plugin URI` mancante

**Soluzione:**
1. Verifica che il file `fp-performance-suite.php` contenga:
   ```php
   * GitHub Plugin URI: https://github.com/franpass87/FP-Performance
   ```
2. Se manca, aggiornalo manualmente o fai il pull delle ultime modifiche

### Directory vuota dopo l'installazione

**Causa:** File `.gitattributes` non funzionante

**Soluzione:**
1. Verifica il contenuto del file `.gitattributes` su GitHub
2. Assicurati che contenga le regole `export-ignore`
3. Fai un nuovo commit se necessario

---

## 📊 Struttura Repository Corretta

Dopo le modifiche, Git Updater scaricherà **solo** i file necessari:

```
FP-Performance/
├── assets/           ✅ CSS e JS del plugin
├── languages/        ✅ Traduzioni
├── src/             ✅ Codice sorgente PHP
├── views/           ✅ Template admin
├── fp-performance-suite.php  ✅ File principale
├── uninstall.php    ✅ Script disinstallazione
├── LICENSE          ✅ Licenza GPL-2.0
└── readme.txt       ✅ Readme WordPress
```

**File/Directory ESCLUSI** dal download:
- `/build` - Directory di build non necessaria
- `/fp-performance-suite` - Directory di sviluppo
- `/tests` - Test automatici
- `/docs` - Documentazione estesa
- File `.md` - Documentazione markdown
- File di sviluppo e test

---

## 🎯 Test Post-Installazione

### Test Funzionalità Base

1. **Cache:**
   - Vai su **FP Performance → Cache**
   - Verifica che le opzioni siano visibili
   - Prova ad attivare la cache di pagina

2. **Ottimizzazioni Assets:**
   - Vai su **FP Performance → Assets**
   - Verifica la presenza delle opzioni di minificazione
   - Controlla le esclusioni

3. **Database:**
   - Vai su **FP Performance → Database**
   - Visualizza le statistiche di ottimizzazione

4. **Diagnostica:**
   - Vai su **FP Performance → Diagnostica**
   - Verifica lo stato del sistema

### Test Aggiornamenti

1. Vai su **Impostazioni → Git Updater**
2. Verifica che **FP Performance Suite** sia nell'elenco
3. Controlla la versione attuale (dovrebbe essere 1.2.0 o superiore)
4. Clicca su **Verifica aggiornamenti**

---

## 📞 Supporto

Se riscontri ancora problemi dopo aver seguito questa guida:

1. **Controlla i log di WordPress:**
   ```
   wp-content/debug.log
   ```

2. **Verifica i permessi delle directory:**
   - `wp-content/plugins` deve essere scrivibile
   - `wp-content/upgrade` deve essere scrivibile

3. **Informazioni da fornire per supporto:**
   - Versione WordPress
   - Versione PHP
   - Messaggio di errore completo
   - Screenshot dell'errore (se disponibile)
   - Log di debug (se presente)

---

## 📝 Note Tecniche

### Come funziona `.gitattributes`

Il file `.gitattributes` utilizza la direttiva `export-ignore` per escludere file/directory quando Git crea un archivio del repository. Questo è esattamente ciò che fa Git Updater quando scarica il plugin.

**Esempio di regola:**
```gitattributes
/build export-ignore        # Esclude la directory build
/*.md export-ignore         # Esclude tutti i file .md nella root
/tests export-ignore        # Esclude la directory tests
```

### Vantaggi della Nuova Struttura

1. **Download più veloce** - Solo file necessari (circa 60% in meno)
2. **Installazione più sicura** - Nessun conflitto di directory
3. **Meno spazio occupato** - Plugin più leggero sul server
4. **Aggiornamenti più affidabili** - Struttura pulita e consistente

---

## ✅ Checklist Finale

Prima di considerare l'installazione completata:

- [ ] Plugin visibile in **Plugin → Plugin Installati**
- [ ] Plugin attivato senza errori
- [ ] Menu **FP Performance** visibile nella sidebar admin
- [ ] Tutte le pagine admin accessibili
- [ ] Nessun errore nel log di WordPress
- [ ] Plugin visibile in **Git Updater** per aggiornamenti futuri

---

## 🔄 Aggiornamenti Futuri

Con questa configurazione, gli aggiornamenti futuri saranno **automatici**:

1. Quando viene rilasciata una nuova versione su GitHub
2. Git Updater rileverà l'aggiornamento
3. Potrai aggiornare con un clic dalla dashboard WordPress
4. Nessun conflitto di directory o errori di installazione

---

**Versione Guida:** 1.0  
**Data:** Ottobre 2025  
**Autore:** Francesco Passeri

