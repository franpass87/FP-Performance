# FP Performance Suite - Refactoring Architecture Documentation

**Versione**: 2.0.0  
**Data**: 2025-11-06  
**Status**: Foundation Complete - Ready for Gradual Migration

---

## 🎯 OBIETTIVO

Trasformare FP-Performance da architettura monolitica a architettura modulare con Service Provider Pattern, Dependency Injection, e separazione netta delle responsabilità. Questa architettura diventerà il blueprint universale per tutti i plugin FP.

---

## 📐 ARCHITETTURA IMPLEMENTATA

### 1. Kernel Layer

**Location**: `src/Kernel/`

Il kernel è il cuore del plugin che orchestra tutto:

- **Container**: Enhanced service container con tagging, singleton, alias
- **PluginKernel**: Orchestratore principale con auto-discovery
- **Bootstrapper**: Gestisce activation/deactivation
- **ServiceProviderInterface**: Contratto per tutti i provider

### 2. Core Services Layer

**Location**: `src/Core/`

Servizi core riutilizzabili:

- **Options**: Repository pattern con migration e defaults
- **Logging**: Logger injectable PSR-3 compatible
- **Validation**: Rule-based validation system
- **Sanitization**: Type-safe sanitization
- **Events**: Event dispatcher PSR-14 compatible
- **Hooks**: Centralized hook registry

### 3. Service Providers Layer

**Location**: `src/Providers/`

12 Service Providers che registrano tutti i servizi:

1. **CoreServiceProvider** (Priority: 100) - Core services sempre caricati
2. **DatabaseServiceProvider** (150) - Database operations
3. **CacheServiceProvider** (150) - Cache services
4. **AssetServiceProvider** (150) - Asset optimization
5. **IntelligenceServiceProvider** (200) - Smart features
6. **MLServiceProvider** (200) - Machine learning
7. **IntegrationServiceProvider** (200) - Third-party integrations
8. **MonitoringServiceProvider** (150) - Monitoring & reporting
9. **RestServiceProvider** (150) - REST API (conditional)
10. **CliServiceProvider** (150) - WP-CLI (conditional)
11. **AdminServiceProvider** (200) - Admin UI (conditional)
12. **FrontendServiceProvider** (200) - Frontend (conditional)

### 4. HTTP Layer

**Location**: `src/Http/`

Struttura REST API modulare:

- **Controllers/**: Business logic separata
  - BaseController
  - CacheController
  - LogsController
  - PresetController
  - DatabaseController
  - ScoreController
  - DebugController
- **Middleware/**: Authentication, validation
- **Routes.php**: Route definitions only

---

## 🔄 FLUSSO DI ESECUZIONE

```
1. fp-performance-suite.php (bootstrap minimale)
   ↓
2. Bootstrapper::bootstrap()
   ├─ Check environment
   ├─ Register activation/deactivation hooks
   └─ Create PluginKernel
   ↓
3. PluginKernel::boot()
   ├─ Auto-discover providers
   ├─ Register services (register phase)
   └─ Boot services (boot phase)
   ↓
4. Service Providers
   ├─ CoreServiceProvider registers core services
   ├─ Other providers register their services
   └─ Services boot in priority order
   ↓
5. WordPress hooks registered
   └─ Services are ready to use
```

---

## 📦 SERVICE CONTAINER

Il Container avanzato supporta:

- **Tagging**: Raggruppa servizi per tipo
- **Singleton**: Una sola istanza per service
- **Alias**: Nomi alternativi per servizi
- **Lazy Loading**: Caricamento on-demand
- **Factory Pattern**: Closure-based factories

**Esempio**:
```php
$container->singleton(LoggerInterface::class, fn($c) => new Logger($c->get(OptionsRepository::class)));
$container->tag('cache', [PageCache::class, ObjectCacheManager::class]);
$container->alias('logger', LoggerInterface::class);
```

---

## 🎨 PATTERNS IMPLEMENTATI

### Service Provider Pattern

Ogni feature è in un Service Provider:

```php
class CacheServiceProvider implements ServiceProviderInterface
{
    public function register(Container $container): void {
        // Bind services
    }
    
    public function boot(Container $container): void {
        // Initialize services
    }
    
    public function priority(): int {
        return 150; // Load order
    }
    
    public function shouldLoad(): bool {
        return true; // Conditional loading
    }
}
```

### Dependency Injection

Tutti i servizi ricevono dipendenze via constructor:

```php
class MyService
{
    public function __construct(
        OptionsRepositoryInterface $options,
        LoggerInterface $logger
    ) {
        // Services injected
    }
}
```

### Repository Pattern

Centralized options management:

```php
$options = $container->get(OptionsRepositoryInterface::class);
$value = $options->get('cache_enabled', false);
$options->set('cache_enabled', true);
```

---

## 🔌 INTEGRAZIONE CON CODICE ESISTENTE

### Backward Compatibility

La nuova architettura coesiste con il vecchio codice:

- Il vecchio `fp-performance-suite.php` è ancora attivo
- I nuovi servizi sono disponibili ma non obbligatori
- Bridge/Adapter permettono migrazione graduale

### Migration Strategy

1. **Phase 1-3**: Nuova architettura creata (✅ COMPLETA)
2. **Phase 4**: Migrazione graduale modulo per modulo
3. **Phase 5**: Rimozione codice vecchio dopo verifica

---

## 📝 ESEMPI D'USO

### Usare OptionsRepository

```php
// Vecchio modo (da sostituire gradualmente)
$enabled = get_option('fp_ps_cache_enabled', false);

// Nuovo modo (via DI)
$options = $container->get(OptionsRepositoryInterface::class);
$enabled = $options->get('cache_enabled', false);
```

### Usare Logger

```php
// Vecchio modo (static)
Logger::error('Error message', $exception);

// Nuovo modo (injectable)
$logger = $container->get(LoggerInterface::class);
$logger->error('Error message', [], $exception);
```

### Registrare Hook

```php
// Vecchio modo (direttamente)
add_action('init', [$this, 'myMethod']);

// Nuovo modo (via HookRegistry)
$registry = $container->get(HookRegistryInterface::class);
$registry->addAction('init', [$this, 'myMethod'], 10, 1, 'MyService');
```

---

## ✅ COMPLETATO

- [x] Kernel architecture
- [x] Core services (Options, Logger, Validator, Sanitizer, Events, Hooks)
- [x] 12 Service Providers
- [x] REST Controllers structure
- [x] Bootstrap minimale
- [x] Auto-discovery system
- [x] Backward compatibility bridges

---

## 🔜 PROSSIMI PASSI

1. Test nuova architettura in ambiente isolato
2. Migrare Routes.php per usare nuovi controller
3. Migrare gradualmente servizi a nuove dipendenze
4. Creare Admin Controllers per pagine admin
5. Documentare migrazione per sviluppatori

---

**Ultimo aggiornamento**: 2025-11-06









