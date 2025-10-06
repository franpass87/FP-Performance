# 🚀 Quick Start Guide - FP Performance Suite v1.1.0

Guida rapida per iniziare subito con tutte le nuove funzionalità.

---

## ⚡ Setup Veloce (5 minuti)

### 1. Dopo Installazione/Aggiornamento

```bash
# Via WP-CLI (se disponibile)
wp plugin activate fp-performance-suite
wp fp-performance info

# O via admin
# Plugins → Activate "FP Performance Suite"
```

### 2. Configurazione Base Rapida

```bash
# Applica preset per il tuo hosting
# Admin → FP Performance → Presets → Applica "Generale"

# O via WP-CLI
wp fp-performance score  # Vedi score attuale
```

### 3. Abilita Nuove Features

```php
// In functions.php o via admin
// Admin → FP Performance → Advanced

// Critical CSS
$criticalCss = new \FP\PerfSuite\Services\Assets\CriticalCss();
$criticalCss->update('body{margin:0}...');

// Performance Monitoring
$monitor = \FP\PerfSuite\Services\Monitoring\PerformanceMonitor::instance();
$monitor->update(['enabled' => true, 'sample_rate' => 10]);

// Scheduled Reports
$reports = new \FP\PerfSuite\Services\Reports\ScheduledReports();
$reports->update([
    'enabled' => true,
    'frequency' => 'weekly',
    'recipient' => get_option('admin_email'),
]);
```

---

## 📋 Checklist Primo Setup

### Fase 1: Base (2 min)
- [ ] Attiva plugin
- [ ] Vai su Admin → FP Performance
- [ ] Vedi score attuale
- [ ] Applica preset "Generale"

### Fase 2: Ottimizzazioni (3 min)
- [ ] Cache → Abilita Page Cache (TTL: 3600)
- [ ] Assets → Abilita Defer JS + Minify HTML
- [ ] Media → Abilita WebP (Quality: 80)
- [ ] Database → Esegui cleanup dry-run

### Fase 3: Nuove Features (5 min)
- [ ] Advanced → Configura Critical CSS
- [ ] Advanced → Abilita Performance Monitoring
- [ ] Advanced → Abilita Scheduled Reports
- [ ] Advanced → Configura CDN (se disponibile)

### Fase 4: Verifica (2 min)
- [ ] Dashboard → Vedi score migliorato
- [ ] Strumenti → Salute Sito → Vedi check FP Performance
- [ ] Performance → Vedi metriche (dopo qualche ora)

---

## 🎯 Comandi Essenziali WP-CLI

```bash
# Cache Management
wp fp-performance cache clear
wp fp-performance cache status

# Database Cleanup
wp fp-performance db cleanup --dry-run  # Test sicuro
wp fp-performance db cleanup --scope=revisions,trash_posts  # Cleanup specifico
wp fp-performance db status

# WebP Conversion
wp fp-performance webp convert --limit=100
wp fp-performance webp status

# Performance Check
wp fp-performance score  # Vedi score dettagliato
wp fp-performance info   # Info plugin
```

---

## 💡 Primi 10 Hook da Usare

### 1. Monitoring Errori
```php
add_action('fp_ps_log_error', function($message, $exception) {
    // Invia a Sentry/Bugsnag/Rollbar
    if (function_exists('sentry_capture_exception')) {
        sentry_capture_exception($exception);
    }
}, 10, 2);
```

### 2. Cache Cleared → Warm Up
```php
add_action('fp_ps_cache_cleared', function() {
    $urls = [home_url('/'), home_url('/about/')];
    foreach ($urls as $url) {
        wp_remote_get($url);
    }
});
```

### 3. WebP → CDN Purge
```php
add_action('fp_ps_webp_converted', function($file) {
    // Purge da CDN custom
    wp_remote_request('https://cdn.example.com/purge?file=' . urlencode($file));
});
```

### 4. DB Cleanup → Notification
```php
add_action('fp_ps_db_cleanup_complete', function($results, $dryRun) {
    if (!$dryRun) {
        $total = array_sum(array_column($results, 'deleted'));
        wp_mail(get_option('admin_email'), 
            'DB Cleanup Complete', 
            "Deleted {$total} items"
        );
    }
}, 10, 2);
```

### 5. Rate Limit → Alert
```php
add_action('fp_ps_rate_limit_exceeded', function($action, $data) {
    error_log("⚠️ Rate limit exceeded: {$action}");
}, 10, 2);
```

### 6. Custom Defer Skip
```php
add_filter('fp_ps_defer_skip_handles', function($handles) {
    $handles[] = 'my-critical-script';
    return $handles;
});
```

### 7. Custom Cleanup Scope
```php
add_filter('fp_ps_db_scheduled_scope', function($scope) {
    $scope[] = 'my_custom_cleanup';
    return $scope;
});
```

### 8. Force Enable GZIP (CloudFlare)
```php
add_filter('fp_ps_gzip_enabled', function($enabled) {
    if (isset($_SERVER['HTTP_CF_RAY'])) {
        return true;
    }
    return $enabled;
});
```

### 9. Require Critical CSS in Production
```php
add_filter('fp_ps_require_critical_css', function($required) {
    return wp_get_environment_type() === 'production';
});
```

### 10. CDN Purge All su Deploy
```php
add_action('fp_ps_cdn_purge_all', function($settings) {
    error_log('🌐 CDN purge triggered');
});
```

---

## 🔧 Troubleshooting Rapido

### Logger non logga?
```php
// Imposta log level
\FP\PerfSuite\Utils\Logger::setLevel('DEBUG');
```

### Rate limit troppo stringente?
```php
// Reset manuale
$limiter = new \FP\PerfSuite\Utils\RateLimiter();
$limiter->reset('webp_bulk_convert');

// O via WP-CLI
wp shell
\FP\PerfSuite\Utils\RateLimiter::clearAll();
```

### Cache non funziona?
```bash
wp fp-performance cache status
wp fp-performance cache clear

# Verifica permessi
ls -la wp-content/cache/fp-performance-suite/
```

### WebP non converte?
```bash
wp fp-performance webp status
# Verifica GD/Imagick disponibile
php -m | grep -E "(gd|imagick)"
```

### Performance monitoring non raccoglie dati?
```php
// Aumenta sample rate temporaneamente
$monitor = \FP\PerfSuite\Services\Monitoring\PerformanceMonitor::instance();
$monitor->update(['enabled' => true, 'sample_rate' => 100]); // 100%

// Dopo qualche ora, riporta a 10%
$monitor->update(['sample_rate' => 10]);
```

---

## 📊 Dashboard Tour (Cosa Vedere)

### 1. Dashboard Principale
- **Performance Score**: Dovrebbe essere 80+
- **Active Optimizations**: Lista feature attive
- **Quick Actions**: Scorciatoie
- **Suggestions**: Raccomandazioni

### 2. Performance (NUOVO)
- **Avg Load Time**: Tempo caricamento medio
- **Avg DB Queries**: Query database medie
- **Avg Memory**: Memoria utilizzata
- **30-Day Comparison**: Trend
- **14-Day Trends**: Grafici testuali

### 3. Advanced (NUOVO)
- **Critical CSS**: Editor con preview size
- **CDN Integration**: Setup multi-provider
- **Performance Monitoring**: Config raccolta dati
- **Scheduled Reports**: Setup email automatiche

### 4. Tools → Site Health (WordPress)
- **4 FP Performance checks**:
  - Page Cache status
  - WebP Coverage
  - Database Health
  - Asset Optimization
- **Debug Info tab**: Metriche dettagliate

---

## 🎨 Personalizzazione UI

### Dark Mode
Automatico! Segue preferenze sistema operativo.
Forza dark mode:
```css
/* In custom admin CSS */
body.admin-color-modern {
    color-scheme: dark;
}
```

### Custom CSS per Plugin
```php
add_action('admin_enqueue_scripts', function($hook) {
    if (strpos($hook, 'fp-performance-suite') !== false) {
        wp_add_inline_style('fp-performance-suite-admin', '
            .fp-ps-card { border-left: 4px solid #2271b1; }
        ');
    }
});
```

---

## 📧 Test Email Report

```bash
# Via WP-CLI
wp shell
$reports = new \FP\PerfSuite\Services\Reports\ScheduledReports();
$result = $reports->sendTestReport('your-email@example.com');
print_r($result);
```

---

## 🔍 Monitoring Setup Completo

### 1. Abilita Monitoring
```php
$monitor = \FP\PerfSuite\Services\Monitoring\PerformanceMonitor::instance();
$monitor->update([
    'enabled' => true,
    'sample_rate' => 10,  // 10% requests
    'track_queries' => true,
    'track_memory' => true,
    'track_timing' => true,
]);
```

### 2. Attendi Dati (1-2 ore)

### 3. Vedi Metriche
```bash
wp shell
$monitor = \FP\PerfSuite\Services\Monitoring\PerformanceMonitor::instance();
print_r($monitor->getStats(7));
print_r($monitor->getTrends(14));
```

### 4. Vedi in Admin
**Admin → FP Performance → Performance**

---

## 🎁 Bonus: Integration Examples

### Slack Notifications
```php
add_action('fp_ps_db_cleanup_complete', function($results, $dryRun) {
    if (!$dryRun) {
        $total = array_sum(array_column($results, 'deleted'));
        wp_remote_post('https://hooks.slack.com/services/YOUR/WEBHOOK/URL', [
            'body' => json_encode([
                'text' => "🧹 Database cleanup: {$total} items removed"
            ])
        ]);
    }
}, 10, 2);
```

### Custom Metrics in New Relic
```php
add_action('shutdown', function() {
    if (extension_loaded('newrelic')) {
        $container = \FP\PerfSuite\Plugin::container();
        $scorer = $container->get(\FP\PerfSuite\Services\Score\Scorer::class);
        $score = $scorer->calculate();
        
        newrelic_custom_metric('Custom/FP_Performance_Score', $score['total']);
    }
});
```

---

## 📞 Support & Community

**Autore**: Francesco Passeri
**Email**: info@francescopasseri.com
**Website**: https://francescopasseri.com
**GitHub**: https://github.com/franpass87/FP-Performance

---

## 🎉 Enjoy!

Hai ora il plugin più completo per WordPress performance optimization!

**Pro Tips**:
- Inizia con preset "Generale"
- Monitora per 1 settimana
- Regola in base ai dati
- Usa WP-CLI per automazione
- Integra con i tuoi tools preferiti

**Happy Optimizing! 🚀**
