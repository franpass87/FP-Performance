# Utilities JavaScript - FP Performance Suite

## BulkProcessor

Sistema generico e riutilizzabile per gestire operazioni bulk con progress bar e polling.

### Caratteristiche

- ✅ Progress bar animata con aggiornamenti in tempo reale
- ✅ Polling automatico dello stato
- ✅ Gestione errori con retry
- ✅ Callbacks personalizzabili
- ✅ Supporto per cancellazione operazioni
- ✅ Completamente configurabile

### Utilizzo Base

```javascript
import { BulkProcessor } from '../utils/bulk-processor.js';

// Seleziona gli elementi
const container = document.querySelector('.my-card');
const button = document.querySelector('#my-button');

// Crea il processor
const processor = new BulkProcessor({
    container: container,
    button: button,
    startAction: 'my_ajax_start_action',    // Azione AJAX per iniziare
    statusAction: 'my_ajax_status_action',  // Azione AJAX per controllare lo stato
    nonce: 'my_nonce',
    statusNonce: 'my_status_nonce',
    labels: {
        starting: 'Avvio operazione...',
        inProgress: 'Elaborazione...',
        completed: 'Completato!',
        noItems: 'Nessun elemento',
        buttonIdle: 'Avvia',
        buttonProcessing: 'Elaborazione...'
    }
});

// Avvia l'operazione
button.addEventListener('click', () => {
    processor.start({
        limit: 50,          // Parametri custom
        offset: 0
    });
});
```

### Configurazione Avanzata

```javascript
const processor = new BulkProcessor({
    // ... configurazione base ...
    
    pollInterval: 2000,     // Intervallo di polling in ms (default: 2000)
    maxErrors: 3,           // Errori consecutivi prima di abortire (default: 3)
    
    // Callbacks
    onProgress: (status) => {
        console.log(`Progresso: ${status.percent}%`);
    },
    
    onComplete: () => {
        console.log('Operazione completata!');
        // Logica custom dopo il completamento
    },
    
    onError: (error) => {
        console.error('Errore:', error);
        // Gestione errori custom
    }
});

// Personalizza la formattazione dell'etichetta
processor.formatProgressLabel = function(status) {
    return `${status.processed}/${status.total} (${status.converted} successi)`;
};
```

### Formato Risposta AJAX Richiesto

#### Start Action Response

```json
{
    "success": true,
    "data": {
        "queued": true,
        "total": 100,
        "error": null
    }
}
```

Se `queued` è `false`, l'operazione termina immediatamente mostrando il messaggio "noItems".

#### Status Action Response

```json
{
    "success": true,
    "data": {
        "active": true,
        "processed": 45,
        "total": 100,
        "converted": 40,
        "percent": 45
    }
}
```

Quando `active` è `false`, l'operazione viene considerata completata.

### Metodi Pubblici

- `start(params)` - Avvia l'operazione con parametri custom
- `cancel()` - Annulla l'operazione in corso
- `formatProgressLabel(status)` - Formatta l'etichetta (può essere sovrascritto)

### Esempio Completo: WebP Conversion

```javascript
import { BulkProcessor } from '../utils/bulk-processor.js';

export function initWebPBulkConvert() {
    const form = document.querySelector('#fp-ps-webp-bulk-form');
    const btn = document.querySelector('#fp-ps-webp-bulk-btn');
    const container = form.closest('.fp-ps-card');
    
    const processor = new BulkProcessor({
        container: container,
        button: btn,
        startAction: 'fp_ps_webp_bulk_convert',
        statusAction: 'fp_ps_webp_queue_status',
        nonce: form.querySelector('[name="nonce"]').value,
        statusNonce: btn.dataset.statusNonce,
        labels: {
            starting: 'Avvio conversione WebP...',
            completed: 'Conversione completata!',
            buttonIdle: 'Avvia Conversione'
        }
    });
    
    // Personalizza formato label
    processor.formatProgressLabel = function(status) {
        return `${status.processed}/${status.total} immagini (${status.converted} convertite)`;
    };
    
    // Avvia con parametri dal form
    btn.addEventListener('click', (e) => {
        e.preventDefault();
        processor.start({
            limit: parseInt(form.querySelector('[name="bulk_limit"]').value),
            offset: parseInt(form.querySelector('[name="bulk_offset"]').value)
        });
    });
}
```

### Compatibilità

Il BulkProcessor è compatibile con tutti i browser moderni ed è progettato per funzionare perfettamente con WordPress AJAX.

### Best Practices

1. **Sempre verificare nonce**: Controlla i nonce sia lato client che server
2. **Gestire timeout**: Il server deve gestire timeout lunghi per operazioni bulk
3. **Chunk processing**: Processa gli elementi in batch sul server per evitare timeout
4. **Progress persistente**: Salva lo stato nel database per sopravvivere a ricaricamenti
5. **Error handling**: Gestisci gli errori con retry logic e messaggi chiari

### Integrazione con WordPress

#### PHP - Handler Start Action

```php
public function startBulkOperation(): void
{
    check_ajax_referer('my_nonce', 'nonce');
    
    if (!current_user_can('manage_options')) {
        wp_send_json_error('Permessi insufficienti', 403);
        return;
    }
    
    try {
        $limit = isset($_POST['limit']) ? (int) $_POST['limit'] : 20;
        $result = $this->processor->bulkProcess($limit);
        
        wp_send_json_success([
            'queued' => !empty($result['queued']),
            'total' => $result['total'],
            'error' => $result['error'] ?? null
        ]);
    } catch (\Exception $e) {
        wp_send_json_error('Errore: ' . $e->getMessage(), 500);
    }
}
```

#### PHP - Handler Status Action

```php
public function getStatus(): void
{
    check_ajax_referer('my_status_nonce', 'nonce');
    
    if (!current_user_can('manage_options')) {
        wp_send_json_error('Permessi insufficienti', 403);
        return;
    }
    
    try {
        $state = $this->processor->getState();
        
        if ($state === null) {
            wp_send_json_success([
                'active' => false,
                'processed' => 0,
                'total' => 0,
                'percent' => 100
            ]);
            return;
        }
        
        $percent = $state['total'] > 0 
            ? round(($state['processed'] / $state['total']) * 100)
            : 0;
        
        wp_send_json_success([
            'active' => true,
            'processed' => $state['processed'],
            'converted' => $state['converted'],
            'total' => $state['total'],
            'percent' => $percent
        ]);
    } catch (\Exception $e) {
        wp_send_json_error('Errore: ' . $e->getMessage(), 500);
    }
}
```

### Troubleshooting

**Progress bar non si aggiorna**
- Verifica che la status action ritorni i dati corretti
- Controlla la console per errori JavaScript
- Verifica che il polling non sia bloccato da errori di rete

**Operazione non parte**
- Verifica i nonce nel form
- Controlla i permessi utente
- Verifica che le azioni AJAX siano registrate correttamente

**Errori consecutivi**
- Aumenta `maxErrors` se la rete è instabile
- Verifica che il server non vada in timeout
- Controlla i log PHP per errori sul backend
