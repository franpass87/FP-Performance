# Correzione Problema Permessi di Accesso

## 🔍 Problema Rilevato

L'utente non poteva accedere a **nessuna pagina amministrativa** di FP Performance Suite, ricevendo l'errore:

```
Non hai il permesso di accedere a questa pagina.
Questo messaggio è stato generato da Core di WordPress.
```

## ✅ Soluzione Implementata

Ho implementato una soluzione completa su **4 livelli**:

### 1. **Classe Capabilities** (`src/Utils/Capabilities.php`)

**Modifiche:**
- ✅ Validazione robusta delle impostazioni `fp_ps_settings`
- ✅ Fallback sicuro a `manage_options` se le impostazioni sono corrotte
- ✅ Validazione del ruolo configurato (solo `administrator` o `editor` sono accettati)
- ✅ Logging dettagliato per debug (quando `WP_DEBUG` è attivo)
- ✅ Verifica che la capability calcolata non sia mai vuota

**Comportamento:**
```php
// Prima (potenziale problema)
$settings = get_option('fp_ps_settings', ['allowed_role' => 'administrator']);
$role = $settings['allowed_role'] ?? 'administrator';

// Dopo (sicuro)
$settings = get_option('fp_ps_settings', []);
if (!is_array($settings)) {
    $settings = ['allowed_role' => 'administrator'];
}
$role = $settings['allowed_role'] ?? 'administrator';
if (!in_array($role, ['administrator', 'editor'], true)) {
    $role = 'administrator';
}
```

### 2. **Classe AbstractPage** (`src/Admin/Pages/AbstractPage.php`)

**Modifiche:**
- ✅ Fallback automatico a `manage_options` se la capability è vuota o non valida
- ✅ **Super admin bypass**: i super admin hanno sempre accesso
- ✅ Messaggio di errore dettagliato con informazioni di debug per gli amministratori
- ✅ Pulsante per reimpostare i permessi direttamente dalla pagina di errore
- ✅ Link alla diagnostica completa
- ✅ Logging esteso per tracciare gli accessi negati

**Comportamento:**
```php
// Super admin hanno sempre accesso
if (is_super_admin()) {
    // Continua con il rendering
} elseif (!current_user_can($required_capability)) {
    // Mostra errore con opzioni di risoluzione
}
```

### 3. **Classe Menu** (`src/Admin/Menu.php`)

**Modifiche:**
- ✅ **Sistema di auto-riparazione**: se un admin è bloccato, ripristina automaticamente i permessi
- ✅ Validazione della capability prima della registrazione del menu
- ✅ Notifica admin quando viene eseguita l'auto-riparazione
- ✅ Logging della registrazione del menu

**Auto-riparazione:**
```php
// Se l'utente è admin ma non ha la capability richiesta
if (current_user_can('manage_options') && !current_user_can($capability)) {
    // Ripristina automaticamente le impostazioni
    $current_settings['allowed_role'] = 'administrator';
    update_option('fp_ps_settings', $current_settings);
    $capability = 'manage_options';
}
```

### 4. **Pagina Settings** (`src/Admin/Pages/Settings.php`)

**Modifiche:**
- ✅ Gestione del reset dei permessi tramite URL
- ✅ Messaggio di conferma quando i permessi vengono reimpostati
- ✅ Ripristino sicuro delle impostazioni predefinite

## 🛠️ Strumenti di Diagnostica Creati

### 1. **Script di Diagnosi** (`diagnose-permissions.php`)

Uno script completo che mostra:
- Informazioni sull'utente corrente (username, ruoli, capabilities)
- Configurazione del plugin (ruolo richiesto, capability)
- Verifica di tutte le capability principali
- Risultato della diagnostica con soluzioni suggerite
- Pulsante per reimpostare le impostazioni (solo per admin)

**Come usarlo:**
```
http://your-site.com/wp-content/plugins/fp-performance-suite/diagnose-permissions.php
```

### 2. **Script di Fix Rapido** (`fix-permissions.php`)

Script di emergenza che:
- Ripristina immediatamente `allowed_role` a `administrator`
- Cancella la cache delle opzioni
- Mostra conferma del ripristino
- Link diretti al plugin e alla dashboard

**Come usarlo:**
```
http://your-site.com/wp-content/plugins/fp-performance-suite/fix-permissions.php
```

## 🚀 Come Risolvere il Problema

### Metodo 1: Auto-riparazione Automatica (Consigliato)

1. **Ricarica semplicemente la dashboard di WordPress**
2. Il sistema rileverà automaticamente che un admin è bloccato
3. Le impostazioni verranno ripristinate automaticamente
4. Vedrai un messaggio di notifica
5. Ora puoi accedere a FP Performance Suite

### Metodo 2: Script di Fix Rapido

1. Vai a: `http://your-site.com/wp-content/plugins/fp-performance-suite/fix-permissions.php`
2. Conferma il ripristino
3. Clicca su "Vai a FP Performance Suite"

### Metodo 3: Script di Diagnosi Completa

1. Vai a: `http://your-site.com/wp-content/plugins/fp-performance-suite/diagnose-permissions.php`
2. Esamina le informazioni diagnostiche
3. Clicca su "Reimposta Impostazioni Predefinite" se sei admin
4. Ricarica la pagina

### Metodo 4: Manuale via Database

Se nessuno dei metodi sopra funziona, puoi ripristinare manualmente via phpMyAdmin:

```sql
-- Trova l'opzione
SELECT * FROM wp_options WHERE option_name = 'fp_ps_settings';

-- Aggiorna il valore
UPDATE wp_options 
SET option_value = 'a:1:{s:12:"allowed_role";s:13:"administrator";}' 
WHERE option_name = 'fp_ps_settings';
```

## 📝 Log di Debug

Se `WP_DEBUG` è abilitato, il plugin ora registra informazioni dettagliate in `wp-content/debug.log`:

```
[FP Performance Suite] Capabilities::required() chiamato
[FP Performance Suite] Ruolo configurato: administrator
[FP Performance Suite] Capability calcolata: manage_options
[FP Performance Suite] Utente corrente ha la capability: SI
[FP Performance Suite] Registrazione menu con capability: manage_options
[FP Performance Suite] Utente corrente può accedere: SI
```

## 🔒 Prevenzione Futura

Il sistema ora ha **3 livelli di protezione**:

1. **Validazione in Capabilities::required()**: previene valori non validi
2. **Fallback in AbstractPage::render()**: garantisce una capability valida
3. **Auto-riparazione in Menu::register()**: ripristina automaticamente se un admin è bloccato

## 📊 Test Effettuati

✅ Validazione con impostazioni corrotte  
✅ Validazione con `allowed_role` non valido  
✅ Validazione con opzione `fp_ps_settings` mancante  
✅ Validazione con array vuoto  
✅ Test super admin bypass  
✅ Test auto-riparazione  
✅ Nessun errore di linting  

## 🎯 Risultato

- **Problema**: Risolto ✅
- **Auto-riparazione**: Implementata ✅
- **Diagnostica**: 2 strumenti creati ✅
- **Logging**: Completo ✅
- **Prevenzione**: Multi-livello ✅

## 📞 Supporto

Se il problema persiste dopo aver provato tutte le soluzioni sopra:

1. Controlla `wp-content/debug.log` per messaggi dettagliati
2. Usa lo script di diagnosi per raccogliere informazioni
3. Contatta il supporto con le informazioni raccolte

---

**Autore**: Francesco Passeri  
**Data**: 2025-10-19  
**Versione Plugin**: 1.3.x

