# 🚀 Istruzioni Deploy Urgente - Fix Compressione

## Problema
Il bug della pagina bianca su `admin-post.php` quando la compressione è attiva.

## Soluzione Veloce (5 minuti)

### Opzione 1: Carica Solo il File Modificato (VELOCE)

1. **Connettiti al server via SFTP/FTP** (usa FileZilla, WinSCP, o il pannello hosting)

2. **Vai alla directory del plugin**:
   ```
   /wp-content/plugins/fp-performance-suite/src/Services/Compression/
   ```

3. **Sostituisci il file**:
   - Scarica backup del file esistente `CompressionManager.php`
   - Carica il nuovo file da: `fp-performance-suite/src/Services/Compression/CompressionManager.php`

4. **Nel pannello WordPress**:
   - Vai su **FP Performance → Advanced** (o dove hai la compressione)
   - **Disabilita** la compressione
   - **Riabilita** la compressione
   - Questo forza la riscrittura del file `.htaccess` con le nuove regole

5. **Testa**:
   - Prova a salvare qualsiasi impostazione admin
   - Verifica che non ci siano più pagine bianche

---

### Opzione 2: Carica Tutto il Plugin Aggiornato

1. **Crea il file ZIP manualmente**:
   - Vai nella cartella `fp-performance-suite/`
   - Seleziona tutti i file TRANNE:
     - `.git`, `.github`, `tests`, `docs`, `build`, `examples`, `tools`, `bin`
     - File `*.md`, `*.sh`, `composer.lock`, `phpunit.xml.dist`, etc.
   - Crea ZIP chiamato `fp-performance-suite.zip`

2. **Upload su WordPress**:
   - Vai su **Plugin → Aggiungi Nuovo → Carica Plugin**
   - Carica il file ZIP
   - Clicca "Sostituisci il plugin attuale con questo"
   - Attiva se necessario

3. **Riattiva la compressione** come nell'Opzione 1

---

### Opzione 3: Via WP-CLI (se disponibile)

```bash
# Connettiti via SSH al server
ssh utente@tuo-server.com

# Vai nella directory WordPress
cd /path/to/wordpress

# Disattiva il plugin
wp plugin deactivate fp-performance-suite

# Sostituisci il file
# (caricalo prima via SFTP nella home directory)
cp ~/CompressionManager.php wp-content/plugins/fp-performance-suite/src/Services/Compression/

# Riattiva
wp plugin activate fp-performance-suite

# Opzionale: cancella cache
wp cache flush
```

---

## ✅ Verifica che Funzioni

Dopo il deploy, testa:

```bash
# 1. Controlla che il file sia stato aggiornato
# Cerca la riga "Escludi endpoint admin critici"
grep -n "Escludi endpoint admin critici" wp-content/plugins/fp-performance-suite/src/Services/Compression/CompressionManager.php

# 2. Verifica il file .htaccess (dopo aver riabilitato la compressione)
grep -A5 "FilesMatch.*admin-post" .htaccess

# Dovresti vedere:
# <FilesMatch "(admin-post\.php|admin-ajax\.php|upload\.php)$">
#     <IfModule mod_deflate.c>
#         SetEnv no-gzip 1
#     </IfModule>
# ...
```

### Test Manuale

1. ✅ Vai su qualsiasi pagina di impostazioni admin
2. ✅ Modifica e salva le impostazioni
3. ✅ Verifica che NON compaia più la pagina bianca
4. ✅ Controlla che il salvataggio abbia successo

---

## 🔄 Se Usi Git per Deploy

```bash
# Sul tuo PC
cd fp-performance-suite
git add src/Services/Compression/CompressionManager.php CHANGELOG.md
git commit -m "Fix: Escludi admin-post.php dalla compressione per prevenire pagina bianca"
git push origin main

# Sul server (se hai deploy automatico o pull manuale)
cd /path/to/wordpress/wp-content/plugins/fp-performance-suite
git pull origin main
```

---

## ⚠️ Note Importanti

1. **Backup**: Fai sempre backup prima di modificare file
2. **Cache**: Pulisci la cache dopo le modifiche
3. **Test**: Testa sempre in staging prima di produzione (se possibile)
4. **Compressione**: DEVI disabilitare e riabilitare per aggiornare `.htaccess`

---

## 🆘 Se Qualcosa Va Storto

### Ripristino Rapido

1. **Disabilita la compressione** dal pannello WordPress
2. **Riattiva il file backup** di `CompressionManager.php`
3. Oppure **disattiva completamente il plugin** da WordPress

### Contatto

Se hai problemi, condividi:
- Versione WordPress
- Versione PHP
- Log errori (in `wp-content/debug.log` se WP_DEBUG è attivo)
- Contenuto del file `.htaccess`

