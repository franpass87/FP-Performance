# 🛠️ Dev Scripts - FP Performance Suite

Questa cartella contiene **script di utilità per sviluppo e deployment**.

---

## ⚠️ ATTENZIONE

**Questi script sono SOLO per sviluppo/debug!**

- ❌ NON includere in produzione
- ❌ NON eseguire su siti live
- ✅ Usa solo in ambiente development

---

## 📂 Script Disponibili

### Deployment
- `cleanup-*.ps1/sh` - Script pulizia automatica
- `update-zip.sh` - Crea ZIP per distribuzione
- `crea-pacchetto-aggiornamento.php` - Pacchetto update

### Verifica
- `check-redis.php` - Verifica configurazione Redis
- `check-activation-status.php` - Verifica stato attivazione
- `lista-zip.php` - Lista contenuti ZIP

### Git
- `GIT_COMMIT_*.sh` - Script commit automatici
- `DEPLOY_RAPIDO_FIX.sh` - Deploy rapido fix
- `fix-plugin-sync.sh` - Sincronizzazione plugin

---

## 🧹 Pulizia Recente

**Data:** 2025-01-25

Rimossi dalla cartella:
- 15+ file `test-*.php`
- 10+ file `diagnose-*.php`
- 12+ file `verifica-*.php`
- 8+ file `fix-*.php`
- File `.txt` e `.md` temporanei

**Mantenuti solo script essenziali per deployment.**

---

## 📝 Note

- Gli script possono richiedere configurazione specifica
- Controlla sempre lo script prima di eseguirlo
- Fai backup prima di eseguire script di modifica

---

**Per documentazione completa, vedi [../docs/02-developer/](../docs/02-developer/)**

