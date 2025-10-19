# 🚨 Soluzione "Too Many Redirects"

**Data**: 19 Ottobre 2025  
**Problema**: Loop di reindirizzamenti infiniti  
**Plugin**: FP Performance Suite

---

## 🎯 Cause Possibili

Il plugin **NON ha redirect nel codice**, ma può causare loop se:

1. ✅ **Page Cache** salva una pagina che contiene un redirect
2. ✅ **HTTPS/SSL** non configurato correttamente
3. ✅ **Conflitto .htaccess** con altre regole
4. ✅ **CDN/Cloudflare** con regole SSL/rewrite
5. ✅ **Altro plugin** che gestisce redirect

---

## 🔧 Soluzione Rapida (PROVARE NELL'ORDINE)

### 1️⃣ **Disabilita Temporaneamente la Page Cache**

```php
// Aggiungi in wp-config.php
define('DONOTCACHEPAGE', true);
```

Oppure vai in:
- **Dashboard WordPress** → **FP Performance** → **Cache** → **Disabilita Page Cache**

---

### 2️⃣ **Svuota Completamente la Cache**

```bash
# Via WP-CLI (se disponibile)
wp fp-ps cache:purge

# Via file system
rm -rf wp-content/cache/fp-performance-suite/*
```

Oppure:
- **Dashboard WordPress** → **FP Performance** → **Cache** → **Svuota Cache**

---

### 3️⃣ **Verifica Impostazioni HTTPS nel wp-config.php**

```php
// Aggiungi/verifica in wp-config.php

// Se il sito è dietro un proxy/CDN (es. Cloudflare)
if (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && 
    $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') {
    $_SERVER['HTTPS'] = 'on';
}

// Se usi Cloudflare
if (isset($_SERVER['HTTP_CF_VISITOR'])) {
    $visitor = json_decode($_SERVER['HTTP_CF_VISITOR'], true);
    if (isset($visitor['scheme']) && $visitor['scheme'] === 'https') {
        $_SERVER['HTTPS'] = 'on';
    }
}
```

---

### 4️⃣ **Controlla URL di WordPress**

```php
// In wp-config.php - DEVONO usare lo STESSO protocollo
define('WP_HOME', 'https://tuosito.com');      // ← HTTPS
define('WP_SITEURL', 'https://tuosito.com');   // ← HTTPS

// ❌ SBAGLIATO (causa loop):
// WP_HOME con HTTPS, ma SITEURL con HTTP
```

---

### 5️⃣ **Disabilita Cloudflare/CDN Temporaneamente**

Se usi Cloudflare:
- Dashboard Cloudflare → **SSL/TLS** → Imposta su **"Full"** (non "Flexible")
- Oppure disabilita temporaneamente il proxy (nuvola grigia)

---

### 6️⃣ **Controlla .htaccess**

Apri `.htaccess` e cerca regole duplicate di redirect HTTPS:

```apache
# ❌ CATTIVO - regole duplicate che causano loop
RewriteCond %{HTTPS} off
RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]

# Più avanti nel file...
RewriteCond %{HTTPS} !=on
RewriteRule ^(.*)$ https://%{HTTP_HOST}/$1 [R=301,L]
# ← DUPLICATO! Rimuovilo
```

**Soluzione**: Lascia **solo una** regola di redirect HTTPS.

```apache
# ✅ BUONO - singola regola HTTPS
<IfModule mod_rewrite.c>
RewriteEngine On
RewriteCond %{HTTPS} off
RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]
</IfModule>
```

---

### 7️⃣ **Disabilita Temporaneamente Altri Plugin di Cache/Redirect**

Disabilita temporaneamente:
- ✅ **WP Rocket**
- ✅ **W3 Total Cache**
- ✅ **LiteSpeed Cache**
- ✅ **Yoast SEO** (redirect premium)
- ✅ **Redirection**
- ✅ **Really Simple SSL**

Riattivali uno alla volta per identificare il conflitto.

---

## 🛠️ Script di Debug

### Test 1: Verifica Response Headers

Salva come `test-redirect-loop.php` nella root di WordPress:

```php
<?php
/**
 * Test Redirect Loop - FP Performance Suite
 */

// Disabilita cache per questo test
define('DONOTCACHEPAGE', true);

require_once('wp-load.php');

header('Content-Type: text/plain');

echo "========================================\n";
echo "TEST REDIRECT LOOP\n";
echo "========================================\n\n";

// 1. Check HTTPS
echo "1. HTTPS Status:\n";
echo "   - is_ssl(): " . (is_ssl() ? 'YES' : 'NO') . "\n";
echo "   - \$_SERVER['HTTPS']: " . (isset($_SERVER['HTTPS']) ? $_SERVER['HTTPS'] : 'NOT SET') . "\n";
echo "   - HTTP_X_FORWARDED_PROTO: " . (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) ? $_SERVER['HTTP_X_FORWARDED_PROTO'] : 'NOT SET') . "\n\n";

// 2. Check URLs
echo "2. WordPress URLs:\n";
echo "   - WP_HOME: " . (defined('WP_HOME') ? WP_HOME : get_option('home')) . "\n";
echo "   - WP_SITEURL: " . (defined('WP_SITEURL') ? WP_SITEURL : get_option('siteurl')) . "\n";
echo "   - Current URL: " . (is_ssl() ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] . "\n\n";

// 3. Check Page Cache
echo "3. FP Performance Suite - Page Cache:\n";
$cacheSettings = get_option('fp_ps_page_cache', []);
echo "   - Enabled: " . (!empty($cacheSettings['enabled']) ? 'YES' : 'NO') . "\n";
echo "   - Cache Dir: " . WP_CONTENT_DIR . '/cache/fp-performance-suite/page-cache' . "\n";

// 4. Check for redirect loops in .htaccess
echo "\n4. .htaccess Check:\n";
$htaccess = ABSPATH . '.htaccess';
if (file_exists($htaccess)) {
    $content = file_get_contents($htaccess);
    $redirectCount = substr_count($content, 'RewriteRule') + substr_count($content, 'Redirect');
    echo "   - Redirect Rules Found: $redirectCount\n";
    
    // Check for duplicate HTTPS redirects
    $httpsRedirects = preg_match_all('/RewriteCond.*HTTPS/i', $content);
    echo "   - HTTPS Redirect Rules: $httpsRedirects\n";
    if ($httpsRedirects > 1) {
        echo "   ⚠️  WARNING: Multiple HTTPS redirect rules detected!\n";
    }
} else {
    echo "   - .htaccess not found\n";
}

// 5. Check active plugins that might cause redirects
echo "\n5. Potential Conflicting Plugins:\n";
$conflictingPlugins = [
    'really-simple-ssl/rlrsssl-really-simple-ssl.php',
    'wp-force-ssl/wp-force-ssl.php',
    'redirection/redirection.php',
    'wordpress-https/wordpress-https.php',
];

foreach ($conflictingPlugins as $plugin) {
    if (is_plugin_active($plugin)) {
        echo "   ⚠️  " . dirname($plugin) . " is ACTIVE\n";
    }
}

echo "\n========================================\n";
echo "TEST COMPLETATO\n";
echo "========================================\n";
```

Esegui visitando: `https://tuosito.com/test-redirect-loop.php`

---

### Test 2: Traccia Request Headers

```bash
# Test con curl (da terminale)
curl -I https://tuosito.com 2>&1 | grep -i "location\|http"

# Test con redirect limit
curl -L --max-redirs 5 -I https://tuosito.com
```

---

## 📋 Checklist Completa

- [ ] Cache del plugin svuotata
- [ ] Page Cache disabilitata temporaneamente
- [ ] `WP_HOME` e `WP_SITEURL` usano lo stesso protocollo
- [ ] HTTPS configurato correttamente in wp-config.php
- [ ] Cloudflare/CDN su SSL "Full" (non "Flexible")
- [ ] .htaccess senza regole duplicate
- [ ] Altri plugin di cache/redirect disabilitati
- [ ] Cookie del browser cancellati

---

## 🎯 Soluzione Definitiva

Dopo aver identificato la causa:

### Se è la Page Cache:

```php
// Aggiungi l'URL problematico alle esclusioni

// In: Dashboard → FP Performance → Cache → Escludi URL
/pagina-problematica
/altro-url

// Oppure via codice:
add_filter('fp_ps_page_cache_exclude_urls', function($urls) {
    $urls[] = '/pagina-problematica';
    return $urls;
});
```

### Se è HTTPS/CDN:

```php
// wp-config.php - aggiungi PRIMA di "require_once(ABSPATH . 'wp-settings.php');"

// Cloudflare/Proxy SSL
if (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && 
    $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') {
    $_SERVER['HTTPS'] = 'on';
}

// URL corretti
define('WP_HOME', 'https://tuosito.com');
define('WP_SITEURL', 'https://tuosito.com');
```

---

## 📞 Supporto Aggiuntivo

Se il problema persiste dopo tutti questi passaggi:

1. Attiva **WP_DEBUG** e controlla i log:
```php
// wp-config.php
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
define('WP_DEBUG_DISPLAY', false);
```

2. Controlla: `wp-content/debug.log`

3. Condividi l'output di `test-redirect-loop.php`

---

## 🎓 Note Tecniche

**Perché accade:**
- Il browser segue il redirect → Il server risponde con altro redirect → Loop infinito
- Solitamente causato da disallineamento HTTPS tra livelli (server, proxy, WordPress)

**Limite browser:**
- Chrome/Firefox: max 20 redirect
- Dopo di che: "ERR_TOO_MANY_REDIRECTS"

---

**Ultima modifica**: 19 Ottobre 2025

