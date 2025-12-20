# Guida alla Migrazione: Logger Injectable

Questa guida spiega come migrare i servizi che usano le chiamate statiche a `Logger::error()`, `Logger::info()`, etc. per utilizzare il nuovo Logger injectable.

## Pattern di Migrazione

### 1. Aggiornare il Costruttore del Servizio

**Prima:**
```php
use FP\PerfSuite\Utils\Logger;

class MyService
{
    public function __construct()
    {
        // Nessuna dependency injection
    }
    
    public function doSomething(): void
    {
        Logger::info('Doing something');
    }
}
```

**Dopo:**
```php
use FP\PerfSuite\Core\Logging\LoggerInterface;

class MyService
{
    private ?LoggerInterface $logger = null;
    
    /**
     * @param LoggerInterface|null $logger Logger service (optional for backward compatibility)
     */
    public function __construct(?LoggerInterface $logger = null)
    {
        $this->logger = $logger;
    }
    
    public function doSomething(): void
    {
        if ($this->logger !== null) {
            $this->logger->info('Doing something');
        } else {
            // Fallback to static Logger for backward compatibility
            \FP\PerfSuite\Utils\Logger::info('Doing something');
        }
    }
}
```

### 2. Helper Method per Logger

Per semplificare, puoi creare un metodo helper:

```php
use FP\PerfSuite\Core\Logging\LoggerInterface;
use FP\PerfSuite\Utils\Logger as StaticLogger;

class MyService
{
    private ?LoggerInterface $logger = null;
    
    public function __construct(?LoggerInterface $logger = null)
    {
        $this->logger = $logger;
    }
    
    /**
     * Log a message (with fallback)
     * 
     * @param string $level Log level
     * @param string $message Message
     * @param array $context Context
     */
    private function log(string $level, string $message, array $context = []): void
    {
        if ($this->logger !== null) {
            $this->logger->$level($message, $context);
        } else {
            StaticLogger::$level($message, $context);
        }
    }
    
    public function doSomething(): void
    {
        $this->log('info', 'Doing something');
    }
    
    public function handleError(\Throwable $e): void
    {
        $this->log('error', 'Error occurred', ['exception' => $e->getMessage()]);
    }
}
```

### 3. Sostituire Chiamate Statiche

**Prima:**
```php
Logger::error('Something went wrong', ['key' => 'value']);
Logger::warning('Warning message');
Logger::info('Info message');
Logger::debug('Debug message');
```

**Dopo:**
```php
if ($this->logger !== null) {
    $this->logger->error('Something went wrong', ['key' => 'value']);
    $this->logger->warning('Warning message');
    $this->logger->info('Info message');
    $this->logger->debug('Debug message');
} else {
    Logger::error('Something went wrong', ['key' => 'value']);
    Logger::warning('Warning message');
    Logger::info('Info message');
    Logger::debug('Debug message');
}
```

### 4. Aggiornare il Service Provider

**Prima:**
```php
$container->singleton(
    MyService::class,
    fn() => new MyService()
);
```

**Dopo:**
```php
$container->singleton(
    MyService::class,
    function(Container $c) {
        $logger = $c->has(LoggerInterface::class)
            ? $c->get(LoggerInterface::class)
            : null;
        return new MyService($logger);
    }
);
```

## Esempio Completo

### PatternLearner.php (dopo migrazione)

```php
namespace FP\PerfSuite\Services\ML;

use FP\PerfSuite\Core\Logging\LoggerInterface;
use FP\PerfSuite\Utils\Logger as StaticLogger;

class PatternLearner
{
    private ?LoggerInterface $logger = null;
    
    public function __construct(?LoggerInterface $logger = null, ?OptionsRepositoryInterface $optionsRepo = null)
    {
        $this->logger = $logger;
        // ... altre inizializzazioni
    }
    
    private function log(string $level, string $message, array $context = []): void
    {
        if ($this->logger !== null) {
            $this->logger->$level($message, $context);
        } else {
            StaticLogger::$level($message, $context);
        }
    }
    
    public function learnPatterns(array $data): array
    {
        // ... logica ...
        
        $this->log('info', 'Patterns learned', ['patterns_count' => count($patterns)]);
        
        return $patterns;
    }
}
```

### MLServiceProvider.php

```php
$container->singleton(
    PatternLearner::class,
    function(Container $c) {
        $logger = $c->has(LoggerInterface::class)
            ? $c->get(LoggerInterface::class)
            : null;
        $optionsRepo = $c->has(OptionsRepositoryInterface::class)
            ? $c->get(OptionsRepositoryInterface::class)
            : null;
        return new PatternLearner($logger, $optionsRepo);
    }
);
```

## Vantaggi della Migrazione

1. **Testability**: Facile da testare con mock Logger
2. **Flexibility**: Possibilità di intercettare e modificare il logging
3. **PSR-3 Compliance**: Logger compatibile con standard PSR-3
4. **Multiple Handlers**: Supporto per più handler (file, database, remote)
5. **Log Levels**: Controllo granulare dei livelli di log
6. **Context Support**: Supporto completo per context arrays

## Checklist di Migrazione

- [ ] Aggiungere `LoggerInterface` al costruttore (opzionale)
- [ ] Creare metodo helper `log()` per semplificare
- [ ] Sostituire tutte le chiamate statiche a `Logger::*()` con metodo helper
- [ ] Aggiornare il Service Provider per iniettare Logger
- [ ] Testare il servizio con e senza Logger injectable
- [ ] Verificare che il fallback funzioni correttamente
- [ ] Rimuovere use statement di `Utils\Logger` (opzionale, dopo migrazione completa)

## Note Importanti

1. **Retrocompatibilità**: Mantenere sempre il fallback per garantire compatibilità
2. **Gradualità**: Migrare un servizio alla volta e testare ogni migrazione
3. **Context Arrays**: Il nuovo Logger supporta context arrays (PSR-3), il vecchio Logger accetta array come secondo parametro opzionale
4. **Testing**: Testare sempre sia con il nuovo Logger che senza (fallback)

## Servizi da Migrare

- [ ] PatternLearner (parzialmente migrato)
- [ ] CDNExclusionSync
- [ ] Altri servizi che usano Logger statico

## Migrazione Combinata (Logger + OptionsRepository)

Se un servizio usa sia Logger che get_option(), puoi migrarli insieme:

```php
class MyService
{
    private ?LoggerInterface $logger = null;
    private ?OptionsRepositoryInterface $optionsRepo = null;
    
    public function __construct(
        ?LoggerInterface $logger = null,
        ?OptionsRepositoryInterface $optionsRepo = null
    ) {
        $this->logger = $logger;
        $this->optionsRepo = $optionsRepo;
    }
}
```

Nel Service Provider:
```php
$container->singleton(
    MyService::class,
    function(Container $c) {
        $logger = $c->has(LoggerInterface::class) ? $c->get(LoggerInterface::class) : null;
        $optionsRepo = $c->has(OptionsRepositoryInterface::class) ? $c->get(OptionsRepositoryInterface::class) : null;
        return new MyService($logger, $optionsRepo);
    }
);
```

---

**Ultimo aggiornamento**: 2025-11-06










