# 🛡️ Gestione Errori Critici all'Installazione

## Panoramica

Questo documento descrive le migliorie implementate per la gestione degli errori critici durante l'installazione e l'attivazione del plugin FP Performance Suite.

## 🎯 Problemi Risolti

### 1. Mancanza di Controlli Preliminari
**Prima:** Il plugin si attivava senza verificare i requisiti di sistema.
**Dopo:** Controlli automatici prima dell'attivazione per:
- Versione PHP minima (7.4.0+)
- Estensioni PHP richieste (json, mbstring, fileinfo)
- Permessi di scrittura directory
- Disponibilità funzioni WordPress critiche

### 2. Messaggi di Errore Poco Chiari
**Prima:** Messaggi di errore generici e poco utili.
**Dopo:** Messaggi dettagliati con:
- 🎯 Descrizione chiara dell'errore
- 💡 Soluzione suggerita automaticamente
- 📊 Dettagli tecnici espandibili
- 🔗 Link diretti al supporto

### 3. Nessun Sistema di Recupero Automatico
**Prima:** L'utente doveva risolvere manualmente ogni problema.
**Dopo:** Sistema di recovery automatico per:
- Problemi di permessi directory
- Directory mancanti
- File essenziali mancanti

### 4. Interfaccia di Debug Inadeguata
**Prima:** Nessuna interfaccia per diagnosticare problemi.
**Dopo:** Nuova pagina "Diagnostics" con:
- Report diagnostico completo
- Strumenti di recupero automatico
- Informazioni di sistema dettagliate
- Stato estensioni PHP

## 📁 File Modificati/Creati

### File Modificati

#### 1. `src/Plugin.php`
**Modifiche:**
- ✅ Aggiunto metodo `performSystemChecks()` per controlli preliminari
- ✅ Aggiunto metodo `ensureRequiredDirectories()` per creare directory necessarie
- ✅ Aggiunto metodo `formatActivationError()` per formattare errori con soluzioni
- ✅ Integrato sistema di recovery automatico nel catch block

**Nuove Funzionalità:**
```php
// Controlli preliminari di sistema
self::performSystemChecks();

// Verifica e crea directory necessarie
self::ensureRequiredDirectories();

// Tenta recupero automatico su errore
$recovered = InstallationRecovery::attemptRecovery($errorDetails);
```

#### 2. `src/Admin/Menu.php`
**Modifiche:**
- ✅ Interfaccia errori completamente ridisegnata con icone e colori
- ✅ Aggiunto feedback visivo per tentativo di recovery
- ✅ Aggiunto link alla pagina Diagnostics
- ✅ Dettagli tecnici in sezione espandibile
- ✅ Aggiunta classe `Diagnostics` all'elenco delle pagine

**Nuove Funzionalità:**
- Messaggi di errore categorizzati per tipo
- Soluzioni contestuali basate sul tipo di errore
- Feedback sul successo/fallimento del recovery automatico

### Nuovi File Creati

#### 1. `src/Utils/InstallationRecovery.php`
**Classe:** `FP\PerfSuite\Utils\InstallationRecovery`

**Metodi Pubblici:**
- `attemptRecovery(array $error): bool` - Tenta il recupero automatico
- `runDiagnostics(): array` - Esegue diagnostica completa
- `generateDiagnosticReport(array $diagnostics): string` - Genera report HTML

**Funzionalità:**
- ✅ Recovery automatico per permessi
- ✅ Creazione automatica directory
- ✅ Verifica file essenziali
- ✅ Report diagnostico completo con:
  - Versione PHP e WordPress
  - Estensioni PHP caricate
  - Permessi directory
  - Limite di memoria
  - Connessione database

#### 2. `src/Admin/Pages/Diagnostics.php`
**Classe:** `FP\PerfSuite\Admin\Pages\Diagnostics`

**Interfaccia Utente:**
- 🔍 **Diagnostica di Sistema** - Esegui controlli completi
- ⚠️ **Errore di Attivazione** - Visualizza e cancella errori
- 🔧 **Strumenti di Recupero** - Recovery automatico
- ℹ️ **Informazioni di Sistema** - PHP, WordPress, Server
- 📦 **Estensioni PHP** - Stato e note per ogni estensione

**Azioni Disponibili:**
- `run_diagnostics` - Esegue diagnostica completa
- `fix_permissions` - Ripara permessi directory
- `clear_error` - Cancella errore di attivazione

## 🔍 Tipi di Errori Gestiti

### 1. `php_version`
**Errore:** Versione PHP non compatibile
**Soluzione:** "Aggiorna PHP alla versione 7.4 o superiore tramite il pannello di hosting."
**Icona:** ⚠️

### 2. `php_extension`
**Errore:** Estensione PHP mancante
**Soluzione:** "Abilita le estensioni PHP richieste (json, mbstring, fileinfo) tramite il pannello di hosting."
**Icona:** ⚠️

### 3. `permissions`
**Errore:** Permessi directory insufficienti
**Soluzione:** "Verifica i permessi delle directory. La directory wp-content/uploads deve essere scrivibile (chmod 755 o 775)."
**Icona:** 🔒
**Recovery:** ✅ Automatico

### 4. `missing_class`
**Errore:** Classe PHP non trovata
**Soluzione:** "Reinstalla il plugin assicurandoti che tutti i file siano stati caricati correttamente."
**Icona:** ❌
**Recovery:** ✅ Verifica automatica

### 5. `memory_limit`
**Errore:** Limite di memoria insufficiente
**Soluzione:** "Aumenta il limite di memoria PHP (memory_limit) a almeno 128MB nel file php.ini."
**Icona:** ⚠️

## 🧪 Testing

### Test Case 1: Errore Estensione PHP Mancante
```php
// Simula estensione mancante (solo per test)
// Modifica temporaneamente performSystemChecks()
```

**Risultato Atteso:**
1. Plugin si attiva ma mostra errore
2. Messaggio dettagliato con soluzione
3. Link a pagina Diagnostics
4. No white screen

### Test Case 2: Permessi Directory Errati
```bash
# Rendi uploads non scrivibile
chmod 000 wp-content/uploads
```

**Risultato Atteso:**
1. Errore di permessi rilevato
2. Recovery automatico tentato
3. Se fallisce, messaggio con soluzione chiara
4. Strumento manuale in Diagnostics

### Test Case 3: Diagnostica Completa
**Steps:**
1. Vai su FP Performance → Diagnostics
2. Clicca "Esegui Diagnostica"

**Risultato Atteso:**
- Report completo con tutti i controlli
- Status pass/fail/warning per ogni check
- Dettagli JSON completi
- Nessun errore PHP

### Test Case 4: Recovery Manuale
**Steps:**
1. Vai su FP Performance → Diagnostics
2. Clicca "Ripara Permessi Directory"

**Risultato Atteso:**
- Directory create se mancanti
- Permessi corretti a 755
- File .htaccess e index.php aggiunti
- Messaggio di successo/fallimento

## 📊 Report Diagnostico

Il report include:

```json
{
  "timestamp": "2025-10-18 10:30:00",
  "php_version": "8.1.0",
  "wp_version": "6.4.0",
  "plugin_version": "1.2.0",
  "checks": {
    "php_version": {
      "status": "pass",
      "current": "8.1.0",
      "required": "7.4.0"
    },
    "php_extensions": {
      "status": "pass",
      "extensions": {
        "json": true,
        "mbstring": true,
        "fileinfo": true
      }
    },
    "directory_permissions": {
      "status": "pass",
      "upload_dir": "/path/to/uploads",
      "writable": true
    },
    "memory_limit": {
      "status": "pass",
      "current": "256M",
      "recommended": "128M"
    },
    "essential_files": {
      "status": "pass"
    },
    "database": {
      "status": "pass",
      "prefix": "wp_"
    }
  }
}
```

## 🎨 Interfaccia Utente

### Notifica di Errore (Admin Notices)
```
┌─────────────────────────────────────────────────┐
│ ❌ FP Performance Suite: Errore Critico         │
│                                                  │
│ Errore: Estensione PHP richiesta non trovata    │
│                                                  │
│ ┌─────────────────────────────────────────────┐ │
│ │ 💡 Soluzione:                               │ │
│ │ Abilita le estensioni PHP richieste...     │ │
│ └─────────────────────────────────────────────┘ │
│                                                  │
│ ▸ Dettagli tecnici (clicca per espandere)      │
│                                                  │
│ [Ho risolto] [Diagnostica] [Supporto]          │
└─────────────────────────────────────────────────┘
```

### Pagina Diagnostics
```
┌─────────────────────────────────────────────────┐
│ 🔍 Diagnostica di Sistema                       │
│ Esegui una diagnostica completa...              │
│ [Esegui Diagnostica]                            │
├─────────────────────────────────────────────────┤
│ 📊 Report Diagnostico                           │
│ ✅ Versione PHP: PASS                           │
│ ✅ Estensioni PHP: PASS                         │
│ ✅ Permessi: PASS                               │
│ ⚠️ Memoria: WARNING                             │
└─────────────────────────────────────────────────┘
```

## 🔧 Manutenzione

### Aggiungere un Nuovo Tipo di Errore

1. **In `Plugin.php`** - Metodo `formatActivationError()`:
```php
elseif (strpos($message, 'tuo_errore') !== false) {
    $errorType = 'nuovo_tipo';
    $solution = 'La tua soluzione qui';
}
```

2. **In `Menu.php`** - Aggiorna icone/colori se necessario

3. **In `InstallationRecovery.php`** - Aggiungi metodo di recovery:
```php
private static function fixNuovoTipo(): bool {
    // Logica di recovery
    return true;
}
```

4. **Aggiorna `$recoveryMethods` array:**
```php
'nuovo_tipo' => [self::class, 'fixNuovoTipo'],
```

### Aggiungere un Nuovo Check Diagnostico

In `InstallationRecovery.php` - Metodo `runDiagnostics()`:
```php
$report['checks']['nuovo_check'] = [
    'status' => $condition ? 'pass' : 'fail',
    'current' => $currentValue,
    'required' => $requiredValue,
];
```

## 📚 Documentazione API

### InstallationRecovery::attemptRecovery()
```php
/**
 * Tenta il recupero automatico da un errore di installazione
 * 
 * @param array $error Dettagli dell'errore con chiave 'type'
 * @return bool True se il recupero ha avuto successo
 */
public static function attemptRecovery(array $error): bool
```

### InstallationRecovery::runDiagnostics()
```php
/**
 * Esegue un controllo diagnostico completo del sistema
 * 
 * @return array Report diagnostico con timestamp e checks
 */
public static function runDiagnostics(): array
```

## ✅ Checklist Deployment

- [x] Modifiche a `src/Plugin.php`
- [x] Modifiche a `src/Admin/Menu.php`
- [x] Creato `src/Utils/InstallationRecovery.php`
- [x] Creato `src/Admin/Pages/Diagnostics.php`
- [x] Aggiunto import in `Menu.php`
- [ ] Test su ambiente di staging
- [ ] Verifica permessi directory
- [ ] Test con PHP 7.4, 8.0, 8.1
- [ ] Test con estensioni mancanti
- [ ] Verifica traduzioni

## 🌐 Traduzioni

I seguenti testi richiedono traduzione nel file `.pot`:
- "System Diagnostics"
- "Diagnostics"
- "Errore Critico all'Installazione"
- "Soluzione"
- "Dettagli tecnici"
- "Recupero Automatico"
- "Esegui Diagnostica"
- "Ripara Permessi Directory"
- E altri...

## 🎓 Best Practices

1. **Non rilanciare mai eccezioni** nel catch block di `onActivate()`
2. **Logga sempre** gli errori prima del recovery
3. **Fornisci sempre** un'azione alternativa all'utente
4. **Testa su diverse** configurazioni di hosting
5. **Mantieni i messaggi** chiari e non tecnici per utenti finali
6. **Usa i dettagli tecnici** solo nelle sezioni espandibili

## 🐛 Troubleshooting

### La pagina Diagnostics non appare
**Causa:** Import mancante o errore di sintassi
**Soluzione:** Verifica `use` statement in `Menu.php`

### Recovery automatico non funziona
**Causa:** Permessi insufficienti o hosting restrittivo
**Soluzione:** Documentare limitazioni e fornire istruzioni manuali

### Report diagnostico vuoto
**Causa:** Errore durante `runDiagnostics()`
**Soluzione:** Controllare log per eccezioni, verificare permessi database

## 📞 Supporto

Per problemi o domande:
- **Email:** support@francescopasseri.com
- **Documentazione:** https://francescopasseri.com/docs
- **GitHub Issues:** (se applicabile)

---

**Versione:** 1.2.0  
**Data:** 2025-10-18  
**Autore:** Francesco Passeri
