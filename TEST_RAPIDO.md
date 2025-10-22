# ⚡ Test Rapido - Fix Pagina Assets

## 🎯 Test in 30 Secondi

### Opzione 1: Test Visuale (CONSIGLIATO)
1. Apri il browser
2. Vai su: `http://tuo-sito.com/wp-admin/`
3. Login come amministratore
4. Clicca: **FP Performance → 📦 Assets**
5. ✅ Se vedi i form e le opzioni = **FUNZIONA!**
6. ❌ Se vedi pagina bianca = **Controlla debug.log**

---

### Opzione 2: Test PHP (Da terminale)

Dalla root del progetto:

```bash
php -r "
define('FP_PERF_SUITE_DIR', __DIR__);
require_once 'src/ServiceContainer.php';
require_once 'src/Plugin.php';

\$container = \FP\PerfSuite\ServiceContainer::getInstance();

// Registra tutti i servizi
\FP\PerfSuite\Plugin::init();
\$container = \FP\PerfSuite\Plugin::container();

// Test servizi
\$tests = [
    'SmartAssetDelivery' => '\FP\PerfSuite\Services\Assets\SmartAssetDelivery',
    'Http2ServerPush' => '\FP\PerfSuite\Services\Assets\Http2ServerPush',
];

foreach (\$tests as \$name => \$class) {
    if (\$container->has(\$class)) {
        try {
            \$service = \$container->get(\$class);
            echo \"✅ \$name: OK\n\";
        } catch (Exception \$e) {
            echo \"❌ \$name: ERRORE - \" . \$e->getMessage() . \"\n\";
        }
    } else {
        echo \"❌ \$name: NON REGISTRATO\n\";
    }
}

echo \"\n✅ TEST COMPLETATO!\n\";
"
```

**Risultato Atteso:**
```
✅ SmartAssetDelivery: OK
✅ Http2ServerPush: OK

✅ TEST COMPLETATO!
```

---

### Opzione 3: Test Console Browser (F12)

1. Apri la pagina Assets nel browser
2. Premi **F12** (Dev Tools)
3. Vai su **Console**
4. Cerca errori JavaScript o PHP
5. ✅ Nessun errore = Tutto OK
6. ❌ Errori presenti = Controlla tipo di errore

**Errori Comuni:**
- `Failed to load resource` → Problema file CSS/JS
- `Uncaught TypeError` → Problema JavaScript
- `500 Internal Server Error` → Problema PHP (controlla debug.log)

---

## 🔍 Quick Check

| Cosa Verificare | Come | Risultato Atteso |
|-----------------|------|------------------|
| Pagina carica? | Apri pagina Assets | Form visibili |
| Tab funzionano? | Clicca sui tab | Contenuto cambia |
| Form salvano? | Salva impostazioni | Messaggio successo |
| Console pulita? | F12 → Console | Nessun errore rosso |

---

## ❌ Se Qualcosa Non Funziona

### 1. Abilita Debug
```php
// wp-config.php
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
define('WP_DEBUG_DISPLAY', false);
```

### 2. Controlla Log
```bash
tail -f wp-content/debug.log
```

### 3. Svuota Cache
- Plugin cache (se presente)
- Ctrl+F5 nel browser
- Svuota OPcache PHP

### 4. Usa Script Diagnostico
```bash
php dev-scripts/diagnose-assets-page.php
```

---

## ✅ Tutto OK?

Se i test sono ✅:
1. Puoi eliminare i file di test:
   - `test-assets-page-fix.php`
   - `FIX_PAGINA_ASSETS_RIEPILOGO.md`
   - `VERIFICA_COMPLETA_FIX_ASSETS.md`
   - `TEST_RAPIDO.md` (questo file)

2. Fai commit:
   ```bash
   git add src/Plugin.php
   git commit -m "Fix: Registrati SmartAssetDelivery e Http2ServerPush nel ServiceContainer"
   git push
   ```

3. 🎉 **Fatto!**

---

**Tempo Stimato**: 30 secondi - 2 minuti  
**Difficoltà**: ⭐ Facile

