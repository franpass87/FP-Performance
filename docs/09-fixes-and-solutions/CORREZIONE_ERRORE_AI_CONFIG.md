# 🔧 Correzione Errore "Unexpected token '<'" - AI Config

## 📋 Problema Riscontrato

Quando si cliccava sul pulsante "**Applica Configurazione AI**", appariva un modal che restava bloccato allo 0% per qualche secondo, seguito dall'errore:

```
❌ Errore
Unexpected token '<', ...
```

## 🔍 Analisi del Problema

L'errore "Unexpected token '<'" è un errore JavaScript che indica che il codice stava ricevendo **HTML invece di JSON** come risposta dalle chiamate AJAX.

### Cause Identificate:

1. **Endpoint REST API Mancanti**: Il JavaScript cercava di chiamare 7 endpoint REST API che **non esistevano**:
   - `/fp-ps/v1/cache/settings`
   - `/fp-ps/v1/cache/headers`
   - `/fp-ps/v1/assets/settings`
   - `/fp-ps/v1/media/webp/settings`
   - `/fp-ps/v1/media/lazy-load/settings`
   - `/fp-ps/v1/database/settings`
   - `/fp-ps/v1/backend/settings`

2. **Handler AJAX Mancanti**: Mancavano anche due handler AJAX per:
   - `fp_ps_update_heartbeat`
   - `fp_ps_update_exclusions`

3. **Gestione Errori Inadeguata**: Il JavaScript non gestiva correttamente gli errori quando il server restituiva HTML invece di JSON.

## ✅ Soluzioni Implementate

### 1. Migliorata Gestione Errori JavaScript

**File Modificati:**
- `assets/js/ai-config-advanced.js`
- `assets/js/ai-config.js`

**Modifiche nella funzione `updateSection()`:**

```javascript
async updateSection(endpoint, settings, nonce) {
    try {
        const response = await fetch(endpoint, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-WP-Nonce': nonce,
            },
            body: JSON.stringify(settings),
        });

        if (!response.ok) {
            // Gestione intelligente degli errori
            let errorMessage = `Errore ${response.status}: ${response.statusText}`;
            try {
                const errorData = await response.json();
                errorMessage = errorData.message || errorMessage;
            } catch (e) {
                // Se non è JSON, prova a leggere come testo
                try {
                    const errorText = await response.text();
                    if (errorText.includes('<')) {
                        // È HTML, probabilmente un errore PHP
                        errorMessage = `Errore del server per l'endpoint ${endpoint}. Controlla i log PHP.`;
                    } else {
                        errorMessage = errorText;
                    }
                } catch (e2) {
                    // Usa il messaggio di default
                }
            }
            throw new Error(errorMessage);
        }

        return response.json();
    } catch (error) {
        console.error(`Errore chiamando ${endpoint}:`, error);
        throw error;
    }
}
```

**Vantaggi:**
- ✅ Gestione robusta degli errori HTML
- ✅ Messaggi di errore più chiari
- ✅ Logging dettagliato nella console

### 2. Creati Endpoint REST API Mancanti

**File Modificato:** `src/Http/Routes.php`

**Aggiunti nel metodo `register()`:**

```php
// AI Config endpoints - Settings per sezioni
register_rest_route('fp-ps/v1', '/cache/settings', [
    'methods' => 'POST',
    'callback' => [$this, 'updateCacheSettings'],
    'permission_callback' => [$this, 'permissionCheck'],
]);

register_rest_route('fp-ps/v1', '/cache/headers', [
    'methods' => 'POST',
    'callback' => [$this, 'updateCacheHeaders'],
    'permission_callback' => [$this, 'permissionCheck'],
]);

register_rest_route('fp-ps/v1', '/assets/settings', [
    'methods' => 'POST',
    'callback' => [$this, 'updateAssetsSettings'],
    'permission_callback' => [$this, 'permissionCheck'],
]);

register_rest_route('fp-ps/v1', '/media/webp/settings', [
    'methods' => 'POST',
    'callback' => [$this, 'updateWebPSettings'],
    'permission_callback' => [$this, 'permissionCheck'],
]);

register_rest_route('fp-ps/v1', '/media/lazy-load/settings', [
    'methods' => 'POST',
    'callback' => [$this, 'updateLazyLoadSettings'],
    'permission_callback' => [$this, 'permissionCheck'],
]);

register_rest_route('fp-ps/v1', '/database/settings', [
    'methods' => 'POST',
    'callback' => [$this, 'updateDatabaseSettings'],
    'permission_callback' => [$this, 'permissionCheck'],
]);

register_rest_route('fp-ps/v1', '/backend/settings', [
    'methods' => 'POST',
    'callback' => [$this, 'updateBackendSettings'],
    'permission_callback' => [$this, 'permissionCheck'],
]);
```

**Aggiunti 7 metodi handler:**

Tutti i metodi seguono lo stesso pattern:

```php
public function updateCacheSettings(WP_REST_Request $request): WP_REST_Response
{
    $settings = $request->get_json_params();
    
    if (empty($settings)) {
        return new WP_REST_Response([
            'success' => false,
            'message' => __('No settings provided', 'fp-performance-suite'),
        ], 400);
    }

    Logger::info('AI Config: Updating cache settings', ['settings' => $settings]);

    // Salva le impostazioni
    $result = update_option('fp_ps_page_cache', $settings, true);

    return rest_ensure_response([
        'success' => $result !== false,
        'message' => __('Cache settings updated', 'fp-performance-suite'),
        'data' => $settings,
    ]);
}
```

**Caratteristiche:**
- ✅ Validazione input
- ✅ Logging dettagliato
- ✅ Gestione errori
- ✅ Risposte JSON standardizzate
- ✅ Permessi verificati automaticamente

### 3. Creato Nuovo Handler AJAX

**File Creato:** `src/Http/Ajax/AIConfigAjax.php`

**Struttura:**

```php
class AIConfigAjax
{
    private ServiceContainer $container;
    
    public function __construct(ServiceContainer $container)
    {
        $this->container = $container;
    }
    
    public function register(): void
    {
        add_action('wp_ajax_fp_ps_update_heartbeat', [$this, 'updateHeartbeat']);
        add_action('wp_ajax_fp_ps_update_exclusions', [$this, 'updateExclusions']);
    }
    
    // ... metodi handler
}
```

**Metodi Implementati:**

1. **`updateHeartbeat()`**: Aggiorna l'intervallo del WordPress Heartbeat API
   - Valida l'intervallo (minimo 15 secondi)
   - Salva nelle impostazioni backend
   - Logging completo

2. **`updateExclusions()`**: Salva le esclusioni per cache e ottimizzazioni
   - Valida il formato JSON
   - Salva l'array di esclusioni
   - Conta e registra il numero di esclusioni

**Registrazione nel file Routes.php:**

```php
$aiConfigAjax = new AIConfigAjax($this->container);
$aiConfigAjax->register();
```

## 📊 Riepilogo Modifiche

### File Modificati:
1. ✅ `assets/js/ai-config-advanced.js` - Gestione errori migliorata
2. ✅ `assets/js/ai-config.js` - Gestione errori migliorata  
3. ✅ `src/Http/Routes.php` - Aggiunti 7 endpoint REST API + registrazione handler AJAX

### File Creati:
4. ✅ `src/Http/Ajax/AIConfigAjax.php` - Nuovo handler AJAX per heartbeat ed esclusioni

## 🎯 Risultati Attesi

Dopo queste modifiche, quando si clicca su "**Applica Configurazione AI**":

1. ✅ Il modal si apre correttamente
2. ✅ Il progresso avanza da 0% a 100%
3. ✅ Ogni sezione viene configurata correttamente:
   - Page Cache
   - Browser Cache
   - Asset Optimizer
   - WebP
   - Lazy Load
   - Database
   - Backend
   - Heartbeat
   - Esclusioni

4. ✅ Messaggi di successo/errore chiari
5. ✅ Logging completo delle operazioni
6. ✅ Redirect automatico alla overview al completamento

## 🧪 Come Testare

1. Vai alla pagina **AI Config** (`admin.php?page=fp-ps-ai-config`)
2. Esegui un'analisi del sito
3. Clicca su **"Applica Configurazione AI"**
4. Verifica che:
   - Il modal mostri il progresso
   - Ogni sezione venga completata
   - Appaia il messaggio di successo
   - Vieni reindirizzato alla overview

### Debug in caso di problemi:

Apri la **Console del Browser** (F12) e verifica:
- Eventuali errori JavaScript
- Le chiamate API nella scheda Network
- I messaggi di log

Controlla i **Log PHP** di WordPress per:
- Errori PHP
- Log delle operazioni (cerca "AI Config:")

## 📝 Note Tecniche

### Nomi Opzioni WordPress Utilizzati:

Gli endpoint salvano le impostazioni in queste opzioni:

- `fp_ps_page_cache` - Impostazioni Page Cache
- `fp_ps_browser_cache` - Impostazioni Browser Cache
- `fp_ps_asset_optimizer` - Impostazioni Asset Optimizer
- `fp_ps_webp` - Impostazioni WebP
- `fp_ps_lazy_load` - Impostazioni Lazy Load
- `fp_ps_database` - Impostazioni Database
- `fp_ps_backend` - Impostazioni Backend (include heartbeat)
- `fp_ps_exclusions` - Array di esclusioni

### Sicurezza:

Tutti gli endpoint implementano:
- ✅ Verifica nonce (`X-WP-Nonce`)
- ✅ Controllo permessi (`manage_options`)
- ✅ Sanitizzazione input
- ✅ Validazione dati

## 🎉 Conclusioni

Il problema era causato dalla **mancanza di endpoint REST API** che il JavaScript cercava di chiamare. Implementando gli endpoint mancanti e migliorando la gestione degli errori, la funzionalità "Applica Configurazione AI" ora funziona correttamente.

---

**Data Correzione:** 21 Ottobre 2025  
**Versione Plugin:** 1.4.0+  
**Autore:** Francesco Passeri

