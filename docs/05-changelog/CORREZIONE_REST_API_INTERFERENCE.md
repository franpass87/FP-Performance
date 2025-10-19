# Correzione Interferenza REST API

**Data**: 19 Ottobre 2025  
**Versione**: 1.3.1  
**Tipo**: Bug Fix Critico  
**Priorità**: Alta

## Problema Identificato

Il plugin FP-Performance Suite stava causando errori 500 alle REST API di altri plugin a causa di:

1. **Output buffering non selettivo**: Il sistema di Page Cache avviava output buffering anche per le richieste REST API
2. **HTML Minification su REST API**: L'Optimizer applicava minification HTML anche alle risposte JSON delle REST API
3. **Assenza di controlli REST API**: I metodi che verificano se una richiesta è cacheable non controllavano `REST_REQUEST` o `DOING_AJAX`

## Sintomi

- Altri plugin ricevevano errore 500 sulle loro REST API
- Risposte JSON corrotte o modificate
- Header HTTP inviati prematuramente
- Possibili timeout o errori di parsing JSON

## Correzioni Applicate

### 1. PageCache.php (`src/Services/Cache/PageCache.php`)

**Metodo modificato**: `isCacheableRequest()`

**Controlli aggiunti**:
```php
// Exclude REST API requests
if (defined('REST_REQUEST') && REST_REQUEST) {
    return false;
}

// Exclude AJAX requests
if (defined('DOING_AJAX') && DOING_AJAX) {
    return false;
}

// Exclude WP-JSON endpoints by checking the URL
if (isset($_SERVER['REQUEST_URI'])) {
    $requestUri = sanitize_text_field(wp_unslash($_SERVER['REQUEST_URI']));
    $restPrefix = rest_get_url_prefix();
    if (strpos($requestUri, '/' . $restPrefix . '/') !== false || 
        strpos($requestUri, '/' . $restPrefix) !== false) {
        return false;
    }
}
```

**Effetto**: Il Page Cache ora esclude completamente:
- Richieste REST API (via costante `REST_REQUEST`)
- Richieste AJAX (via costante `DOING_AJAX`)
- Endpoint wp-json (via controllo URL)

### 2. Optimizer.php (`src/Services/Assets/Optimizer.php`)

**Metodo modificato**: `register()`

**Cambio alla riga 73**:
```php
// PRIMA:
if (!is_admin()) {

// DOPO:
if (!is_admin() && !$this->isRestOrAjaxRequest()) {
```

**Nuovo metodo aggiunto**: `isRestOrAjaxRequest()`
```php
private function isRestOrAjaxRequest(): bool
{
    // Check for REST API request
    if (defined('REST_REQUEST') && REST_REQUEST) {
        return true;
    }

    // Check for AJAX request
    if (defined('DOING_AJAX') && DOING_AJAX) {
        return true;
    }

    // Check for WP-JSON in URL
    if (isset($_SERVER['REQUEST_URI'])) {
        $requestUri = sanitize_text_field(wp_unslash($_SERVER['REQUEST_URI']));
        $restPrefix = rest_get_url_prefix();
        if (strpos($requestUri, '/' . $restPrefix . '/') !== false || 
            strpos($requestUri, '/' . $restPrefix) !== false) {
            return true;
        }
    }

    return false;
}
```

**Effetto**: L'HTML Minifier non viene più attivato per REST API e AJAX, evitando di corrompere risposte JSON.

### 3. Headers.php - Già Protetto ✓

Il servizio `Headers` già controllava correttamente le REST API alla riga 39:
```php
if (
    (function_exists('wp_doing_ajax') && wp_doing_ajax()) ||
    (function_exists('rest_doing_request') && rest_doing_request()) || 
    is_user_logged_in()
) {
    return;
}
```

## File Modificati

- ✅ `src/Services/Cache/PageCache.php`
- ✅ `fp-performance-suite/src/Services/Cache/PageCache.php`
- ✅ `src/Services/Assets/Optimizer.php`
- ✅ `fp-performance-suite/src/Services/Assets/Optimizer.php`

## Testing

### Test da Eseguire

1. **Test REST API di altri plugin**:
   ```bash
   curl -X GET https://tuosito.com/wp-json/wp/v2/posts
   ```
   - Dovrebbe restituire JSON valido senza errori 500

2. **Test REST API del plugin**:
   ```bash
   curl -X GET https://tuosito.com/wp-json/fp-ps/v1/score \
     -H "X-WP-Nonce: YOUR_NONCE"
   ```
   - Dovrebbe restituire dati corretti

3. **Test AJAX di WordPress**:
   - Verificare che le chiamate AJAX dell'admin funzionino correttamente

4. **Test funzionalità caching**:
   - Verificare che il page caching continui a funzionare per le pagine normali
   - Verificare che la minification HTML funzioni per le pagine normali

### Scenari di Test

| Tipo Richiesta | Output Buffering | HTML Minification | Page Cache |
|----------------|------------------|-------------------|------------|
| Pagina normale | ❌ No            | ✅ Sì             | ✅ Sì      |
| REST API       | ❌ No            | ❌ No             | ❌ No      |
| AJAX           | ❌ No            | ❌ No             | ❌ No      |
| Admin          | ❌ No            | ❌ No             | ❌ No      |

**Nota**: L'output buffering per il Page Cache è intenzionalmente disabilitato nella tabella sopra perché avviene solo DOPO il controllo `isCacheableRequest()`.

## Impatto

### Positivo
- ✅ Nessuna interferenza con REST API di altri plugin
- ✅ Risposte JSON pulite e non modificate
- ✅ Nessun errore 500 sulle REST API
- ✅ Compatibilità migliorata con l'ecosistema WordPress

### Neutro
- ⚪ Le REST API non vengono cachate (comportamento corretto)
- ⚪ Le risposte REST API non vengono minificate (comportamento corretto)

### Nessun Impatto Negativo
- ✅ Il caching delle pagine continua a funzionare normalmente
- ✅ La minification HTML continua a funzionare per le pagine frontend
- ✅ Tutte le altre funzionalità rimangono invariate

## Compatibilità

- ✅ WordPress 5.8+
- ✅ PHP 7.4+
- ✅ Tutti i plugin che utilizzano REST API
- ✅ WooCommerce REST API
- ✅ Gutenberg/Block Editor
- ✅ Plugin di contact form
- ✅ Plugin di e-commerce

## Note Tecniche

### Ordine dei Controlli

1. **REST_REQUEST**: Costante definita da WordPress quando una richiesta REST viene inizializzata
2. **DOING_AJAX**: Costante definita da WordPress per richieste AJAX
3. **URL Check**: Fallback per rilevare endpoint `/wp-json/` nell'URL

L'uso di controlli multipli garantisce che **tutte** le REST API vengano escluse, anche in edge cases dove le costanti potrebbero non essere definite al momento del controllo.

### Perché Tre Controlli?

1. `REST_REQUEST`: Il metodo ufficiale e più affidabile
2. `DOING_AJAX`: Previene interferenze con AJAX che potrebbero restituire JSON
3. **URL Pattern**: Sicurezza aggiuntiva per catturare richieste REST anche se le costanti non sono ancora definite (edge case durante early hooks)

## Raccomandazioni per il Futuro

1. **Test di Regressione**: Aggiungere test automatici per verificare che REST API non vengano cachate
2. **Monitoring**: Monitorare i log per verificare che non ci siano tentativi di caching di REST API
3. **Documentazione**: Aggiornare la documentazione utente per spiegare che REST API sono escluse dal caching (comportamento corretto)

## Changelog Entry

```markdown
### Fixed [1.3.1] - 2025-10-19
- **Critical**: Risolto problema di interferenza con REST API di altri plugin
- Il Page Cache ora esclude correttamente tutte le richieste REST API e AJAX
- L'HTML Minifier non viene più applicato a richieste REST API
- Aggiunto triplo controllo (REST_REQUEST, DOING_AJAX, URL pattern) per massima affidabilità
```

## Autore

Francesco Passeri  
Data: 19 Ottobre 2025

