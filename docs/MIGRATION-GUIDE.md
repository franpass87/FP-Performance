# FP Performance Suite - Migration Guide

**Versione Target**: 2.0.0  
**Data**: 2025-11-06

Questa guida spiega come migrare gradualmente dal codice esistente alla nuova architettura.

---

## 🎯 STRATEGIA DI MIGRAZIONE

La migrazione avviene in modo **graduale e sicuro**, mantenendo il codice esistente funzionante mentre si migra modulo per modulo.

### Principi

1. **Non rompere nulla**: Il vecchio codice continua a funzionare
2. **Migrazione incrementale**: Un modulo alla volta
3. **Test continuo**: Verificare dopo ogni migrazione
4. **Backward compatible**: Bridge/adapter per transizione

---

## 📋 CHECKLIST MIGRAZIONE

### Per ogni servizio/classe da migrare:

- [ ] Identificare dipendenze dirette (get_option, Logger static, etc.)
- [ ] Creare versione migrata che usa nuove dipendenze
- [ ] Testare in parallelo con vecchio codice
- [ ] Sostituire gradualmente chiamate
- [ ] Rimuovere vecchio codice solo dopo verifica

---

## 🔄 ESEMPI DI MIGRAZIONE

### Esempio 1: Migrare uso di get_option()

**Prima** (codice esistente):
```php
class MyService
{
    public function isEnabled(): bool
    {
        return (bool) get_option('fp_ps_my_feature_enabled', false);
    }
    
    public function enable(): void
    {
        update_option('fp_ps_my_feature_enabled', true);
    }
}
```

**Dopo** (migrato):
```php
use FP\PerfSuite\Core\Options\OptionsRepositoryInterface;

class MyService
{
    private OptionsRepositoryInterface $options;
    
    public function __construct(OptionsRepositoryInterface $options)
    {
        $this->options = $options;
    }
    
    public function isEnabled(): bool
    {
        return (bool) $this->options->get('my_feature_enabled', false);
    }
    
    public function enable(): void
    {
        $this->options->set('my_feature_enabled', true);
    }
}
```

### Esempio 2: Migrare Logger statico

**Prima**:
```php
Logger::error('Something went wrong', $exception);
Logger::info('Operation completed');
```

**Dopo**:
```php
use FP\PerfSuite\Core\Logging\LoggerInterface;

class MyService
{
    private LoggerInterface $logger;
    
    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }
    
    public function doSomething(): void
    {
        try {
            // code
            $this->logger->info('Operation completed');
        } catch (\Throwable $e) {
            $this->logger->error('Something went wrong', [], $e);
        }
    }
}
```

### Esempio 3: Registrare Hook via HookRegistry

**Prima**:
```php
public function init()
{
    add_action('wp_head', [$this, 'outputSomething']);
    add_filter('the_content', [$this, 'filterContent']);
}
```

**Dopo**:
```php
use FP\PerfSuite\Core\Hooks\HookRegistryInterface;

public function boot(Container $container): void
{
    $registry = $container->get(HookRegistryInterface::class);
    $registry->addAction('wp_head', [$this, 'outputSomething'], 10, 1, 'MyService');
    $registry->addFilter('the_content', [$this, 'filterContent'], 10, 1, 'MyService');
}
```

---

## 📦 REGISTRAZIONE SERVIZIO NEL CONTAINER

Quando migri un servizio, registralo nel Service Provider appropriato:

```php
// In AssetServiceProvider.php
public function register(Container $container): void
{
    $container->singleton(
        MyService::class,
        function(Container $c) {
            return new MyService(
                $c->get(OptionsRepositoryInterface::class),
                $c->get(LoggerInterface::class)
            );
        }
    );
}
```

---

## 🧪 TESTING DURANTE MIGRAZIONE

1. **Test parallelo**: Vecchio e nuovo codice devono funzionare insieme
2. **Feature flags**: Usa opzioni per abilitare/disabilitare nuova versione
3. **Logging**: Monitora errori durante transizione
4. **Rollback plan**: Possibilità di tornare indietro rapidamente

---

## ⚠️ RISCHI E MITIGAZIONE

### Rischio: Breaking changes

**Mitigazione**:
- Mantieni vecchio codice finché nuovo non è testato
- Usa adapter pattern per compatibilità
- Test su staging prima di produzione

### Rischio: Opzioni database

**Mitigazione**:
- OptionsRepository mappa automaticamente vecchie chiavi
- OptionsMigrator gestisce migrazione struttura
- Backup database prima di migrazioni

### Rischio: Hook non registrati

**Mitigazione**:
- Mantieni stesso nome hook
- Stessa priorità inizialmente
- Audit completo hook prima rimozione

---

## 📊 PROGRESSO MIGRAZIONE

### Servizi ad alta priorità per migrazione:

1. ✅ Core services (già completati)
2. ⏳ Cache services
3. ⏳ Asset Optimizer
4. ⏳ Database services
5. ⏳ Admin pages

---

**Ultimo aggiornamento**: 2025-11-06









