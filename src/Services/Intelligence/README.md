# 🤖 Smart Exclusion Detection System

## Panoramica

Il **Smart Exclusion Detector** è un sistema di intelligenza artificiale che rileva automaticamente:
- URL sensibili da escludere dalla cache
- Script critici da non ottimizzare
- Pattern comuni basati su plugin attivi
- Problemi basati sul comportamento degli utenti

## Come Funziona

### 1. **Rilevamento URL Sensibili**

Il sistema analizza tre livelli:

#### A. **Pattern Standard** (Confidence: 90%)
```php
Rileva automaticamente:
- /cart, /checkout, /payment (E-commerce)
- /account, /profile, /login (User areas)
- /search, /ajax, /api (Dynamic content)
- /wp-admin, /wp-json (WordPress core)
```

#### B. **Plugin-Based Detection** (Confidence: 95%)
```php
Supporta:
- WooCommerce (/cart, /checkout, /my-account, /wc-ajax)
- Easy Digital Downloads (/checkout, /purchase, /edd-ajax)
- LearnDash (/courses, /lessons, /quizzes)
- MemberPress (/account, /membership)
- bbPress (/forums, /topics)
- BuddyPress (/members, /groups, /activity)
```

#### C. **Behavior-Based Detection** (Confidence: 80%)
```php
Analizza database per trovare:
- Pagine con errori frequenti (>3 errori in 7 giorni)
- Pagine con performance instabili
- Pattern di errore comuni
```

### 2. **Rilevamento Script Critici**

#### A. **Script Core** (Always Exclude)
```php
jQuery, jQuery Migrate, WP Polyfill
Stripe, PayPal, reCAPTCHA
Analytics, GTM, Facebook Pixel
LiveChat, Intercom, Crisp
```

#### B. **Plugin-Specific Scripts**
```php
WooCommerce: wc-*, woocommerce*
EDD: edd-*
LearnDash: learndash*
MemberPress: mepr-*
```

#### C. **Dependency Analysis**
```php
Script con >= 3 dipendenze → Probabilmente critico
Analisi grafo delle dipendenze
Detection cicli di dipendenza
```

### 3. **Analisi Contenuto Pagina**

```php
Rileva nel contenuto HTML:
- Form (confidence +30%)
- Payment keywords (confidence +40%)
- User data keywords (confidence +30%)
```

## Utilizzo

### Dashboard Admin

#### Settings Page
```php
// Rilevamento automatico
POST: auto_detect_exclusions
→ Mostra suggerimenti con confidence score

// Applicazione automatica
POST: auto_apply_exclusions
→ Applica solo esclusioni con confidence >= 80%
```

#### Assets Page
```php
// Rileva script critici
POST: auto_detect_scripts
→ Mostra script categorizzati per tipo
```

### Programmatico

```php
use FP\PerfSuite\Services\Intelligence\SmartExclusionDetector;

$detector = new SmartExclusionDetector();

// Rileva URL sensibili
$urls = $detector->detectSensitiveUrls();

// Rileva script critici
$scripts = $detector->detectCriticalScripts();

// Analizza pagina specifica
$analysis = $detector->analyzePage('https://example.com/checkout');

// Applica automaticamente (dry run)
$result = $detector->autoApplyExclusions(true);

// Applica automaticamente (real)
$result = $detector->autoApplyExclusions(false);
```

## Confidence Scores

### Interpretazione
- **90-100%**: Estremamente sicuro - Applica immediatamente
- **80-89%**: Alta confidence - Generalmente sicuro
- **70-79%**: Media confidence - Richiede revisione
- **< 70%**: Bassa confidence - Solo suggerimento

### Calcolo
```php
Confidence = Base_Score + Modifiers

Base Scores:
- Pattern standard: 0.9
- Plugin attivo: 0.95
- Behavior-based: 0.8

Modifiers:
- Has forms: +0.3
- Has payment: +0.4
- Has user data: +0.3
- Frequent errors: +0.2
```

## Database Schema

### Performance History
```sql
CREATE TABLE wp_fp_ps_performance_history (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    url VARCHAR(255),
    load_time FLOAT,
    db_queries INT,
    memory_usage BIGINT,
    has_error TINYINT(1),
    created_at DATETIME
);
```

## Algorithm Flow

```
1. SCAN SITE
   ↓
2. DETECT PATTERNS
   ├─ Standard Sensitive URLs
   ├─ Plugin-Based URLs
   └─ Behavior Analysis
   ↓
3. CALCULATE CONFIDENCE
   ├─ Pattern matching
   ├─ Content analysis
   └─ Historical data
   ↓
4. CATEGORIZE
   ├─ High confidence (>= 80%)
   ├─ Medium confidence (70-79%)
   └─ Low confidence (< 70%)
   ↓
5. SUGGEST / AUTO-APPLY
   ├─ Auto-apply: >= 80%
   ├─ Suggest: 70-79%
   └─ Ignore: < 70%
```

## Pattern Detection Rules

### E-commerce Detection
```php
IF (
    WooCommerce ACTIVE OR EDD ACTIVE
    OR URL contains ['cart', 'checkout', 'payment']
    OR Content has payment keywords
) THEN
    Exclude from cache
    Confidence: 95%
```

### User Area Detection
```php
IF (
    URL contains ['account', 'profile', 'login', 'dashboard']
    OR Has login forms
    OR Cookie-dependent content
) THEN
    Exclude from cache
    Confidence: 90%
```

### Dynamic Content Detection
```php
IF (
    URL contains ['search', 'filter', 'ajax']
    OR Query string parameters
    OR Real-time data
) THEN
    Exclude from cache
    Confidence: 85%
```

## Machine Learning (Future)

### Planned Features
- [ ] User interaction learning
- [ ] Error pattern recognition
- [ ] A/B testing suggestions
- [ ] Automatic optimization based on metrics
- [ ] Predictive exclusion suggestions

## Best Practices

### 1. Initial Setup
```
1. Run auto-detection
2. Review high-confidence suggestions
3. Apply high-confidence (>= 80%)
4. Manually review medium-confidence
5. Test site functionality
```

### 2. Ongoing Maintenance
```
1. Re-run detection monthly
2. Check behavior-based suggestions
3. Review error logs
4. Adjust confidence thresholds if needed
```

### 3. Custom Rules
```php
// Add custom pattern
add_filter('fp_ps_sensitive_patterns', function($patterns) {
    $patterns[] = '/custom-sensitive-url';
    return $patterns;
});

// Modify confidence threshold
add_filter('fp_ps_confidence_threshold', function($threshold) {
    return 0.85; // Default: 0.8
});
```

## Performance Impact

### Resource Usage
- Detection: ~500ms per scan
- Analysis: ~100ms per URL
- Apply: ~50ms per exclusion

### Caching
- Results cached for 1 hour
- Re-scan on plugin activation/deactivation
- Force re-scan available in admin

## Security

### Data Protection
- No external API calls
- All analysis local
- No sensitive data logged
- Database queries optimized

### Permissions
- Requires `manage_options` capability
- CSRF protection via nonces
- Input sanitization on all fields

## Troubleshooting

### Common Issues

**Q: Detection trova troppi URL**
A: Aumenta confidence threshold o usa filtro custom

**Q: Script critici non rilevati**
A: Aggiungi pattern custom via filtro

**Q: Performance lente**
A: Disabilita behavior-based analysis per siti grandi

**Q: False positives**
A: Rivedi confidence scores e aggiusta soglie

## Changelog

### v1.0.0 (2024-01)
- Initial release
- Standard pattern detection
- Plugin-based detection
- Behavior analysis
- Auto-apply feature

## Credits

Developed by Francesco Passeri
https://francescopasseri.com

## License

GPL-2.0+
