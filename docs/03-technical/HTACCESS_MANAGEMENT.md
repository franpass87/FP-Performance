# 📝 Gestione File .htaccess - Documentazione Completa

**Versione:** 1.3.3+  
**Data:** 19 Ottobre 2025  
**Autore:** Francesco Passeri

---

## 📋 Panoramica

Il plugin **FP Performance Suite** include un sistema completo per la gestione, validazione e riparazione del file `.htaccess` di WordPress. Questa funzionalità è accessibile dalla pagina **FP Performance → Diagnostics**.

---

## 🎯 Funzionalità Principali

### 1. ✅ Validazione Sintassi

**Classe:** `FP\PerfSuite\Utils\Htaccess::validate()`

Valida la sintassi del file `.htaccess` controllando:

- **Tag non bilanciati** (`<>`)
- **Markers BEGIN/END** non bilanciati
- **Sintassi ErrorDocument** non valida
- **Struttura generale** del file

**Esempio di utilizzo:**

```php
$htaccess = new Htaccess(new Fs());
$validation = $htaccess->validate();

if ($validation['valid']) {
    echo "File valido!";
} else {
    foreach ($validation['errors'] as $error) {
        echo "Errore: " . $error;
    }
}
```

**Output:**

```php
[
    'valid' => bool,
    'errors' => array
]
```

---

### 2. 🔧 Riparazione Automatica

**Classe:** `FP\PerfSuite\Utils\Htaccess::repairCommonIssues()`

Ripara automaticamente i problemi comuni:

1. **RewriteEngine mancante** - Aggiunge `RewriteEngine On` se ci sono RewriteRule ma manca l'Engine
2. **Righe duplicate** - Rimuove direttive duplicate
3. **Markers non bilanciati** - Corregge o rimuove markers BEGIN/END non bilanciati

**Caratteristiche:**

- ✅ Crea **backup automatico** prima di modificare
- ✅ Report dettagliato delle **correzioni applicate**
- ✅ Gestione sicura degli **errori**

**Esempio:**

```php
$htaccess = new Htaccess(new Fs());
$result = $htaccess->repairCommonIssues();

if ($result['success']) {
    echo "Riparato! Correzioni: ";
    print_r($result['fixes']);
} else {
    echo "Errori: ";
    print_r($result['errors']);
}
```

**Output:**

```php
[
    'success' => bool,
    'fixes' => array,
    'errors' => array
]
```

---

### 3. 💾 Gestione Backup

#### a) Creazione Backup

**Classe:** `FP\PerfSuite\Utils\Htaccess::backup()`

```php
$htaccess = new Htaccess(new Fs());
$backupPath = $htaccess->backup(ABSPATH . '.htaccess');

if ($backupPath) {
    echo "Backup creato: " . $backupPath;
}
```

**Formato nome backup:** `.htaccess.bak-YYYYMMDDHHmmss`  
**Esempio:** `.htaccess.bak-20251019143022`

#### b) Lista Backup

**Classe:** `FP\PerfSuite\Utils\Htaccess::getBackups()`

```php
$htaccess = new Htaccess(new Fs());
$backups = $htaccess->getBackups();

foreach ($backups as $backup) {
    echo $backup['readable_date'] . " - " . $backup['filename'];
}
```

**Output per ogni backup:**

```php
[
    'path' => string,           // Percorso completo
    'filename' => string,       // Nome file
    'timestamp' => string,      // YYYYMMDDHHmmss
    'date' => DateTime,         // Oggetto DateTime
    'size' => int,              // Dimensione in bytes
    'readable_date' => string   // Data formattata (es: "19/10/2025 14:30:22")
]
```

#### c) Ripristino Backup

**Classe:** `FP\PerfSuite\Utils\Htaccess::restore()`

```php
$htaccess = new Htaccess(new Fs());
$result = $htaccess->restore('/path/to/.htaccess.bak-20251019143022');

if ($result) {
    echo "Backup ripristinato con successo!";
}
```

**⚠️ Nota:** Prima di ripristinare, viene creato un backup del file corrente.

#### d) Eliminazione Backup

**Classe:** `FP\PerfSuite\Utils\Htaccess::deleteBackup()`

```php
$htaccess = new Htaccess(new Fs());
$result = $htaccess->deleteBackup('/path/to/.htaccess.bak-20251019143022');
```

#### e) Gestione Automatica Backup

- **Massimo 3 backup** conservati
- I backup più vecchi vengono **automaticamente eliminati**
- Gestione tramite `pruneBackups()` (metodo privato)

---

### 4. 🔄 Reset a WordPress Standard

**Classe:** `FP\PerfSuite\Utils\Htaccess::resetToWordPressDefault()`

Ripristina il file `.htaccess` alle regole WordPress standard:

```apache
# BEGIN WordPress
RewriteEngine On
RewriteRule .* - [E=HTTP_AUTHORIZATION:%{HTTP:Authorization}]
RewriteBase /
RewriteRule ^index\.php$ - [L]
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule . /index.php [L]
# END WordPress
```

**Utilizzo:**

```php
$htaccess = new Htaccess(new Fs());
$result = $htaccess->resetToWordPressDefault();

if ($result) {
    echo "File .htaccess resettato alle regole WordPress standard";
}
```

**⚠️ ATTENZIONE:** Questa operazione **sovrascrive completamente** il file (ma crea un backup prima).

---

### 5. 📊 Informazioni File

**Classe:** `FP\PerfSuite\Utils\Htaccess::getFileInfo()`

Ottiene informazioni dettagliate sul file `.htaccess`:

```php
$htaccess = new Htaccess(new Fs());
$info = $htaccess->getFileInfo();
```

**Output:**

```php
[
    'exists' => bool,              // File esiste
    'path' => string,              // Percorso completo
    'writable' => bool,            // Scrivibile
    'size' => int,                 // Dimensione in bytes
    'size_formatted' => string,    // Dimensione formattata (es: "2.4 KB")
    'modified' => int|null,        // Timestamp modifica
    'modified_formatted' => string|null, // Data formattata
    'lines' => int,                // Numero righe
    'sections' => array            // Sezioni trovate (es: ['WordPress', 'FP Performance Suite'])
]
```

---

### 6. 🔍 Verifica Sezioni

**Classe:** `FP\PerfSuite\Utils\Htaccess::hasSection()`

Verifica se una sezione specifica è presente nel file:

```php
$htaccess = new Htaccess(new Fs());

if ($htaccess->hasSection('WordPress')) {
    echo "Sezione WordPress trovata!";
}

if ($htaccess->hasSection('FP Performance Suite')) {
    echo "Sezione FP Performance Suite trovata!";
}
```

---

## 🖥️ Interfaccia Admin

### Accesso

Vai su: **Dashboard WordPress → FP Performance → Diagnostics**

Scorri fino alla sezione: **📝 Gestione File .htaccess**

### Funzionalità UI

#### 1. Informazioni File

Visualizza in tempo reale:
- ✅ Percorso file
- ✅ Stato scrivibilità
- ✅ Dimensione
- ✅ Ultima modifica
- ✅ Numero righe
- ✅ Sezioni presenti (con badge colorati)

#### 2. Strumenti di Diagnostica

**Valida Sintassi** 🔍
- Verifica la correttezza del file
- Mostra errori dettagliati se presenti

**Ripara Automaticamente** 🔧
- Corregge problemi comuni
- Mostra report delle correzioni applicate
- Crea backup automatico

#### 3. Gestione Backup

**Tabella Backup:**
- Data e ora backup
- Dimensione file
- Nome file
- Azioni: Ripristina / Elimina

**Azioni disponibili:**
- **🔄 Ripristina** - Ripristina il file da un backup
- **🗑️ Elimina** - Elimina un backup specifico

#### 4. Reset Completo

**⚠️ Zona Pericolosa:**
- Checkbox di conferma richiesta
- Doppia conferma JavaScript
- Reset a regole WordPress standard
- Backup automatico prima del reset

---

## 🔐 Sicurezza

### Backup Automatici

**Ogni modifica al file `.htaccess` crea automaticamente un backup**, incluse:
- ✅ Iniezione regole (`injectRules()`)
- ✅ Rimozione sezioni (`removeSection()`)
- ✅ Riparazione automatica (`repairCommonIssues()`)
- ✅ Reset completo (`resetToWordPressDefault()`)
- ✅ Ripristino da backup (`restore()`)

### Protezioni

1. **Verifica permessi** - Solo utenti con capability `manage_options`
2. **Nonce verification** - Protezione CSRF su tutte le azioni
3. **Conferme multiple** - Per azioni distruttive (reset)
4. **Logging** - Tutte le operazioni vengono registrate

### WordPress Hooks

Il plugin emette eventi hook per integrazioni:

```php
// Quando il file viene aggiornato
do_action('fp_ps_htaccess_updated', $section, $rules);

// Quando una sezione viene rimossa
do_action('fp_ps_htaccess_section_removed', $section);

// Quando viene ripristinato un backup
do_action('fp_ps_htaccess_restored', $backupPath);

// Quando il file viene riparato
do_action('fp_ps_htaccess_repaired', $fixes);

// Quando viene fatto un reset
do_action('fp_ps_htaccess_reset');
```

---

## 📖 Casi d'Uso Comuni

### Caso 1: Sito con errore 500 dopo modifica .htaccess

1. Vai su **FP Performance → Diagnostics**
2. Scorri a **Gestione File .htaccess**
3. Controlla la sezione **Backup Disponibili**
4. Clicca **🔄 Ripristina** sul backup più recente precedente all'errore
5. Verifica che il sito funzioni

### Caso 2: Regole duplicate o markers corrotti

1. Vai su **FP Performance → Diagnostics**
2. Clicca **✓ Valida Sintassi**
3. Se vengono mostrati errori, clicca **🔧 Ripara Automaticamente**
4. Verifica il report delle correzioni applicate
5. Ricontrolla con **✓ Valida Sintassi**

### Caso 3: Reset completo dopo conflitti con altri plugin

1. Vai su **FP Performance → Diagnostics**
2. Scorri a **⚠️ Zona Pericolosa**
3. Spunta la checkbox di conferma
4. Clicca **🔥 Reset Completo .htaccess**
5. Conferma la doppia richiesta
6. Il file viene resettato alle regole WordPress standard
7. Riattiva i permalink da **Impostazioni → Permalink** per rigenerare le regole WordPress

### Caso 4: Verifica regole prima del deploy in produzione

1. Esegui il test script: `wp-content/plugins/fp-performance-suite/test-htaccess-manager.php`
2. Controlla il report completo
3. Verifica sezioni presenti
4. Controlla validazione
5. Analizza eventuali problemi rilevati

---

## 🧪 Testing

### Script di Test

**File:** `test-htaccess-manager.php`

**Esecuzione:**
1. Accedi come amministratore
2. Vai su: `https://tuosito.com/wp-content/plugins/fp-performance-suite/test-htaccess-manager.php`

**Test eseguiti:**
1. ✅ Verifica supporto .htaccess
2. ✅ Informazioni file
3. ✅ Validazione sintassi
4. ✅ Lista backup disponibili
5. ✅ Verifica sezioni specifiche
6. ✅ Anteprima contenuto
7. ✅ Simulazione riparazione (dry run)

---

## 🛠️ API Programmativa

### Esempio Completo

```php
use FP\PerfSuite\Utils\Htaccess;
use FP\PerfSuite\Utils\Fs;

// Inizializza
$htaccess = new Htaccess(new Fs());

// Verifica supporto
if (!$htaccess->isSupported()) {
    die('Server non supporta .htaccess o file non scrivibile');
}

// Ottieni info
$info = $htaccess->getFileInfo();
echo "File: " . $info['path'] . "\n";
echo "Dimensione: " . $info['size_formatted'] . "\n";

// Valida
$validation = $htaccess->validate();
if (!$validation['valid']) {
    echo "Errori trovati:\n";
    foreach ($validation['errors'] as $error) {
        echo "- " . $error . "\n";
    }
    
    // Ripara
    $repair = $htaccess->repairCommonIssues();
    if ($repair['success']) {
        echo "Riparato! Correzioni:\n";
        foreach ($repair['fixes'] as $fix) {
            echo "- " . $fix . "\n";
        }
    }
}

// Lista backup
$backups = $htaccess->getBackups();
echo "Backup disponibili: " . count($backups) . "\n";
foreach ($backups as $backup) {
    echo "- " . $backup['readable_date'] . " (" . size_format($backup['size']) . ")\n";
}

// Verifica sezioni
$sections = ['WordPress', 'FP Performance Suite', 'W3 Total Cache'];
foreach ($sections as $section) {
    $status = $htaccess->hasSection($section) ? 'PRESENTE' : 'ASSENTE';
    echo "{$section}: {$status}\n";
}
```

---

## 📚 Riferimenti

### Metodi Pubblici

| Metodo | Descrizione | Return |
|--------|-------------|--------|
| `isSupported()` | Verifica supporto .htaccess | `bool` |
| `backup($file)` | Crea backup | `string\|null` |
| `injectRules($section, $rules)` | Inietta regole | `bool` |
| `removeSection($section)` | Rimuove sezione | `bool` |
| `hasSection($section)` | Verifica sezione | `bool` |
| `getBackups()` | Lista backup | `array` |
| `restore($backupPath)` | Ripristina backup | `bool` |
| `validate($content = null)` | Valida sintassi | `array` |
| `repairCommonIssues()` | Ripara automaticamente | `array` |
| `resetToWordPressDefault()` | Reset a default WP | `bool` |
| `getFileInfo()` | Info file | `array` |
| `deleteBackup($backupPath)` | Elimina backup | `bool` |

### Costanti

```php
const MAX_BACKUPS = 3;  // Numero massimo di backup conservati
```

---

## 🆘 Troubleshooting

### Il file .htaccess non è scrivibile

**Soluzione:**
```bash
# Via SSH
chmod 644 .htaccess

# Se non funziona
chmod 666 .htaccess

# Dopo le modifiche, ripristina
chmod 644 .htaccess
```

### I backup non vengono creati

**Cause possibili:**
1. Directory root non scrivibile
2. Spazio disco insufficiente
3. Limiti hosting

**Verifica:**
```php
echo is_writable(ABSPATH) ? 'Scrivibile' : 'Non scrivibile';
```

### La riparazione non funziona

**Controlla i log:**
```php
// src/Utils/Logger.php
$logs = Logger::getLogs();
```

### Server Nginx (non Apache)

Se usi **Nginx**, il file `.htaccess` non viene utilizzato. Le configurazioni vanno fatte nel file di configurazione Nginx.

Il plugin rileverà automaticamente che non c'è supporto `.htaccess`.

---

## 📝 Note Finali

- ⚠️ **Testare sempre su ambiente di sviluppo** prima di modificare in produzione
- 💾 **I backup sono limitati a 3** - scarica manualmente quelli importanti
- 🔒 **Permessi consigliati:** `644` per `.htaccess`
- 📊 **Monitoraggio:** Controlla i log in caso di problemi
- 🔄 **Aggiornamenti:** Le nuove versioni del plugin potrebbero aggiungere ulteriori controlli di validazione

---

**Domande o Problemi?**  
Apri una issue su GitHub o contatta il supporto.

---

*Documentazione generata automaticamente il 19 Ottobre 2025*

