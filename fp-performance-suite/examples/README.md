# Examples Directory - FP Performance Suite

Ready-to-use code examples for common integration scenarios.

## 📁 Available Examples

### 1. Custom Logging Integration
**File**: `01-custom-logging-integration.php`

Examples included:
- ✅ Sentry integration
- ✅ Slack notifications
- ✅ Custom log files
- ✅ Email critical errors only

**How to use**: Copy code to `functions.php` or custom plugin

---

### 2. CDN Integrations
**File**: `02-cdn-integrations.php`

Examples included:
- ✅ CloudFlare full setup
- ✅ Auto-purge on cache clear
- ✅ Purge specific files
- ✅ Custom CDN provider
- ✅ Domain sharding
- ✅ Exclude files from CDN

**How to use**: Adapt to your CDN provider

---

### 3. Performance Monitoring
**File**: `03-performance-monitoring.php`

Examples included:
- ✅ Track custom operations
- ✅ WooCommerce integration
- ✅ Database query monitoring
- ✅ Google Analytics integration
- ✅ Performance degradation alerts
- ✅ Custom dashboard widget

**How to use**: Pick what you need, customize

---

### 4. Event System Usage
**File**: `04-event-system-usage.php`

Examples included:
- ✅ Listen to events
- ✅ Dispatch custom events
- ✅ Event history tracking
- ✅ Conditional listeners
- ✅ Priority-based listeners

**How to use**: Build event-driven features

---

### 5. WP-CLI Automation
**File**: `05-automation-with-wpcli.sh`

Scripts included:
- ✅ Daily maintenance
- ✅ Weekly WebP conversion
- ✅ Monthly deep clean
- ✅ Pre-deploy baseline
- ✅ Performance report generator
- ✅ CI/CD integration
- ✅ Multi-site batch operations

**How to use**: Make executable and add to cron

---

### 6. Custom Integrations
**File**: `06-custom-integrations.php`

Examples included:
- ✅ Redis object cache
- ✅ New Relic APM
- ✅ Elasticsearch logging
- ✅ Custom cache backend (Memcached)
- ✅ Datadog APM
- ✅ Prometheus metrics export
- ✅ Webhook on score changes

**How to use**: Advanced integrations

---

## 🚀 Quick Start

### 1. Choose Example
Browse files above and pick what you need

### 2. Copy Code
```php
// In your theme's functions.php
// Or create mu-plugin: wp-content/mu-plugins/fp-custom.php

// Paste example code here
```

### 3. Customize
- Replace webhook URLs
- Add your API keys
- Adjust to your needs

### 4. Test
```bash
wp fp-performance info  # Verify plugin loaded
# Test your customization
```

---

## 💡 Common Use Cases

### Use Case 1: "I want Slack notifications for errors"
→ Use `01-custom-logging-integration.php` - Example 2

### Use Case 2: "I need CloudFlare CDN integration"
→ Use `02-cdn-integrations.php` - Example 1

### Use Case 3: "I want daily automated maintenance"
→ Use `05-automation-with-wpcli.sh` - Example 1

### Use Case 4: "I need performance monitoring in Google Analytics"
→ Use `03-performance-monitoring.php` - Example 4

### Use Case 5: "I want to track custom events"
→ Use `04-event-system-usage.php` - Example 2

---

## 🎓 Learning Path

### Beginner:
1. Start with `01-custom-logging-integration.php`
2. Try `05-automation-with-wpcli.sh` scripts
3. Add simple hooks from examples

### Intermediate:
1. Implement `03-performance-monitoring.php`
2. Setup `02-cdn-integrations.php`
3. Use Event system from `04-event-system-usage.php`

### Advanced:
1. Custom backends from `06-custom-integrations.php`
2. Build your own integrations based on examples
3. Combine multiple examples

---

## 📚 Related Documentation

- **HOOKS.md** - Complete hooks reference
- **DEVELOPER_GUIDE.md** - Detailed integration guide
- **QUICK_START_v1.1.0.md** - Quick start guide

---

## 💬 Need Help?

- Check main documentation
- GitHub Issues: https://github.com/franpass87/FP-Performance/issues
- Email: info@francescopasseri.com

---

**Happy Integrating! 🚀**
