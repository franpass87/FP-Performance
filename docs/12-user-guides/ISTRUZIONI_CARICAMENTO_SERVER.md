# 📤 Istruzioni Caricamento File sul Server

## Problema Rilevato

L'errore indica che i nuovi file di ottimizzazione database **non sono presenti sul server**.

```
Service "FP\PerfSuite\Services\DB\DatabaseQueryMonitor" not found.
```

Questo significa che il plugin sul server è **vecchio** e non ha le nuove funzionalità.

---

## ✅ Soluzione: Carica i Nuovi File

### File da Caricare sul Server

Devi caricare questi **4 nuovi file** nella directory del plugin sul server:

1. **DatabaseOptimizer.php** (37 KB)
2. **DatabaseQueryMonitor.php** (10 KB)  
3. **PluginSpecificOptimizer.php** (19 KB)
4. **DatabaseReportService.php** (18 KB)

### Percorso di Destinazione sul Server

```
/homepages/37/d970968572/htdocs/clickandbuilds/VillaDianella/
  wp-content/
    plugins/
      FP-Performance/
        src/
          Services/
            DB/
              ← CARICA I 4 FILE QUI
```

---

## 🔧 Metodo 1: Via FTP/SFTP (Raccomandato)

### Passo 1: Connettiti al Server FTP

Usa un client FTP come:
- **FileZilla** (gratuito, Windows/Mac/Linux)
- **WinSCP** (gratuito, Windows)
- **Cyberduck** (gratuito, Mac)

### Passo 2: Naviga nella Directory

```
/wp-content/plugins/FP-Performance/src/Services/DB/
```

### Passo 3: Carica i File

Dalla tua macchina locale, carica questi file:

**Dalla directory locale:**
```
C:\Users\franc\OneDrive\Desktop\FP-Performance\src\Services\DB\
```

**File da caricare:**
- ✅ `DatabaseOptimizer.php`
- ✅ `DatabaseQueryMonitor.php`
- ✅ `PluginSpecificOptimizer.php`
- ✅ `DatabaseReportService.php`

### Passo 4: Carica Anche i File Aggiornati

Devi sostituire anche questi 3 file già esistenti con le versioni aggiornate:

**File da sovrascrivere:**
1. `/wp-content/plugins/FP-Performance/src/Plugin.php`
2. `/wp-content/plugins/FP-Performance/src/Admin/Pages/Database.php`
3. `/wp-content/plugins/FP-Performance/src/Cli/Commands.php`

---

## 🔧 Metodo 2: Via File Manager cPanel

### Passo 1: Accedi a cPanel

1. Login al tuo hosting
2. Apri **File Manager**

### Passo 2: Naviga alla Directory

```
public_html/wp-content/plugins/FP-Performance/src/Services/DB/
```

### Passo 3: Carica i File

1. Click su **Upload**
2. Seleziona i 4 nuovi file dalla tua macchina
3. Aspetta il completamento dell'upload

### Passo 4: Verifica Permessi

Assicurati che i file abbiano permessi **644**:
- Click destro su file → **Change Permissions**
- Imposta: **644** (rw-r--r--)

---

## 🔧 Metodo 3: Via SSH (Avanzato)

### Se Hai Accesso SSH:

```bash
# Connettiti al server
ssh user@tuoserver.com

# Naviga alla directory
cd /homepages/37/d970968572/htdocs/clickandbuilds/VillaDianella/wp-content/plugins/FP-Performance/src/Services/DB/

# Verifica file esistenti
ls -la

# Carica i nuovi file (usa scp o rsync dal tuo computer)
# Da un altro terminale sul tuo PC:
scp C:\Users\franc\OneDrive\Desktop\FP-Performance\src\Services\DB\Database*.php user@server:/path/to/plugin/src/Services/DB/
scp C:\Users\franc\OneDrive\Desktop\FP-Performance\src\Services\DB\Plugin*.php user@server:/path/to/plugin/src/Services/DB/
```

---

## ✅ Dopo il Caricamento

### 1. Riattiva il Plugin

**Via Admin WordPress:**
```
1. Vai su Plugin > Plugin Installati
2. Disattiva FP Performance
3. Riattiva FP Performance
```

**Via WP-CLI:**
```bash
wp plugin deactivate fp-performance-suite
wp plugin activate fp-performance-suite
```

### 2. Svuota le Cache

```bash
# Cache WordPress
wp cache flush

# Se hai OPcache, riavvia PHP-FPM
service php-fpm restart
```

### 3. Verifica

1. Vai su `FP Performance > Database`
2. **NON dovresti più vedere** l'avviso giallo
3. **Dovresti vedere** il Dashboard Health Score (sezione viola)

---

## 📋 Checklist Caricamento

Prima di procedere, verifica:

- [ ] **File locali esistono?** 
  ```
  C:\Users\franc\OneDrive\Desktop\FP-Performance\src\Services\DB\
  ```
  
- [ ] **Connessione FTP funzionante?**

- [ ] **Percorso server corretto?**
  ```
  /wp-content/plugins/FP-Performance/src/Services/DB/
  ```

- [ ] **File caricati con successo?** (4 nuovi + 3 aggiornati)

- [ ] **Permessi file corretti?** (644)

- [ ] **Plugin riattivato?**

- [ ] **Cache svuotata?**

---

## 🆘 Se i File Non si Caricano

### Problema 1: "Permission Denied"

**Soluzione:**
```bash
# Via SSH, cambia proprietario
chown www-data:www-data *.php

# Oppure imposta permessi più permissivi temporaneamente
chmod 755 /path/to/Services/DB
chmod 644 /path/to/Services/DB/*.php
```

### Problema 2: "Directory Not Found"

**Soluzione:**
1. Verifica che la directory `DB` esista
2. Se non esiste, creala:
   ```bash
   mkdir -p /path/to/plugin/src/Services/DB
   ```

### Problema 3: "File Already Exists"

**Soluzione:**
- Sovrascrivi i file esistenti
- In FileZilla: **conferma overwrite**
- In cPanel: spunta "Overwrite existing files"

---

## 📊 Verifica Caricamento

### Script di Verifica Remoto

Crea un file `verifica-server.php` nella root del plugin:

```php
<?php
// verifica-server.php
$files = [
    'DatabaseOptimizer.php',
    'DatabaseQueryMonitor.php', 
    'PluginSpecificOptimizer.php',
    'DatabaseReportService.php',
];

$basePath = __DIR__ . '/src/Services/DB/';

foreach ($files as $file) {
    $path = $basePath . $file;
    if (file_exists($path)) {
        echo "✅ $file (" . round(filesize($path)/1024, 1) . " KB)\n";
    } else {
        echo "❌ $file - MANCANTE!\n";
    }
}
```

Poi aprilo nel browser:
```
https://tuosito.com/wp-content/plugins/FP-Performance/verifica-server.php
```

---

## 🎯 Riepilogo Rapido

1. **Carica 4 nuovi file** in `src/Services/DB/`
2. **Sovrascrivi 3 file** esistenti (Plugin.php, Database.php, Commands.php)
3. **Riattiva** il plugin
4. **Verifica** che l'errore sia sparito

**Tempo stimato:** 5-10 minuti

---

## 📞 Supporto

Se dopo aver seguito questi passi l'errore persiste:

1. Controlla `debug.log`:
   ```
   /wp-content/debug.log
   ```

2. Verifica file esistenti via FTP

3. Controlla permessi file (devono essere 644)

4. Verifica che PHP possa leggere i file:
   ```php
   <?php
   var_dump(file_exists('/path/to/DatabaseOptimizer.php'));
   var_dump(is_readable('/path/to/DatabaseOptimizer.php'));
   ```

---

**Buon caricamento! 🚀**

*Nota: Dopo il caricamento, le nuove funzionalità avanzate saranno immediatamente disponibili!*

