# 🚨 DIAGNOSI WSOD - PROBLEMA REALE IDENTIFICATO

## ❌ IL PROBLEMA VERO

**Sul server c'è una versione CORROTTA o VECCHIA del plugin FP Performance Suite!**

### Prova Definitiva

**Log del server dice:**
```
syntax error, unexpected variable "$lighthouseFonts", expecting "function" 
in FontOptimizer.php on line 353
```

**Il codice LOCALE alla linea 353:**
```php
Logger::info('Font optimization settings updated', $updated);  // ← CORRETTO
```

**La variabile `$lighthouseFonts` esiste in:**
- `LighthouseFontOptimizer.php` (file DIVERSO) ← File CORRETTO
- NON in `FontOptimizer.php` ← File SUL SERVER È CORROTTO

### Conclusione

I file sul server sono:
1. **Mescolati** (codice di LighthouseFontOptimizer dentro FontOptimizer)
2. **Vecchi** (versione precedente del plugin)
3. **Corrotti** (upload parziale o interrotto)

---

## 🔍 Altri Errori Secondari

### 1. Git Updater Plugin
```
FP_Git_Updater_Updater::run_plugin_update(): 
Optional parameter $commit_sha declared before required parameter $plugin
```
**Non è nostro plugin** - Questo è il plugin `fp-git-updater` che ha un problema di compatibilità PHP 8.0+

### 2. Problemi Database
```
wpdb deve impostare una connessione ad un database
mysqli_get_server_info(): Argument #1 ($mysql) must be of type mysqli, null given
```
**Causato dal WSOD** - Il database va in timeout perché il sito è crashato

### 3. Tempi AJAX Assurdi
```
Request took 1761070915.9268 seconds (55+ anni)
```
**Bug del sistema di logging** - Calcolo timestamp errato quando il sito è in WSOD

---

## ✅ SOLUZIONE DEFINITIVA

### Opzione A: Upload FTP (Più Semplice) ⭐ CONSIGLIATO

#### Step 1: Prepara il plugin
```bash
# Da Windows PowerShell nella cartella del progetto
.\fix-plugin-sync.sh
```
Questo creerà: `fp-performance-suite.zip`

#### Step 2: Accedi via FTP
- Host: (dal tuo provider)
- User: (dal tuo provider)  
- Password: (dal tuo provider)

#### Step 3: Rimuovi plugin corrotto
```
/wp-content/plugins/FP-Performance/  →  RINOMINA IN:
/wp-content/plugins/FP-Performance-OLD/
```

#### Step 4: Carica nuovo plugin
1. Vai in `/wp-content/plugins/`
2. Carica `fp-performance-suite.zip`
3. Estrai il file ZIP

#### Step 5: Pulisci Cache OPcache
**Opzione 1 - Via cPanel:**
- Vai in "PHP OPcache" o "Cache Manager"
- Clicca "Reset" o "Clear"

**Opzione 2 - Via Script:**
1. Carica `fix-wsod-emergency.php` nella root di WordPress
2. Visita: `https://tuosito.com/fix-wsod-emergency.php?action=clear_cache`

**Opzione 3 - Via SSH:**
```bash
# Disabilita temporaneamente OPcache
echo "opcache.enable=0" > .user.ini
# Aspetta 5 minuti
sleep 300
# Riabilita OPcache
echo "opcache.enable=1" > .user.ini
```

#### Step 6: Testa il sito
Visita: `https://tuosito.com/wp-admin`

---

### Opzione B: Deploy via Git (Per Sviluppatori)

#### Step 1: Verifica branch corretto
```bash
git branch
# Assicurati di essere su 'main'
```

#### Step 2: Pull ultime modifiche
```bash
git pull origin main
```

#### Step 3: Build plugin
```bash
./build-plugin.ps1
```

#### Step 4: Deploy sul server
```bash
# Via Git (se hai accesso SSH)
ssh user@tuosito.com
cd /path/to/wordpress/wp-content/plugins/
rm -rf FP-Performance
git clone https://github.com/tuo-repo/FP-Performance.git
```

#### Step 5: Clear cache
```bash
# Sul server
php -r "if(function_exists('opcache_reset')) opcache_reset();"
```

---

### Opzione C: Via WordPress Admin (Se accessibile)

**NOTA:** Funziona solo se riesci ad accedere a wp-admin

1. **Disattiva il plugin:**
   - Plugin → Plugin Installati
   - Trova "FP Performance Suite"
   - Clicca "Disattiva"

2. **Elimina il plugin:**
   - Clicca "Elimina"
   - Conferma l'eliminazione

3. **Installa versione corretta:**
   - Plugin → Aggiungi nuovo
   - Carica Plugin
   - Seleziona `fp-performance-suite.zip`
   - Clicca "Installa ora"

4. **Attiva il plugin:**
   - Clicca "Attiva Plugin"

---

## 🔧 TROUBLESHOOTING

### Se dopo il fix il sito è ancora in WSOD

#### 1. Verifica che i file siano corretti

Via FTP, controlla:
```
/wp-content/plugins/FP-Performance/src/Services/Assets/
```

Devono esserci 3 FILE SEPARATI:
- ✅ `FontOptimizer.php` (377 righe)
- ✅ `LighthouseFontOptimizer.php` (456 righe)
- ✅ `AutoFontOptimizer.php`

**Se c'è UN SOLO file o le dimensioni sono sbagliate:**
→ I file sono ancora corrotti, riprova l'upload

#### 2. Verifica la cache OPcache

Crea questo file: `/test-opcache.php`
```php
<?php
if (function_exists('opcache_reset')) {
    opcache_reset();
    echo "✅ Cache OPcache pulita!";
} else {
    echo "⚠️ OPcache non disponibile";
}
phpinfo();
?>
```

Visita: `https://tuosito.com/test-opcache.php`

Se vedi ancora la vecchia versione nel phpinfo:
→ Contatta l'hosting per reset manuale OPcache

#### 3. Disabilita TUTTI i plugin

Via database (phpMyAdmin):
```sql
UPDATE wp_options 
SET option_value = '' 
WHERE option_name = 'active_plugins';
```

Se il sito funziona:
→ Uno dei plugin causa conflitto, riattivali uno alla volta

#### 4. Verifica permessi file

Via SSH:
```bash
chmod 755 /wp-content/plugins/FP-Performance
chmod 644 /wp-content/plugins/FP-Performance/fp-performance-suite.php
chmod 644 /wp-content/plugins/FP-Performance/src/Services/Assets/*.php
```

---

## 📊 VERIFICA POST-FIX

Dopo aver risolto, verifica che tutto funzioni:

### 1. Accesso WordPress
```
✅ https://tuosito.com/wp-admin → Carica correttamente
✅ Nessun errore PHP visibile
✅ Plugin attivo e funzionante
```

### 2. Log PHP puliti
Controlla: `/wp-content/debug.log`
```
✅ Nessun errore "syntax error"
✅ Nessun errore "unexpected variable"
✅ Nessun errore "$lighthouseFonts"
```

### 3. OPcache funzionante
```
✅ Script PHP si caricano velocemente
✅ Nessun errore di cache
✅ Modifiche ai file si riflettono subito (dopo clear cache)
```

### 4. Database connesso
```
✅ Nessun errore "wpdb must set a database connection"
✅ Nessun errore "mysqli_get_server_info"
✅ Query database funzionano correttamente
```

---

## 🛡️ PREVENZIONE FUTURA

### 1. Usa sempre Git per il deploy

**NON caricare mai file manualmente via FTP!**

Invece:
```bash
git push origin main
# Poi sul server:
git pull origin main
```

### 2. Automatizza il deploy

Usa GitHub Actions o un deploy script:
```bash
#!/bin/bash
# deploy.sh
ssh server "cd /path/to/plugin && git pull && php opcache-reset.php"
```

### 3. Versiona il plugin

In `fp-performance-suite.php`:
```php
/*
Plugin Name: FP Performance Suite
Version: 1.5.1  ← INCREMENTA AD OGNI MODIFICA
*/
```

### 4. Test in staging prima di produzione

```
Locale → Staging → Produzione
   ↓         ↓          ↓
 Test     Test       Deploy
```

### 5. Monitoring continuo

Installa:
- **Query Monitor** (per debug)
- **Error Log Monitor** (per log PHP)
- **Health Check** (per monitoraggio generale)

---

## 📞 SUPPORTO

### Se nulla funziona

1. **Backup completo del sito**
   ```bash
   wp db export backup.sql
   tar -czf backup-files.tar.gz wp-content/
   ```

2. **Reset completo WordPress**
   - Reinstalla core WordPress
   - Ripristina database
   - Reinstalla plugin uno alla volta

3. **Contatta hosting**
   ```
   Il mio sito ha un WSOD causato da:
   - File plugin corrotti (sintassi PHP errata)
   - Cache OPcache che non si pulisce
   - Possibile problema di deploy/sincronizzazione file
   
   Ho bisogno di:
   - Reset completo cache OPcache per il mio account
   - Verifica integrità file system
   - Controllo log PHP-FPM per errori
   ```

---

## ✅ CHECKLIST FINALE

Prima di considerare risolto:

- [ ] File plugin corretti caricati sul server
- [ ] Cache OPcache pulita completamente
- [ ] Sito accessibile senza errori
- [ ] Plugin FP Performance attivo
- [ ] Log PHP puliti (nessun errore)
- [ ] Database connesso correttamente
- [ ] Frontend funzionante
- [ ] Backend (wp-admin) funzionante
- [ ] File temporanei di debug rimossi
- [ ] Backup effettuato
- [ ] Monitoring attivo per prevenire futuri problemi

---

**Creato**: 21 Ottobre 2025  
**Ultimo aggiornamento**: 21 Ottobre 2025  
**Autore**: Francesco Passeri - FP Performance Suite

