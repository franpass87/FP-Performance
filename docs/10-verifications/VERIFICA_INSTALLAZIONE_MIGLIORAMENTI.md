# ✅ Verifica Installazione Miglioramenti Database

## File Copiati e Aggiornati

I seguenti file sono stati sincronizzati tra `fp-performance-suite/src/` e `src/`:

### Nuovi File Creati:
- ✅ `src/Services/DB/DatabaseOptimizer.php` (MIGLIORATO)
- ✅ `src/Services/DB/PluginSpecificOptimizer.php` (NUOVO)
- ✅ `src/Services/DB/DatabaseReportService.php` (NUOVO)
- ✅ `src/Services/DB/DatabaseQueryMonitor.php` (esisteva già)

### File Aggiornati:
- ✅ `src/Plugin.php` (registrazione nuovi servizi)
- ✅ `src/Admin/Pages/Database.php` (nuova interfaccia)
- ✅ `src/Cli/Commands.php` (nuovi comandi WP-CLI)

---

## Come Vedere le Modifiche

### Passo 1: Ricarica il Plugin

**Opzione A - Via Admin WordPress:**
1. Vai su `Plugin > Plugin Installati`
2. **Disattiva** FP Performance Suite
3. **Riattiva** FP Performance Suite

**Opzione B - Via WP-CLI:**
```bash
wp plugin deactivate fp-performance-suite
wp plugin activate fp-performance-suite
```

### Passo 2: Svuota le Cache

**Cache PHP (Opcode Cache):**
Se usi OPcache, svuotala:
```bash
# Via wp-cli
wp cache flush

# Oppure riavvia PHP-FPM
service php8.1-fpm restart
```

**Cache Browser:**
- Apri Admin WordPress in modalità incognito
- Oppure fai `Ctrl+F5` (Windows) o `Cmd+Shift+R` (Mac)

### Passo 3: Verifica

1. **Vai su:** `FP Performance > Database`
2. **Dovresti vedere:**
   - 💯 Dashboard Health Score (sezione viola)
   - 🔬 Analisi Avanzate
   - 🔌 Ottimizzazioni Plugin-Specific
   - 📊 Report & Trend

3. **Via WP-CLI:**
```bash
wp fp-performance db health
wp fp-performance db fragmentation
wp fp-performance db plugin-cleanup
```

---

## Troubleshooting

### ❌ Problema: Errore "Class not found"

**Soluzione:**
1. Verifica che i file siano stati copiati correttamente:
```bash
ls -la src/Services/DB/
```

Dovresti vedere:
- Cleaner.php
- QueryCacheManager.php
- **DatabaseOptimizer.php** ← NUOVO
- **DatabaseQueryMonitor.php**
- **PluginSpecificOptimizer.php** ← NUOVO
- **DatabaseReportService.php** ← NUOVO

2. Se mancano, copia manualmente:
```bash
copy fp-performance-suite\src\Services\DB\*.php src\Services\DB\
```

### ❌ Problema: Pagina Database non carica

**Soluzione:**
1. Controlla errori PHP in `wp-content/debug.log`
2. Attiva debug mode in `wp-config.php`:
```php
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
define('WP_DEBUG_DISPLAY', false);
```

3. Ricarica la pagina e controlla il log

### ❌ Problema: "Fatal error: Cannot redeclare class"

**Soluzione:**
Hai probabilmente file duplicati. Elimina la cache PHP:
```bash
# Per OPcache
php -r "opcache_reset();"

# Oppure riavvia il server web
service apache2 restart
# oppure
service nginx restart
```

### ❌ Problema: Le nuove sezioni non appaiono

**Causa:** Cache del browser o WordPress object cache

**Soluzione:**
```bash
# Svuota tutte le cache
wp cache flush
wp transient delete --all

# Riapri browser in incognito
```

---

## Verifica Rapida - Checklist

Prima di procedere, verifica:

- [ ] **Plugin riattivato?** (disattiva + riattiva)
- [ ] **Cache PHP svuotata?** (OPcache/APC)
- [ ] **Browser ricaricato?** (Ctrl+F5 o incognito)
- [ ] **File copiati?** (verifica con `ls src/Services/DB/`)
- [ ] **Debug attivato?** (se ci sono errori)

---

## Test Funzionamento

### Test 1: Interfaccia Admin
```
1. Vai su FP Performance > Database
2. Cerca sezione "💯 Database Health Score"
3. Se la vedi → ✅ FUNZIONA!
```

### Test 2: WP-CLI
```bash
wp fp-performance db health
```

**Output Atteso:**
```
Running database health check...

=== Database Health Score ===
Score: XX%
Grade: X
Status: ...
```

Se vedi questo → ✅ FUNZIONA!

### Test 3: Health Score
```
1. Nella pagina Database, guarda il punteggio (0-100%)
2. Se vedi un numero → ✅ FUNZIONA!
```

---

## File di Log per Debug

Se qualcosa non funziona, controlla:

1. **WordPress Debug Log:**
```
wp-content/debug.log
```

2. **PHP Error Log:**
```
# Varia per server, tipicamente:
/var/log/php-errors.log
# oppure
/var/log/apache2/error.log
# oppure
/var/log/nginx/error.log
```

3. **Plugin Error Log:**
```
wp-content/uploads/fp-performance-suite/logs/
```

---

## Supporto Rapido

Se dopo aver seguito questi passi non vedi ancora le modifiche:

1. **Ricopia tutti i file:**
```bash
copy /Y fp-performance-suite\src\Services\DB\*.php src\Services\DB\
copy /Y fp-performance-suite\src\Plugin.php src\Plugin.php
copy /Y fp-performance-suite\src\Admin\Pages\Database.php src\Admin\Pages\Database.php
copy /Y fp-performance-suite\src\Cli\Commands.php src\Cli\Commands.php
```

2. **Disattiva e riattiva il plugin**

3. **Riavvia il server web** (se hai accesso):
```bash
service apache2 restart
# oppure
service nginx restart
service php-fpm restart
```

4. **Controlla i permessi dei file:**
```bash
chmod 644 src/Services/DB/*.php
```

---

## Conferma Successo

Quando tutto funziona, dovresti vedere:

✅ Health Score visibile nella pagina Database
✅ Sezioni "Analisi Avanzate" espandibili
✅ Comandi WP-CLI funzionanti
✅ Nessun errore nel debug.log

🎉 **Congratulazioni! L'installazione è completa!**

---

*Ultima verifica: Ottobre 2025*

