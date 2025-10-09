# 📝 Guida al Commit - Correzioni Bug e Sicurezza

## 🎯 Obiettivo

Questo commit include correzioni critiche di sicurezza e bug logici identificati durante l'audit del codice.

---

## 📋 Checklist Pre-Commit

Prima di procedere con il commit, assicurati di:

- [x] ✅ Tutti i bug sono stati corretti
- [x] ✅ Nessun errore di sintassi PHP
- [x] ✅ Documentazione aggiornata
- [x] ✅ CHANGELOG creato
- [ ] ⏳ Test manuali eseguiti (da fare)
- [ ] ⏳ Code review completata (da fare)

---

## 💾 Comando Commit Raccomandato

```bash
git add src/Services/Cache/PageCache.php
git add src/Services/DB/Cleaner.php
git add src/Utils/Env.php
git add src/Services/Cache/Headers.php
git add BUG_FIXES_REPORT.md
git add CHANGELOG_BUG_FIXES.md
git add COMMIT_GUIDE.md
```

### Messaggio di Commit

```
fix(security): Correggi vulnerabilità SQL injection e sanitizza accessi $_SERVER

BREAKING: No
SECURITY FIX: Yes (CRITICAL)

Correzioni applicate:
- [CRITICAL] SQL injection in Cleaner::optimizeTables()
- [HIGH] Sanitizzati accessi a $_SERVER in PageCache, Headers, Env
- [MEDIUM] Rimossa chiamata inappropriata a is_main_query()
- [MEDIUM] Corretta precedenza operatori in condizione cache

File modificati:
- src/Services/Cache/PageCache.php (4 fix)
- src/Services/DB/Cleaner.php (1 fix critico)
- src/Utils/Env.php (1 fix)
- src/Services/Cache/Headers.php (1 fix)

Totale bug corretti: 7
Bug critici di sicurezza: 3

Riferimenti:
- BUG_FIXES_REPORT.md (report dettagliato)
- CHANGELOG_BUG_FIXES.md (changelog formale)

Co-authored-by: AI Assistant <ai@cursor.sh>
```

---

## 🔍 Verifica Post-Commit

Dopo il commit, verifica:

```bash
# 1. Controlla lo stato
git status

# 2. Verifica i file modificati
git diff HEAD~1

# 3. Controlla il log
git log -1 --stat

# 4. Verifica che non ci siano file non tracciati importanti
git ls-files --others --exclude-standard
```

---

## 🚀 Deployment Strategy

### Ambiente di Staging

```bash
# 1. Crea branch di staging
git checkout -b staging/security-fixes

# 2. Push su remote
git push origin staging/security-fixes

# 3. Deploy su staging
# [Usa il tuo processo di deployment]

# 4. Esegui test completi su staging
```

### Test su Staging

Esegui questi test prima del deploy in produzione:

#### Test Funzionali
```bash
# Cache test
wp fp-performance cache purge
wp fp-performance cache status

# Database cleanup test (dry-run)
wp fp-performance db cleanup --scope=revisions --dry-run

# WebP conversion test
wp fp-performance webp convert --limit=5

# Score calculation
wp fp-performance score
```

#### Test Sicurezza

1. **Test SQL Injection (dovrebbe fallire)**
   ```bash
   # Prova a iniettare codice SQL nelle operazioni di cleanup
   # Verifica che la sanitizzazione funzioni
   ```

2. **Test XSS via $_SERVER (dovrebbe fallire)**
   ```bash
   # Prova a iniettare script via headers HTTP
   # Verifica che la sanitizzazione blocchi tutto
   ```

#### Test Regressione

- [ ] Cache page funziona correttamente
- [ ] Purge cache per URL specifici funziona
- [ ] Purge cache per post funziona
- [ ] Auto-purge su update post funziona
- [ ] Database cleanup funziona (dry-run e real)
- [ ] Ottimizzazione tabelle funziona
- [ ] WebP conversion funziona
- [ ] Asset optimization (defer/async) funziona
- [ ] HTML minification funziona
- [ ] Preset application funziona
- [ ] Debug toggles funzionano
- [ ] Performance score calculation funziona

### Produzione

Solo dopo test completi su staging:

```bash
# 1. Merge su main
git checkout main
git merge staging/security-fixes

# 2. Tag versione
git tag -a v1.1.1 -m "Security fixes and bug corrections"

# 3. Push con tag
git push origin main --tags

# 4. Deploy su produzione
# [Usa il tuo processo di deployment]
```

---

## 📢 Comunicazione

### Utenti del Plugin

Invia notifica agli utenti:

**Oggetto:** [IMPORTANTE] Aggiornamento di sicurezza disponibile per FP Performance Suite

**Corpo:**
```
Ciao,

È disponibile un aggiornamento importante per FP Performance Suite (v1.1.1) 
che include correzioni critiche di sicurezza.

Correzioni incluse:
- Vulnerabilità SQL injection (CRITICA)
- Sanitizzazione accessi superglobali (ALTA)
- Bug logici vari

Si raccomanda di aggiornare quanto prima.

Changelog completo: [link]

Grazie,
Francesco Passeri
```

### Changelog Pubblico

Aggiungi al README.md:

```markdown
## [1.1.1] - 2025-10-09

### 🔒 Security
- **[CRITICAL]** Fixed SQL injection vulnerability in database optimization
- **[HIGH]** Sanitized all $_SERVER superglobal accesses
- Improved overall security posture

### 🐛 Bug Fixes
- Removed inappropriate is_main_query() call
- Fixed operator precedence in cache invalidation logic

### 📊 Statistics
- 7 bugs fixed
- 3 critical security issues resolved
- 4 files modified
```

---

## 🆘 Rollback Plan

In caso di problemi critici:

### Rollback Rapido

```bash
# 1. Torna alla versione precedente
git revert HEAD

# 2. Push immediato
git push origin main

# 3. Deploy rollback
# [Usa il tuo processo di deployment]
```

### Rollback con Tag

```bash
# 1. Checkout tag precedente
git checkout v1.1.0

# 2. Crea branch di hotfix
git checkout -b hotfix/rollback-v1.1.1

# 3. Force push (ATTENZIONE!)
git push origin main --force
```

### Backup Database

Prima del deployment, assicurati di avere:

```bash
# Backup completo database
wp db export backup-pre-v1.1.1-$(date +%Y%m%d-%H%M%S).sql

# Backup files
tar -czf backup-files-pre-v1.1.1-$(date +%Y%m%d-%H%M%S).tar.gz wp-content/plugins/fp-performance-suite/
```

---

## 📊 Metriche di Successo

Monitora queste metriche dopo il deployment:

### Performance
- Tempo di risposta pagine (dovrebbe rimanere invariato)
- Hit rate cache (dovrebbe rimanere invariato o migliorare)
- Errori PHP (dovrebbero diminuire)

### Sicurezza
- Tentativi di SQL injection bloccati
- XSS attempts bloccati
- Accessi non autorizzati bloccati

### Qualità
- Bug reports (dovrebbero diminuire)
- Support tickets (dovrebbero diminuire)
- User satisfaction (dovrebbe migliorare)

---

## 🔗 Risorse Utili

- [WordPress Security Best Practices](https://wordpress.org/support/article/hardening-wordpress/)
- [OWASP Top 10](https://owasp.org/www-project-top-ten/)
- [PHP Security Cheat Sheet](https://cheatsheetseries.owasp.org/cheatsheets/PHP_Configuration_Cheat_Sheet.html)

---

## ✅ Sign-off

**Developer:** Francesco Passeri  
**Reviewer:** [Da assegnare]  
**QA:** [Da assegnare]  
**Security Review:** [Da assegnare]  

**Data completamento:** 2025-10-09  
**Approved for deployment:** [ ] YES / [ ] NO

---

**Note aggiuntive:**

⚠️ Questo è un aggiornamento di sicurezza critico. Il deployment dovrebbe essere prioritizzato.

💡 Considera di programmare il deployment in una finestra di manutenzione per minimizzare l'impatto sugli utenti.

🔔 Monitora attentamente i log dopo il deployment per le prime 24-48 ore.