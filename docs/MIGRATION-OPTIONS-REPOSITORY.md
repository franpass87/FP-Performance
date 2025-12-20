# Guida alla Migrazione: OptionsRepository

Questa guida spiega come migrare i servizi che usano direttamente `get_option()`/`update_option()` per utilizzare il nuovo `OptionsRepository`.

## Pattern di Migrazione

### 1. Aggiornare il Costruttore del Servizio

**Prima:**
```php
class MyService
{
    public function __construct()
    {
        // Nessuna dependency injection
    }
    
    public function getSettings(): array
    {
        return get_option('fp_ps_my_option', []);
    }
}
```

**Dopo:**
```php
use FP\PerfSuite\Core\Options\OptionsRepositoryInterface;

class MyService
{
    private ?OptionsRepositoryInterface $optionsRepo = null;
    
    /**
     * @param OptionsRepositoryInterface|null $optionsRepo Options repository (optional for backward compatibility)
     */
    public function __construct(?OptionsRepositoryInterface $optionsRepo = null)
    {
        $this->optionsRepo = $optionsRepo;
    }
    
    public function getSettings(): array
    {
        if ($this->optionsRepo !== null) {
            return $this->optionsRepo->get('fp_ps_my_option', []);
        }
        
        // Fallback to direct option call for backward compatibility
        return get_option('fp_ps_my_option', []);
    }
}
```

### 2. Sostituire `get_option()` con `OptionsRepository::get()`

**Prima:**
```php
$value = get_option('fp_ps_my_option', 'default');
```

**Dopo:**
```php
$value = $this->optionsRepo !== null 
    ? $this->optionsRepo->get('fp_ps_my_option', 'default')
    : get_option('fp_ps_my_option', 'default'); // Fallback
```

### 3. Sostituire `update_option()` con `OptionsRepository::set()`

**Prima:**
```php
update_option('fp_ps_my_option', $value);
```

**Dopo:**
```php
if ($this->optionsRepo !== null) {
    $this->optionsRepo->set('fp_ps_my_option', $value);
} else {
    update_option('fp_ps_my_option', $value); // Fallback
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
        $optionsRepo = $c->has(OptionsRepositoryInterface::class)
            ? $c->get(OptionsRepositoryInterface::class)
            : null;
        return new MyService($optionsRepo);
    }
);
```

## Esempio Completo: PatternStorage

### PatternStorage.php

```php
namespace FP\PerfSuite\Services\ML\PatternLearner;

use FP\PerfSuite\Core\Options\OptionsRepositoryInterface;
use function update_option;
use function get_option;

class PatternStorage
{
    private const OPTION = 'fp_ps_ml_patterns';
    private ?OptionsRepositoryInterface $optionsRepo = null;

    public function __construct(?OptionsRepositoryInterface $optionsRepo = null)
    {
        $this->optionsRepo = $optionsRepo;
    }

    public function save(array $patterns): void
    {
        if ($this->optionsRepo !== null) {
            $this->optionsRepo->set(self::OPTION, $patterns);
        } else {
            update_option(self::OPTION, $patterns);
        }
    }

    public function get(): array
    {
        if ($this->optionsRepo !== null) {
            return $this->optionsRepo->get(self::OPTION, []);
        }
        return get_option(self::OPTION, []);
    }
}
```

### PatternLearner.php

```php
use FP\PerfSuite\Core\Options\OptionsRepositoryInterface;

class PatternLearner
{
    private ?OptionsRepositoryInterface $optionsRepo = null;
    private PatternStorage $storage;

    public function __construct(?OptionsRepositoryInterface $optionsRepo = null)
    {
        $this->optionsRepo = $optionsRepo;
        $this->storage = new PatternStorage($optionsRepo);
    }
    
    public function getSettings(): array
    {
        $defaults = ['enabled' => false];
        
        if ($this->optionsRepo !== null) {
            return $this->optionsRepo->get('fp_ps_pattern_learner', $defaults);
        }
        return get_option('fp_ps_pattern_learner', $defaults);
    }
}
```

### MLServiceProvider.php

```php
$container->singleton(
    PatternLearner::class,
    function(Container $c) {
        $optionsRepo = $c->has(OptionsRepositoryInterface::class)
            ? $c->get(OptionsRepositoryInterface::class)
            : null;
        return new PatternLearner($optionsRepo);
    }
);
```

## Vantaggi della Migrazione

1. **Type Safety**: `OptionsRepository` fornisce type hints migliori
2. **Default Values**: Gestione centralizzata dei default in `OptionsDefaults`
3. **Validation**: Possibilità di aggiungere validazione automatica
4. **Migration Support**: Supporto per migrazioni di opzioni tra versioni
5. **Cache Integration**: Cache integrata per ridurre query al database
6. **Testability**: Facile da testare con mock

## Checklist di Migrazione

- [ ] Aggiungere `OptionsRepositoryInterface` al costruttore (opzionale)
- [ ] Sostituire tutte le chiamate a `get_option()` con fallback
- [ ] Sostituire tutte le chiamate a `update_option()` con fallback
- [ ] Aggiornare il Service Provider per iniettare `OptionsRepository`
- [ ] Testare il servizio con e senza `OptionsRepository`
- [ ] Verificare che il fallback funzioni correttamente
- [ ] Aggiornare la documentazione del servizio

## Note Importanti

1. **Retrocompatibilità**: Mantenere sempre il fallback per garantire compatibilità durante la migrazione
2. **Gradualità**: Migrare un servizio alla volta e testare ogni migrazione
3. **Default Values**: Verificare che i default siano corretti in `OptionsDefaults.php`
4. **Testing**: Testare sempre sia con il nuovo `OptionsRepository` che senza (fallback)

## Servizi da Migrare

- [x] PatternStorage (completato)
- [x] PatternLearner (completato)
- [ ] CDNProviderDetector
- [ ] CDNReportGenerator
- [ ] Altri servizi che usano `get_option()` direttamente

---

**Ultimo aggiornamento**: 2025-11-06










