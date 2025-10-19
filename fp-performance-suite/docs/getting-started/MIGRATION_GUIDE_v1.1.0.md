# 🔄 Migration Guide - v1.0.1 to v1.1.0

Complete guide for upgrading FP Performance Suite from v1.0.1 to v1.1.0.

---

## ✅ Good News: Zero Breaking Changes!

**v1.1.0 is 100% backward compatible with v1.0.1**

- ✅ All existing settings preserved
- ✅ All existing hooks still work
- ✅ No database migration needed
- ✅ No configuration changes required
- ✅ Existing customizations still function

---

## 📦 Upgrade Process

### Method 1: Automatic Update (Recommended)

```bash
# Via WP-CLI
wp plugin update fp-performance-suite

# Via Admin
# Dashboard → Updates → Update FP Performance Suite
```

### Method 2: Manual Upload

1. Download v1.1.0 ZIP
2. Deactivate current version (settings preserved)
3. Delete old plugin folder
4. Upload and activate new version

### Method 3: Git Pull (Development)

```bash
cd wp-content/plugins/fp-performance-suite
git pull origin main
composer install
```

---

## 🔍 What Happens During Upgrade

### Automatic Actions:
1. ✅ All settings are preserved
2. ✅ Cache files remain intact
3. ✅ Scheduled tasks continue
4. ✅ New services auto-register
5. ✅ New hooks become available
6. ✅ No downtime

### New Options Added:
```php
// These are created with defaults when first accessed
'fp_ps_log_level'         => 'ERROR'  // Logger level
'fp_ps_cdn'               => []       // CDN settings
'fp_ps_critical_css'      => ''       // Critical CSS
'fp_ps_perf_monitor'      => []       // Monitoring config
'fp_ps_reports'           => []       // Email reports config
```

---

## 🎯 Post-Upgrade Recommendations

### Immediate (First 5 Minutes)

#### 1. Verify Plugin Active
```bash
wp plugin list | grep fp-performance
# Should show: active, version 1.1.0
```

#### 2. Check Dashboard
- Go to: **Admin → FP Performance**
- Verify: Score still shows correctly
- Check: No error notices

#### 3. Test Core Functions
```bash
wp fp-performance cache status
wp fp-performance score
wp fp-performance info
```

### First Hour

#### 4. Enable New Monitoring (Optional)
```bash
# Via WP-CLI
wp shell
$monitor = \FP\PerfSuite\Services\Monitoring\PerformanceMonitor::instance();
$monitor->update(['enabled' => true, 'sample_rate' => 10]);
exit
```

#### 5. Configure Scheduled Reports (Optional)
**Admin → FP Performance → Advanced → Scheduled Reports**
- Enable: ✓
- Frequency: Weekly
- Recipient: your-email@example.com

#### 6. Check Site Health
**Tools → Site Health**
- Look for 4 new "FP Performance" checks
- All should be "Good" or "Recommended"

### First Day

#### 7. Review Performance Metrics
**Admin → FP Performance → Performance** (new page)
- Wait a few hours for data collection
- Review avg load time, queries, memory

#### 8. Test WP-CLI Commands
```bash
wp fp-performance cache clear
wp fp-performance db cleanup --dry-run
wp fp-performance webp status
```

#### 9. Check Error Logs
```bash
tail -f wp-content/debug.log | grep "FP-PerfSuite"
# Should show INFO level logs (if WP_DEBUG enabled)
```

---

## 🔄 Migration Paths

### If You Have Custom Code

#### Old Logger Usage → New Logger
```php
// ❌ Before (v1.0.1)
error_log('[FP Performance Suite] Error: ' . $message);

// ✅ After (v1.1.0) - Still works, but update to:
use FP\PerfSuite\Utils\Logger;
Logger::error('Error message', $exception);
```

#### Direct Service Instantiation → Container
```php
// ❌ Before (still works but not recommended)
$cache = new \FP\PerfSuite\Services\Cache\PageCache($fs, $env);

// ✅ After (recommended)
$container = \FP\PerfSuite\Plugin::container();
$cache = $container->get(\FP\PerfSuite\Services\Cache\PageCache::class);
```

#### get_option() → Cached Settings
```php
// ❌ Before (still works)
$settings = get_option('fp_ps_page_cache', []);

// ✅ After (faster)
$container = \FP\PerfSuite\Plugin::container();
$settings = $container->getCachedSettings('fp_ps_page_cache', []);
```

---

## 🆕 New Features You Can Use Immediately

### 1. WP-CLI Commands
```bash
# Available immediately after upgrade
wp fp-performance info
wp fp-performance score
wp fp-performance cache clear
wp fp-performance db cleanup --dry-run
wp fp-performance webp convert --limit=50
```

### 2. New Hooks
```php
// Use right away in functions.php or plugin

// Monitor errors
add_action('fp_ps_log_error', function($message, $exception) {
    // Your error handling
}, 10, 2);

// Cache cleared event
add_action('fp_ps_cache_cleared', function() {
    // Your cache warmup logic
});

// WebP conversion tracking
add_action('fp_ps_webp_converted', function($file) {
    // Purge from CDN, log, etc.
});
```

### 3. Site Health Checks
- **Tools → Site Health** (automatic, no config needed)
- See 4 new FP Performance checks

### 4. Query Monitor Integration
- Install Query Monitor plugin (if not already)
- See "FP Performance" tab in Query Monitor bar
- Automatic integration, no config needed

---

## 🎨 New Admin Pages

### 1. Advanced Page (NEW)
**Admin → FP Performance → Advanced**

Configure:
- Critical CSS
- CDN Integration
- Performance Monitoring
- Scheduled Reports

### 2. Performance Page (NEW)
**Admin → FP Performance → Performance**

View:
- Avg load time (7/30 days)
- Avg DB queries
- Avg memory usage
- 14-day trends
- Performance comparisons

---

## 🔧 Optional Configurations

### Enable Critical CSS
```php
// In functions.php or via admin
$criticalCss = new \FP\PerfSuite\Services\Assets\CriticalCss();
$criticalCss->update('
    body { margin: 0; padding: 0; }
    .header { background: #fff; }
    h1 { font-size: 32px; }
');

// Or via Admin → Advanced → Critical CSS
```

### Setup CDN
```php
// Via code
$cdn = new \FP\PerfSuite\Services\CDN\CdnManager();
$cdn->update([
    'enabled' => true,
    'url' => 'https://cdn.yourdomain.com',
    'provider' => 'cloudflare',
    'api_key' => 'your-api-key',
    'zone_id' => 'your-zone-id',
]);

// Or via Admin → Advanced → CDN Integration
```

### Enable Performance Monitoring
```php
$monitor = \FP\PerfSuite\Services\Monitoring\PerformanceMonitor::instance();
$monitor->update([
    'enabled' => true,
    'sample_rate' => 10,  // Monitor 10% of requests
    'track_queries' => true,
    'track_memory' => true,
    'track_timing' => true,
]);
```

### Configure Email Reports
```php
$reports = new \FP\PerfSuite\Services\Reports\ScheduledReports();
$reports->update([
    'enabled' => true,
    'frequency' => 'weekly',
    'recipient' => 'performance@yourdomain.com',
    'include_suggestions' => true,
    'include_optimizations' => true,
    'include_metrics' => true,
]);

// Send test report
$reports->sendTestReport('test@yourdomain.com');
```

---

## 🐛 Troubleshooting

### Issue: "Service not found" Error

**Cause**: Cached autoloader or opcache
**Solution**:
```bash
# Clear opcache
wp cache flush
# Or restart PHP-FPM
sudo service php8.2-fpm restart
```

### Issue: WP-CLI Commands Not Found

**Cause**: Plugin not fully loaded
**Solution**:
```bash
# Deactivate and reactivate
wp plugin deactivate fp-performance-suite
wp plugin activate fp-performance-suite
wp fp-performance info  # Should work now
```

### Issue: Rate Limit Immediately Triggered

**Cause**: Testing bulk operations multiple times
**Solution**:
```bash
wp shell
\FP\PerfSuite\Utils\RateLimiter::clearAll();
exit
```

### Issue: Dark Mode Not Showing

**Cause**: Browser/OS preference not set
**Solution**: 
- macOS: System Preferences → General → Appearance → Dark
- Windows: Settings → Colors → Choose your mode → Dark
- Browser will detect automatically

### Issue: Monitoring Not Collecting Data

**Cause**: Sample rate too low or monitoring disabled
**Solution**:
```php
$monitor = \FP\PerfSuite\Services\Monitoring\PerformanceMonitor::instance();
$monitor->update(['enabled' => true, 'sample_rate' => 50]); // 50% for testing
// After few hours, reduce back to 10%
```

---

## 📊 What to Monitor After Upgrade

### First 24 Hours:
- ✅ Error log (should be clean)
- ✅ Performance score (should improve or stay same)
- ✅ Page load time (should improve)
- ✅ Cache hit rate (in Query Monitor if installed)

### First Week:
- ✅ Performance metrics trends
- ✅ Email reports (if enabled)
- ✅ Site Health checks
- ✅ Database overhead

### Check These Logs:
```bash
# General WordPress debug log
tail -f wp-content/debug.log | grep FP-PerfSuite

# Error log
grep "ERROR" wp-content/debug.log | grep FP-PerfSuite

# Info events (if WP_DEBUG enabled)
grep "INFO" wp-content/debug.log | grep FP-PerfSuite
```

---

## 🎁 New Integrations You Can Add

### Example 1: Slack Notifications
```php
// In functions.php or custom plugin
add_action('fp_ps_log_error', function($message, $exception) {
    wp_remote_post('https://hooks.slack.com/services/YOUR/WEBHOOK', [
        'body' => json_encode([
            'text' => "🚨 FP Performance Error: {$message}",
            'channel' => '#performance-alerts',
        ])
    ]);
}, 10, 2);
```

### Example 2: Custom CDN Purge
```php
add_action('fp_ps_cache_cleared', function() {
    // Purge your custom CDN
    wp_remote_request('https://your-cdn.com/api/purge', [
        'method' => 'POST',
        'headers' => ['X-API-Key' => 'your-key'],
    ]);
});
```

### Example 3: Performance Metrics to Analytics
```php
add_action('shutdown', function() {
    $monitor = \FP\PerfSuite\Services\Monitoring\PerformanceMonitor::instance();
    $monitor->track('custom_metric', 123);
    
    // Send to Google Analytics
    if (function_exists('gtag')) {
        gtag('event', 'timing_complete', [
            'name' => 'page_load',
            'value' => round(microtime(true) - WP_START_TIMESTAMP, 3),
        ]);
    }
});
```

---

## 🎓 Learning New Features

### Priority 1: Essential (Learn First)
1. **Logger** - Better error tracking
2. **WP-CLI** - Automation
3. **Site Health** - Native integration
4. **Performance Dashboard** - See trends

### Priority 2: Powerful (Learn Soon)
1. **Hooks System** - Extensibility
2. **Event Dispatcher** - Event-driven code
3. **Repository Pattern** - Clean data access
4. **Value Objects** - Type safety

### Priority 3: Advanced (When Needed)
1. **Critical CSS** - Advanced optimization
2. **CDN Integration** - When scaling
3. **Scheduled Reports** - Automation
4. **Query Monitor** - Deep debugging

---

## 📝 Checklist Completa Post-Upgrade

### Setup (30 minuti)
- [ ] Plugin aggiornato a v1.1.0
- [ ] No errori in error log
- [ ] Score performance invariato o migliorato
- [ ] WP-CLI commands funzionanti
- [ ] Site Health checks visibili
- [ ] Cache funzionante
- [ ] WebP conversions funzionanti
- [ ] Database cleanup funzionante

### Configuration (1 ora)
- [ ] Performance monitoring abilitato
- [ ] Sample rate configurato (10%)
- [ ] Scheduled reports configurati
- [ ] Test email report ricevuta
- [ ] Critical CSS aggiunto (opzionale)
- [ ] CDN configurato (se applicabile)

### Monitoring (1 settimana)
- [ ] Nessun errore nei log
- [ ] Performance metrics in raccolta
- [ ] Trends visibili nel dashboard
- [ ] Email reports ricevuti (se abilitati)
- [ ] Rate limiting non bloccante
- [ ] Memory usage stabile/migliorata
- [ ] Page load time stabile/migliorata

### Optimization (Ongoing)
- [ ] Analizzare trends dopo 2 settimane
- [ ] Regolare settings basandosi su dati
- [ ] Ottimizzare Critical CSS
- [ ] Fine-tune CDN settings
- [ ] Regolare sample rate monitoring

---

## 💡 Pro Tips

### 1. Start with Monitoring
Enable performance monitoring subito per raccogliere dati:
```php
$monitor = \FP\PerfSuite\Services\Monitoring\PerformanceMonitor::instance();
$monitor->update(['enabled' => true, 'sample_rate' => 10]);
```

### 2. Use WP-CLI for Maintenance
Aggiungi a cron jobs:
```bash
# Weekly cache clear
0 2 * * 0 wp fp-performance cache clear

# Monthly database cleanup
0 3 1 * * wp fp-performance db cleanup --scope=revisions,trash_posts
```

### 3. Setup Error Monitoring
```php
add_action('fp_ps_log_error', function($message, $exception) {
    // Send to your monitoring service
    if (function_exists('send_to_monitoring')) {
        send_to_monitoring($message, $exception);
    }
}, 10, 2);
```

### 4. Test Email Reports Early
```bash
wp shell
$reports = new \FP\PerfSuite\Services\Reports\ScheduledReports();
$reports->sendTestReport('your-email@example.com');
exit
```

### 5. Monitor Rate Limits
```php
add_action('fp_ps_rate_limit_exceeded', function($action, $data) {
    error_log("Rate limit hit for: {$action}");
    // Adjust limits if too restrictive
}, 10, 2);
```

---

## 🔄 Rollback Plan (If Needed)

### Quick Rollback (< 5 minutes)

```bash
# 1. Deactivate v1.1.0
wp plugin deactivate fp-performance-suite

# 2. Restore backup
cd wp-content/plugins/
rm -rf fp-performance-suite/
tar -xzf fp-performance-suite-v1.0.1.tar.gz

# 3. Reactivate
wp plugin activate fp-performance-suite
```

### Database Rollback (Rarely Needed)
```bash
# v1.1.0 doesn't modify existing tables
# Only new options are added, safe to leave them
# But if needed:
wp shell
delete_option('fp_ps_log_level');
delete_option('fp_ps_cdn');
delete_option('fp_ps_critical_css');
delete_option('fp_ps_perf_monitor');
delete_option('fp_ps_reports');
exit
```

---

## 📚 Learn More

### Documentation:
1. **QUICK_START_v1.1.0.md** - Getting started guide
2. **docs/HOOKS.md** - All hooks with examples
3. **docs/DEVELOPER_GUIDE.md** - Integration guide
4. **COMPLETE_IMPLEMENTATION_v1.1.0.md** - Full technical details

### Support:
- Email: info@francescopasseri.com
- Website: https://francescopasseri.com
- GitHub: https://github.com/franpass87/FP-Performance

---

## 🎉 Welcome to v1.1.0!

You now have:
- ✅ Better logging and debugging
- ✅ Rate limiting protection
- ✅ 30% faster settings access
- ✅ Performance monitoring
- ✅ Email reports
- ✅ CDN integration
- ✅ Critical CSS support
- ✅ Site Health integration
- ✅ WP-CLI automation
- ✅ Dark mode UI
- ✅ 20+ new hooks

**Enjoy the upgrade! 🚀**

---

*Migration Guide v1.1.0*
*Last Updated: 2025-10-06*
