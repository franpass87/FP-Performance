# 🚨 Risoluzione WSOD (White Screen of Death)

## Diagnosi dei Problemi

Analizzando i log, sono stati identificati **3 problemi critici**:

### 1. **Cache OPcache Corrotta** ⚠️ CRITICO
```
FATAL ERROR: Zend OPcache can't be temporary enabled
```
La cache PHP OPcache contiene codice vecchio o corrotto.

### 2. **Connessione Database Instabile** ⚠️ CRITICO
```
wpdb deve impostare una connessione ad un database
mysqli_get_server_info(): Argument #1 ($mysql) must be of type mysqli, null given
```
Il database perde la connessione durante le richieste.

### 3. **Tempi AJAX Anomali** ⚠️ MOLTO STRANO
```
Request took 1761070915.9268 seconds (55+ anni!)
```
Problema di timestamp o calcolo del tempo, indica instabilità del server.

---

## ✅ Soluzione Rapida (5 minuti)

### Passo 1: Carica il File di Fix
1. Carica il file `fix-wsod-emergency.php` nella **root di WordPress** via FTP
2. Visita: `https://tuosito.com/fix-wsod-emergency.php` nel browser

### Passo 2: Esegui il Fix Completo
1. Clicca sul pulsante **"🚀 Fix Completo (Consigliato)"**
2. Attendi il completamento delle operazioni
3. Il sistema:
   - Pulirà la cache OPcache
   - Verificherà la connessione al database
   - Disabiliterà tutti i plugin temporaneamente

### Passo 3: Verifica il Sito
1. Prova ad accedere a: `https://tuosito.com/wp-admin`
2. Se funziona:
   - ✅ Vai su **Plugin → Plugin Installati**
   - ✅ Riattiva i plugin **uno alla volta**
   - ✅ Testa il sito dopo ogni attivazione
3. Se NON funziona, vai al **Passo 4**

### Passo 4: Fix Manuale (Solo se necessario)

Se il fix automatico non funziona, esegui questi passaggi **via FTP**:

#### A. Disabilita Tutti i Plugin Manualmente
Rinomina la cartella:
```
/wp-content/plugins  →  /wp-content/plugins-disabled
```

#### B. Abilita WP_DEBUG
Aggiungi in `wp-config.php` prima di `/* That's all, stop editing! */`:
```php
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
define('WP_DEBUG_DISPLAY', false);
```

#### C. Verifica il Database
Contatta il tuo hosting e chiedi di verificare:
- Stato del server MySQL
- Connessioni attive
- Log degli errori MySQL
- Timeout di connessione

---

## 🔧 Soluzioni per Problemi Specifici

### Problema: "OPcache can't be temporary enabled"

**Soluzione 1: Reset OPcache da cPanel/Hosting**
1. Accedi al pannello di controllo hosting
2. Cerca "PHP OPcache" o "Cache Manager"
3. Clicca su "Reset" o "Clear"

**Soluzione 2: Reset tramite .htaccess**
Aggiungi in `.htaccess` nella root:
```apache
<IfModule mod_php7.c>
    php_flag opcache.enable Off
</IfModule>
```

**Soluzione 3: Contatta l'Hosting**
Chiedi di:
- Pulire la cache OPcache per il tuo account
- Riavviare PHP-FPM se disponibile
- Verificare la configurazione OPcache

### Problema: "wpdb must set a database connection"

**Causa**: Timeout o disconnessione MySQL durante le richieste AJAX.

**Soluzione 1: Aumenta Timeout MySQL**
Aggiungi in `wp-config.php`:
```php
define('DB_CHARSET', 'utf8mb4');
define('DB_COLLATE', '');
define('WP_MEMORY_LIMIT', '256M');
ini_set('mysql.connect_timeout', 300);
ini_set('default_socket_timeout', 300);
```

**Soluzione 2: Verifica Credenziali DB**
In `wp-config.php` verifica che siano corrette:
```php
define('DB_NAME', 'nome_database');
define('DB_USER', 'utente_database');
define('DB_PASSWORD', 'password_database');
define('DB_HOST', 'localhost'); // O l'IP fornito dall'hosting
```

**Soluzione 3: Ripara il Database**
1. Aggiungi in `wp-config.php`:
   ```php
   define('WP_ALLOW_REPAIR', true);
   ```
2. Visita: `https://tuosito.com/wp-admin/maint/repair.php`
3. Clicca "Repair Database"
4. **IMPORTANTE**: Rimuovi la riga dopo la riparazione!

---

## 🛡️ Prevenzione Futura

### 1. Monitora le Risorse del Server
Installa un plugin di monitoraggio come:
- Query Monitor
- Server IP & Memory Usage Display

### 2. Configura Limite di Memoria Adeguato
In `wp-config.php`:
```php
define('WP_MEMORY_LIMIT', '256M');
define('WP_MAX_MEMORY_LIMIT', '512M');
```

### 3. Disabilita OPcache per WordPress (Opzionale)
Se i problemi persistono, in `.htaccess`:
```apache
<IfModule mod_php7.c>
    php_flag opcache.enable Off
</IfModule>
```

### 4. Usa un Plugin di Cache Affidabile
- WP Rocket (Premium)
- W3 Total Cache (Gratuito)
- LiteSpeed Cache (Se hai hosting LiteSpeed)

---

## 📞 Quando Contattare l'Hosting

Contatta il supporto hosting SE:

1. ✅ Il fix automatico non funziona
2. ✅ Il sito funziona con i plugin disabilitati ma crasha quando li riattivi
3. ✅ Vedi errori di connessione database ricorrenti
4. ✅ I tempi di esecuzione sono anomali (secondi molto alti)

**Cosa chiedere all'hosting:**
```
Salve,

il mio sito WordPress ha un WSOD causato da:
1. Cache OPcache che sembra corrotta
2. Disconnessioni frequenti del database MySQL
3. Tempi di esecuzione AJAX anomali

Potete verificare:
- Stato del server MySQL e stabilità delle connessioni
- Configurazione OPcache (eventualmente reset)
- Log PHP-FPM per errori
- Timeout di connessione database
- Risorse disponibili (RAM, CPU)

Grazie!
```

---

## 🧪 Test Dopo il Fix

Dopo aver risolto il problema, testa:

1. **Frontend**: Visita la homepage e alcune pagine
2. **Backend**: Accedi a wp-admin e naviga tra le pagine
3. **Plugin**: Riattiva i plugin uno alla volta e testa dopo ognuno
4. **Performance**: Usa [GTmetrix](https://gtmetrix.com) o [PageSpeed Insights](https://pagespeed.web.dev)

---

## 📝 Checklist Completa

- [ ] Caricato `fix-wsod-emergency.php` via FTP
- [ ] Eseguito "Fix Completo" dallo script
- [ ] Verificato accesso a wp-admin
- [ ] Riattivato i plugin uno alla volta
- [ ] Testato il sito (frontend e backend)
- [ ] Eliminato `fix-wsod-emergency.php` per sicurezza
- [ ] (Opzionale) Contattato hosting se problemi persistono
- [ ] Configurato monitoring per prevenire futuri problemi

---

## ⚠️ Note Importanti

1. **Backup**: Prima di fare modifiche, assicurati di avere un backup recente
2. **Sicurezza**: Elimina `fix-wsod-emergency.php` dopo averlo usato
3. **Test**: Testa sempre dopo ogni modifica
4. **Support**: Se non sei sicuro, contatta un professionista WordPress

---

## 📚 Risorse Utili

- [WordPress Debug Log](https://wordpress.org/support/article/debugging-in-wordpress/)
- [Database Repair](https://wordpress.org/support/article/database-repair/)
- [Common WordPress Errors](https://wordpress.org/support/article/common-wordpress-errors/)

---

**Ultima modifica**: 21 Ottobre 2025
**Creato da**: Francesco Passeri - FP Performance Suite

