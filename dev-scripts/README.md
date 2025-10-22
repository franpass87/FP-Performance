# 🛠️ Dev Scripts

Questa cartella contiene script di sviluppo, test e debug utilizzati durante lo sviluppo del plugin FP Performance Suite.

## 📋 Contenuto

### Script PHP
File PHP utilizzati per diagnostica, test e fix durante lo sviluppo:
- `diagnose-*.php` - Script di diagnostica
- `fix-*.php` - Script di fix automatici
- `verifica-*.php` - Script di verifica
- `check-*.php` - Script di controllo
- `cerca-*.php` - Script di ricerca
- `lista-*.php` - Script di listing
- `crea-*.php` - Script di creazione

### Script PowerShell (`.ps1`)
Script PowerShell per automazione su Windows:
- `cleanup-*.ps1` - Script di pulizia
- `finalize-*.ps1` - Script di finalizzazione
- `ripristino-*.ps1` - Script di ripristino
- `verifica-*.ps1` - Script di verifica

### Script Shell (`.sh`)
Script bash per automazione su Linux/Mac:
- `cleanup-*.sh` - Script di pulizia
- `fix-*.sh` - Script di fix
- `update-*.sh` - Script di aggiornamento
- `GIT_*.sh` - Script Git

### File Temporanei
- `*-temp.txt` - File temporanei di output

## ⚠️ Attenzione

Questi script sono utilizzati **solo in ambiente di sviluppo**. Non eseguirli in produzione senza aver prima:
1. Letto attentamente il codice
2. Fatto un backup completo
3. Testato in staging

## 📚 Documentazione

Per la documentazione ufficiale del plugin, consultare:
- [README.md principale](../README.md)
- [Documentazione completa](../docs/INDEX.md)

## 🔧 Utilizzo

La maggior parte degli script sono pensati per essere eseguiti dalla root del progetto:

```bash
# Esempio PHP
php dev-scripts/diagnose-plugin-activation.php

# Esempio PowerShell
.\dev-scripts\cleanup-auto.ps1

# Esempio Shell
bash dev-scripts/update-zip.sh
```

## 📝 Note

- Questi script non sono inclusi nella distribuzione del plugin
- Sono mantenuti per riferimento futuro e debug
- Alcuni potrebbero essere obsoleti o non più funzionanti
- Verificare sempre il codice prima dell'esecuzione

---

[◀ Torna alla root del progetto](../)

