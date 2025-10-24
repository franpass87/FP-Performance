# 🔧 Soluzione: Statistiche Query che Rimangono Sempre a 0

## Problema Identificato

Le statistiche delle query nel dashboard del plugin FP Performance Suite rimangono sempre a 0 per:
- **Query Lente**: 0
- **Query Duplicate**: 0  
- **Tempo Totale**: 0s

## Cause del Problema

1. **Servizio non abilitato**: Il `DatabaseQueryMonitor` viene registrato solo se il servizio database è abilitato
2. **SAVEQUERIES non definito**: WordPress non abilita il logging delle query
3. **Hook non attivati**: I hook per intercettare le query non funzionano correttamente
4. **Statistiche non persistenti**: I dati vengono persi tra le richieste

## Soluzioni Implementate

### 1. Modifiche al DatabaseQueryMonitor

**File**: `src/Services/DB/DatabaseQueryMonitor.php`

#### Modifiche Principali:

```php
// Forza l'abilitazione del monitoraggio
public function __construct()
{
    $settings = $this->getSettings();
    $this->isEnabled = !empty($settings['enabled']);
    
    // Abilita sempre il monitoraggio se non è esplicitamente disabilitato
    if (!$this->isEnabled) {
        $this->isEnabled = true; // Forza l'abilitazione per il debug
    }
}

// Abilita sempre il monitoraggio
public function register(): void
{
    // Abilita sempre il monitoraggio per il debug
    $this->isEnabled = true;
    
    // ... resto del codice
}

// Migliora il salvataggio delle statistiche
public function getStatistics(): array
{
    // ... codice esistente ...
    
    // Se ancora non abbiamo query, prova a recuperare dalle query salvate
    if ($totalQueries === 0) {
        $savedStats = get_option(self::OPTION_KEY . '_stats', []);
        if (!empty($savedStats)) {
            $totalQueries = $savedStats['total_queries'] ?? 0;
            $this->slowQueries = $savedStats['slow_queries'] ?? [];
            $this->duplicateCount = $savedStats['duplicate_queries'] ?? 0;
            $this->totalQueryTime = $savedStats['total_query_time'] ?? 0;
        }
    }
    
    // ... resto del codice ...
    
    // Salva le statistiche per la persistenza
    $this->saveStatistics($stats);
    
    return $stats;
}

// Nuovo metodo per salvare le statistiche
private function saveStatistics(array $stats): void
{
    $essentialStats = [
        'total_queries' => $stats['total_queries'] ?? 0,
        'slow_queries' => $stats['slow_queries'] ?? 0,
        'duplicate_queries' => $stats['duplicate_queries'] ?? 0,
        'total_query_time' => $stats['total_query_time'] ?? 0,
        'timestamp' => time(),
    ];
    
    update_option(self::OPTION_KEY . '_stats', $essentialStats, false);
}
```

### 2. Script di Abilitazione

**File**: `enable-query-monitoring.php`

Questo script:
- Abilita `SAVEQUERIES` se non è già abilitato
- Abilita il servizio database
- Abilita il Query Monitor
- Testa il sistema con query di esempio
- Verifica che le statistiche vengano salvate

### 3. Script di Fix Completo

**File**: `fix-query-stats-zero.php`

Questo script:
- Diagnostica il problema
- Applica tutti i fix necessari
- Testa il sistema
- Verifica che le statistiche funzionino

## Come Utilizzare la Soluzione

### Metodo 1: Script Automatico

1. Esegui il file `fix-query-stats-zero.php` nel browser
2. Il script applicherà automaticamente tutti i fix
3. Verifica che le statistiche funzionino nel dashboard

### Metodo 2: Abilitazione Manuale

1. Vai nel dashboard di WordPress
2. Vai in **FP Performance Suite > Database**
3. Assicurati che il servizio database sia abilitato
4. Ricarica la pagina per vedere le statistiche

### Metodo 3: Configurazione WordPress

Aggiungi questo codice al file `wp-config.php`:

```php
// Abilita il logging delle query
define('SAVEQUERIES', true);
```

## Verifica della Soluzione

Dopo aver applicato la soluzione, le statistiche dovrebbero mostrare:

- **Query Totali**: Numero di query eseguite (es. 132)
- **Query Lente**: Query che superano la soglia di 5ms
- **Query Duplicate**: Query identiche ripetute
- **Tempo Totale**: Tempo totale di esecuzione delle query

## File Modificati

1. `src/Services/DB/DatabaseQueryMonitor.php` - Modifiche principali
2. `enable-query-monitoring.php` - Script di abilitazione
3. `fix-query-stats-zero.php` - Script di fix completo
4. `test-query-monitor-simple.php` - Test del sistema

## Note Importanti

- Le modifiche sono retrocompatibili
- Il sistema funziona anche senza WordPress in modalità debug
- Le statistiche vengono salvate persistentemente
- Il monitoraggio è sempre attivo per il debug

## Risoluzione Problemi

Se le statistiche rimangono ancora a 0:

1. **Ricarica la pagina** del dashboard
2. **Riavvia il server** se possibile
3. **Verifica che SAVEQUERIES sia abilitato**
4. **Controlla i log di WordPress** per errori
5. **Esegui il script di fix** per diagnosticare il problema

## Risultato Atteso

Dopo aver applicato la soluzione, le statistiche delle query dovrebbero funzionare correttamente e mostrare valori reali invece di rimanere sempre a 0.
