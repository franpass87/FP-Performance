# 📚 Guida all'Uso delle Nuove Funzionalità - FP Performance Suite

**Data:** 20 Ottobre 2025  
**Versione Plugin:** 1.4.0+  
**Per:** Sviluppatori e Manutentori

---

## 🎯 Panoramica

Questa guida spiega come utilizzare le nuove funzionalità di accessibilità, modal e validazione implementate nel plugin.

---

## 1. 🤝 Accessibilità - Utility JavaScript

### Import e Funzioni Disponibili

```javascript
import { 
    initAccessibleToggles,
    trapFocus,
    announceToScreenReader,
    initAccessibleTooltip,
    initAccessibleForm,
    isAccessible
} from './utils/accessibility.js';
```

### 1.1 Annunciare Messaggi agli Screen Reader

```javascript
import { announceToScreenReader } from './utils/accessibility.js';

// Messaggio informativo (non intrusivo)
announceToScreenReader('Operazione completata con successo', 'polite');

// Messaggio urgente (interrompe lettura corrente)
announceToScreenReader('Errore critico: connessione fallita', 'assertive');

// Usa dopo azioni AJAX
jQuery.ajax({
    // ...
    success: function(response) {
        announceToScreenReader('Dati salvati correttamente', 'polite');
    },
    error: function() {
        announceToScreenReader('Errore durante il salvataggio', 'assertive');
    }
});
```

### 1.2 Focus Trap per Dialog/Modal

```javascript
import { trapFocus } from './utils/accessibility.js';

// Quando apri un modal/dialog
const modal = document.querySelector('.my-modal');
modal.style.display = 'block';

const releaseTrap = trapFocus(modal);

// Quando chiudi il modal
modal.style.display = 'none';
releaseTrap(); // Ripristina focus all'elemento precedente
```

### 1.3 Tooltip Accessibili

```javascript
import { initAccessibleTooltip } from './utils/accessibility.js';

const infoButton = document.querySelector('.info-button');
const tooltip = document.querySelector('.tooltip-content');

const { show, hide } = initAccessibleTooltip(infoButton, tooltip);

// Gestione automatica di:
// - Mouse hover
// - Keyboard focus
// - Enter/Spazio per toggle
// - Escape per chiudere
```

### 1.4 Form Accessibili

```javascript
import { initAccessibleForm } from './utils/accessibility.js';

// Aggiungi data-accessible al form
<form data-accessible method="post">
    <input type="email" id="email" name="email" required>
    <span id="email-error" role="alert"></span>
</form>

// JavaScript
initAccessibleForm(document.querySelector('form[data-accessible]'));

// Gestisce automaticamente:
// - aria-invalid su errori
// - Focus sul primo campo invalido
// - Annunci screen reader per errori
```

---

## 2. 💬 Modal Dialog - API Completa

### Import

```javascript
// ES6 Module
import { Modal, confirm, alert, deleteConfirm } from './components/modal.js';

// O uso globale (esposto automaticamente)
const { confirm, alert, deleteConfirm } = window.fpPerfSuiteUtils;
```

### 2.1 Conferma Semplice

```javascript
// Sostituisce window.confirm()
if (await confirm('Sei sicuro di voler procedere?')) {
    // Utente ha cliccato "Conferma"
    console.log('Confermato!');
} else {
    // Utente ha cliccato "Annulla" o ESC
    console.log('Annullato');
}
```

### 2.2 Alert Informativo

```javascript
// Sostituisce window.alert()
await alert('Operazione completata con successo!', {
    title: 'Successo',
    icon: '✅'
});

// Solo bottone OK, nessun annulla
```

### 2.3 Conferma Eliminazione (Danger Style)

```javascript
// Stile rosso per azioni distruttive
if (await deleteConfirm('Eliminare definitivamente 5 elementi?')) {
    // Procedi con eliminazione
    deleteItems();
}

// Modal rosso con icona ⚠️ e bottone danger
```

### 2.4 Modal Personalizzato Avanzato

```javascript
import { Modal } from './components/modal.js';

const modal = new Modal({
    title: 'Conferma Esportazione',
    message: 'Vuoi esportare tutti i dati in formato CSV?',
    confirmText: 'Esporta',
    cancelText: 'Annulla',
    confirmClass: 'button-primary',
    danger: false,
    showCancel: true,
    icon: '📊',
    size: 'medium' // 'small', 'medium', 'large'
});

const result = await modal.show();
if (result) {
    // Esporta
}
```

### 2.5 Auto-Replace confirm() esistenti

Il sistema **automaticamente converte** i vecchi `onclick="confirm()"`:

```php
<!-- PRIMA - vecchio stile -->
<button onclick="return confirm('Eliminare?')">Elimina</button>

<!-- DOPO - viene automaticamente convertito in modal personalizzato -->
<!-- Nessuna modifica necessaria! -->
```

**Come funziona:**
- `initConfirmModals()` trova tutti i `onclick` con `confirm()`
- Li converte automaticamente in modal personalizzati
- Mantiene lo stesso comportamento

---

## 3. ✅ FormValidator - Validazione PHP

### Import

```php
use FP\PerfSuite\Utils\FormValidator;
```

### 3.1 Validazione Base

```php
// Validazione semplice
$validator = FormValidator::validate($_POST, [
    'email' => ['required', 'email'],
    'age' => ['required', 'numeric', 'min' => 18, 'max' => 120],
    'country' => ['required', 'in' => ['IT', 'US', 'UK', 'FR']],
]);

if ($validator->fails()) {
    $message = $validator->firstError();
    // "Il campo Email è obbligatorio."
    // "Il campo Age deve essere almeno 18."
} else {
    $data = $validator->validated();
    // Usa $data per salvare
}
```

### 3.2 Regole Disponibili

```php
// required - Campo obbligatorio
'field' => ['required']

// email - Validazione email
'email' => ['email']

// url - Validazione URL
'website' => ['url']

// numeric - Deve essere numero
'price' => ['numeric']

// integer - Deve essere intero
'quantity' => ['integer']

// min - Valore minimo (numerico)
'ttl' => ['numeric', 'min' => 60]

// max - Valore massimo (numerico)
'ttl' => ['numeric', 'max' => 86400]

// min_length - Lunghezza minima stringa
'password' => ['required', 'min_length' => 8]

// max_length - Lunghezza massima stringa
'title' => ['max_length' => 200]

// in - Valore deve essere in array
'status' => ['in' => ['draft', 'published', 'archived']]

// not_in - Valore NON deve essere in array
'role' => ['not_in' => ['admin', 'super_admin']]

// boolean - Deve essere booleano
'enabled' => ['boolean']

// array - Deve essere array
'items' => ['array']

// regex - Deve matchare pattern
'slug' => ['regex' => '/^[a-z0-9-]+$/']

// confirmed - Campo deve coincidere con field_confirmation
'password' => ['required', 'confirmed'] // Cerca password_confirmation
```

### 3.3 Etichette Personalizzate

```php
// Senza etichette (usa nome campo)
$validator = FormValidator::validate($_POST, [
    'cache_ttl' => ['required', 'numeric'],
]);
// Errore: "Il campo Cache Ttl è obbligatorio." (auto-genera)

// Con etichette personalizzate (consigliato)
$validator = FormValidator::validate($_POST, [
    'cache_ttl' => ['required', 'numeric'],
], [
    'cache_ttl' => __('Durata Cache', 'fp-performance-suite'),
]);
// Errore: "Il campo Durata Cache è obbligatorio."
```

### 3.4 Gestione Errori Avanzata

```php
$validator = FormValidator::validate($_POST, $rules);

// Verifica se ha errori
if ($validator->fails()) {
    // Primo errore (qualsiasi campo)
    $firstError = $validator->firstError();
    
    // Tutti gli errori
    $allErrors = $validator->errors();
    // ['email' => ['Email non valida'], 'age' => ['Minimo 18']]
    
    // Errori di un campo specifico
    $emailErrors = $validator->errorsFor('email');
    // ['Email non valida', 'Email già in uso'] (se multipli)
    
    // Numero totale errori
    $count = $validator->errorCount();
    
    // Verifica se campo specifico ha errori
    if ($validator->hasError('email')) {
        // Fai qualcosa
    }
}
```

### 3.5 Renderizzare Errori in HTML

```php
<?php if ($validator->fails()): ?>
    <!-- Mostra tutti gli errori -->
    <?php echo $validator->renderErrors(); ?>
    
    <!-- O errori per campo specifico -->
    <label for="email">Email</label>
    <input type="email" id="email" name="email">
    <?php echo $validator->renderErrors('email'); ?>
<?php endif; ?>

<!-- Output HTML -->
<div class="fp-ps-validation-errors" role="alert" aria-live="assertive">
    <ul style="margin: 0; padding-left: 20px; color: #dc2626;">
        <li>Il campo Email è obbligatorio.</li>
    </ul>
</div>
```

### 3.6 Regole Personalizzate

```php
$validator = FormValidator::validate($_POST, [
    'username' => ['required'],
]);

// Aggiungi validazione custom
$validator->addCustomRule('username', function($value, $allData) {
    // Check database se username esiste
    global $wpdb;
    $exists = $wpdb->get_var($wpdb->prepare(
        "SELECT COUNT(*) FROM wp_users WHERE user_login = %s",
        $value
    ));
    
    return $exists == 0; // True = passa, False = fallisce
}, __('Questo username è già in uso.', 'fp-performance-suite'));

if ($validator->fails()) {
    // Mostra errore custom
}
```

### 3.7 Ottenere Dati Validati

```php
$validator = FormValidator::validate($_POST, $rules);

if ($validator->passes()) {
    // Tutti i dati validati
    $all = $validator->validated();
    
    // Solo campi specifici
    $subset = $validator->only(['email', 'name']);
    // ['email' => '...', 'name' => '...']
    
    // Tutti tranne specifici
    $withoutSensitive = $validator->except(['password', 'token']);
    
    // Usa i dati
    update_option('my_settings', $subset);
}
```

---

## 4. 🎨 Toggle Accessibili - Pattern PHP

### Pattern Completo

```php
<label class="fp-ps-toggle">
    <span class="info">
        <strong id="feature-name-label">
            <?php esc_html_e('Abilita Feature', 'fp-performance-suite'); ?>
        </strong>
        <span class="description" id="feature-name-description">
            <?php esc_html_e('Descrizione dettagliata della feature e cosa fa.', 'fp-performance-suite'); ?>
        </span>
    </span>
    <input 
        type="checkbox" 
        name="feature_enabled" 
        value="1"
        <?php checked($settings['feature_enabled']); ?>
        role="switch"
        aria-labelledby="feature-name-label"
        aria-describedby="feature-name-description"
        aria-checked="<?php echo !empty($settings['feature_enabled']) ? 'true' : 'false'; ?>"
    />
</label>
```

### Checklist Toggle Accessibile

- ✅ ID unico per label (`id="feature-name-label"`)
- ✅ ID unico per description (`id="feature-name-description"`)
- ✅ `role="switch"` sull'input
- ✅ `aria-labelledby` che punta al label
- ✅ `aria-describedby` che punta alla description
- ✅ `aria-checked` con stato dinamico
- ✅ Gestito automaticamente da `accessibility.js`

---

## 5. 🔧 Esempi Pratici Completi

### Esempio 1: Form con Validazione

```php
<?php
use FP\PerfSuite\Utils\FormValidator;

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && wp_verify_nonce($_POST['nonce'], 'my_action')) {
    $validator = FormValidator::validate($_POST, [
        'cache_ttl' => ['required', 'numeric', 'min' => 60, 'max' => 86400],
        'alert_email' => ['email'],
        'schedule' => ['required', 'in' => ['hourly', 'daily', 'weekly']],
    ], [
        'cache_ttl' => __('Durata Cache', 'fp-performance-suite'),
        'alert_email' => __('Email Avvisi', 'fp-performance-suite'),
        'schedule' => __('Pianificazione', 'fp-performance-suite'),
    ]);
    
    if ($validator->fails()) {
        $message = $validator->firstError();
    } else {
        $data = $validator->validated();
        update_option('my_settings', $data);
        $message = __('Impostazioni salvate!', 'fp-performance-suite');
    }
}
?>

<?php if ($message): ?>
    <div class="notice <?php echo $validator->fails() ? 'notice-error' : 'notice-success'; ?>">
        <p><?php echo esc_html($message); ?></p>
        <?php if ($validator->fails()): ?>
            <?php echo $validator->renderErrors(); ?>
        <?php endif; ?>
    </div>
<?php endif; ?>

<form method="post" data-accessible>
    <?php wp_nonce_field('my_action', 'nonce'); ?>
    
    <!-- Campo con ARIA -->
    <p>
        <label for="cache_ttl">
            <?php esc_html_e('Durata Cache (secondi)', 'fp-performance-suite'); ?>
        </label>
        <input 
            type="number" 
            id="cache_ttl" 
            name="cache_ttl"
            value="3600"
            min="60"
            max="86400"
            aria-invalid="<?php echo $validator->hasError('cache_ttl') ? 'true' : 'false'; ?>"
            aria-describedby="cache-ttl-error"
        >
        <?php if ($validator->hasError('cache_ttl')): ?>
            <span id="cache-ttl-error" role="alert" style="color: #dc2626;">
                <?php echo esc_html($validator->firstErrorFor('cache_ttl')); ?>
            </span>
        <?php endif; ?>
    </p>
    
    <button type="submit" class="button button-primary">
        <?php esc_html_e('Salva Impostazioni', 'fp-performance-suite'); ?>
    </button>
</form>
```

### Esempio 2: Modal con Conferma

```php
<button 
    id="delete-cache-btn" 
    class="button button-danger"
    data-confirm-message="<?php esc_attr_e('Eliminare tutta la cache? Questa operazione non può essere annullata.', 'fp-performance-suite'); ?>"
>
    <?php esc_html_e('Elimina Cache', 'fp-performance-suite'); ?>
</button>

<script type="module">
import { deleteConfirm } from './components/modal.js';

document.getElementById('delete-cache-btn').addEventListener('click', async function(e) {
    e.preventDefault();
    
    const message = this.getAttribute('data-confirm-message');
    
    if (await deleteConfirm(message)) {
        // Esegui eliminazione
        const response = await fetch('/wp-admin/admin-ajax.php', {
            method: 'POST',
            body: new FormData(/* ... */)
        });
        
        if (response.ok) {
            await alert('Cache eliminata con successo!', {
                icon: '✅',
                title: 'Successo'
            });
        }
    }
});
</script>
```

### Esempio 3: Bulk Actions con Progress

```php
<button id="optimize-all">
    <?php esc_html_e('Ottimizza Tutte le Tabelle', 'fp-performance-suite'); ?>
</button>

<script type="module">
import { confirm } from './components/modal.js';
import { announceToScreenReader } from './utils/accessibility.js';
import { showProgress, updateProgress, removeProgress } from './components/progress.js';

document.getElementById('optimize-all').addEventListener('click', async function() {
    // Conferma con modal
    const confirmed = await confirm(
        'Ottimizzare tutte le 47 tabelle del database? L\'operazione potrebbe richiedere alcuni minuti.',
        {
            title: 'Conferma Ottimizzazione',
            confirmText: 'Inizia Ottimizzazione',
            icon: '⚙️'
        }
    );
    
    if (!confirmed) return;
    
    // Mostra progress
    const progressId = showProgress('Ottimizzazione in corso...', 0);
    
    try {
        // Simulazione processamento
        for (let i = 1; i <= 47; i++) {
            await optimizeTable(i);
            updateProgress(progressId, (i / 47) * 100);
        }
        
        // Completa
        removeProgress(progressId);
        announceToScreenReader('Ottimizzazione completata con successo', 'polite');
        
        await alert('Tutte le 47 tabelle sono state ottimizzate!', {
            title: 'Operazione Completata',
            icon: '✅'
        });
        
    } catch (error) {
        removeProgress(progressId);
        announceToScreenReader('Errore durante l\'ottimizzazione', 'assertive');
        
        await alert('Si è verificato un errore: ' + error.message, {
            title: 'Errore',
            icon: '❌',
            confirmText: 'OK'
        });
    }
});
</script>
```

---

## 6. 🎯 Best Practices

### Accessibilità
- ✅ **Sempre** usa `role="switch"` per toggle on/off
- ✅ **Sempre** fornisci `aria-labelledby` e `aria-describedby`
- ✅ **Mai** nascondere informazioni importanti solo visualmente
- ✅ **Testa** con screen reader reale (NVDA o JAWS)

### Modal
- ✅ Usa `deleteConfirm()` per azioni distruttive
- ✅ Usa `confirm()` per azioni reversibili
- ✅ Usa `alert()` solo per informazioni, non richieste conferma
- ✅ **Mai** usare `window.confirm()` o `window.alert()` nativi

### Validazione
- ✅ **Sempre** valida lato server con FormValidator
- ✅ Fornisci etichette italiane personalizzate
- ✅ Mostra errori vicino ai campi (non solo in alto)
- ✅ Usa `aria-invalid` e `aria-describedby` per accessibilità

### Performance
- ✅ Usa cache in memoria per settings (pattern già implementato)
- ✅ Evita chiamate `get_option()` ripetute
- ✅ Invalida cache dopo `update()`

---

## 7. 📊 Migrazione da Codice Vecchio

### Sostituire confirm() nativi

**PRIMA:**
```php
<button onclick="return confirm('Sei sicuro?')">Elimina</button>
```

**DOPO (opzione 1 - auto):**
```php
<button onclick="return confirm('Sei sicuro?')">Elimina</button>
<!-- Convertito automaticamente da initConfirmModals() -->
```

**DOPO (opzione 2 - manuale preferita):**
```php
<button id="delete-btn">Elimina</button>
<script type="module">
import { deleteConfirm } from './components/modal.js';

document.getElementById('delete-btn').addEventListener('click', async (e) => {
    e.preventDefault();
    if (await deleteConfirm('Sei sicuro?')) {
        // Elimina
    }
});
</script>
```

### Sostituire validazione manuale

**PRIMA:**
```php
if (empty($_POST['email']) || !is_email($_POST['email'])) {
    $message = 'Email non valida';
}
if (empty($_POST['ttl']) || !is_numeric($_POST['ttl']) || $_POST['ttl'] < 60) {
    $message = 'TTL deve essere almeno 60';
}
// ... ripetuto per ogni campo
```

**DOPO:**
```php
$validator = FormValidator::validate($_POST, [
    'email' => ['required', 'email'],
    'ttl' => ['required', 'numeric', 'min' => 60],
]);

if ($validator->fails()) {
    $message = $validator->firstError();
} else {
    $data = $validator->validated();
    // Usa dati validati
}
```

**Benefici:** -70% codice, messaggi consistenti, più leggibile

---

## 8. 🧪 Testing

### Test Accessibilità

```bash
# 1. Keyboard navigation
# - Tab tra gli elementi
# - Spazio per attivare toggle
# - Enter per submit form
# - Escape per chiudere modal

# 2. Screen Reader (NVDA gratis)
# - Download NVDA
# - Attiva con NVDA+N
# - Naviga nel plugin
# - Verifica annunci corretti

# 3. Browser DevTools
# - Chrome > Elements > Accessibility tab
# - Verifica ARIA attributes
# - Controlla Accessibility Tree
```

### Test Modal

```javascript
// In browser console
const { confirm, alert, deleteConfirm } = window.fpPerfSuiteUtils;

// Test confirm
if (await confirm('Test message')) {
    console.log('Confirmed!');
}

// Test alert
await alert('Info message');

// Test deleteConfirm
if (await deleteConfirm('Delete item?')) {
    console.log('Deleted!');
}
```

### Test FormValidator

```php
// test-form-validator.php
require_once 'wp-load.php';

use FP\PerfSuite\Utils\FormValidator;

$validator = FormValidator::validate([
    'email' => 'invalid-email',
    'ttl' => '30', // Troppo basso
], [
    'email' => ['required', 'email'],
    'ttl' => ['required', 'numeric', 'min' => 60],
]);

var_dump($validator->fails()); // true
var_dump($validator->errors()); // Array di errori
var_dump($validator->firstError()); // "Il campo Email deve contenere..."
```

---

## 📚 Riferimenti Rapidi

### File Creati
- `src/Utils/FormValidator.php` - Validazione PHP
- `assets/js/utils/accessibility.js` - Utility accessibilità
- `assets/js/components/modal.js` - Modal personalizzati
- `assets/css/components/modal.css` - Stili modal

### Documentazione
- `ANALISI_OTTIMIZZAZIONI_SUGGERITE.md` - Piano completo
- `OTTIMIZZAZIONI_IMPLEMENTATE_OGGI.md` - Riepilogo sessione 1
- `OTTIMIZZAZIONI_SESSIONE_2_ACCESSIBILITA.md` - Riepilogo accessibilità
- `RIEPILOGO_FINALE_OTTIMIZZAZIONI.md` - Stato finale
- `GUIDA_USO_NUOVE_FUNZIONALITA.md` - Questo documento

---

**Creato da:** AI Assistant  
**Data:** 20 Ottobre 2025  
**Versione:** 1.0  
**Per supporto:** Consulta la documentazione tecnica

