# Performance Analyzer - Documentazione

## 📖 Panoramica

Il **Performance Analyzer** è un sistema intelligente di diagnosi automatica che analizza il tuo sito WordPress e identifica problemi di performance, fornendo raccomandazioni dettagliate e prioritizzate per migliorare le prestazioni.

## 🎯 Caratteristiche Principali

### 1. Analisi Multi-Area

Il sistema analizza **6 aree critiche**:

#### 🗄️ Cache
- Stato page cache (filesystem)
- Configurazione browser cache headers
- Impatto sulla velocità di caricamento

#### ⚡ Asset
- Minificazione HTML/CSS/JS
- Defer JavaScript
- Script emoji WordPress
- API Heartbeat throttling
- Critical CSS configuration

#### 💾 Database
- Overhead delle tabelle
- Numero di query per pagina
- Frammentazione

#### 🖼️ Immagini
- Copertura WebP
- Ottimizzazione formati
- Dimensioni file

#### 🖥️ Server
- Compressione GZIP/Brotli
- Limiti memoria PHP
- Configurazione ottimale

#### 📊 Metriche Storiche
- Tempi di caricamento (ultimi 7 giorni)
- Uso memoria
- Performance database

### 2. Categorizzazione Intelligente

I problemi vengono classificati in tre livelli:

#### 🚨 CRITICI (Priorità 80-100)
Problemi che impattano gravemente le performance e richiedono intervento immediato.

**Esempi:**
- Cache delle pagine disabilitata
- Compressione non attiva
- Database con overhead >20MB
- Tempo di caricamento >2 secondi

**Impatto sul punteggio:** -15 punti cadauno

#### ⚠️ WARNING (Priorità 60-79)
Problemi significativi che dovrebbero essere risolti per ottenere performance ottimali.

**Esempi:**
- Browser cache headers non configurati
- JavaScript non differito
- Bassa copertura WebP (<40%)
- Numero elevato di query (>100)

**Impatto sul punteggio:** -8 punti cadauno

#### 💡 RACCOMANDAZIONI (Priorità 40-59)
Ottimizzazioni consigliate per migliorare ulteriormente le performance.

**Esempi:**
- Minificazione HTML
- Rimozione script emoji
- Throttling heartbeat
- Critical CSS
- Ottimizzazione memoria

**Impatto sul punteggio:** -3 punti cadauno

### 3. Health Score (0-100)

Il punteggio di salute viene calcolato sottraendo penalità dal punteggio base di 100:

```
Score = 100 - (Critici × 15) - (Warning × 8) - (Raccomandazioni × 3)
```

**Interpretazione:**
- **90-100**: 🟢 Salute eccellente
- **70-89**: 🟡 Buona salute
- **50-69**: 🟠 Necessita attenzione
- **0-49**: 🔴 Problemi critici

### 4. Informazioni Dettagliate

Ogni problema fornisce:

1. **Titolo Descrittivo**
   - Chiaro e conciso
   - Identifica immediatamente il problema

2. **Impatto Quantificato**
   - Effetto sulle performance (tempo, dimensione, percentuali)
   - Conseguenze per utenti e SEO
   - Esempi: "rallenta di 300-1000ms", "aumenta dimensioni del 25-35%"

3. **Soluzione Step-by-Step**
   - Dove andare nell'interfaccia admin
   - Cosa attivare/disattivare
   - Configurazioni raccomandate
   - Tool esterni se necessari

4. **Priorità**
   - Numero da 1 a 100
   - Ordina automaticamente i problemi
   - Mostra cosa risolvere per primo

## 🔍 Aree di Analisi Dettagliate

### Cache Analysis

```php
// Controlla page cache
if (!$this->pageCache->isEnabled()) {
    → CRITICO: Cache disabilitata
    → Impatto: rigenerazione HTML completo ad ogni richiesta (300-1000ms vs 10-50ms)
    → Soluzione: Attiva page cache in FP Performance > Cache
}

// Controlla browser cache headers
if (empty($headerStatus['enabled'])) {
    → WARNING: Headers non configurati
    → Impatto: asset ricaricati ad ogni visita
    → Soluzione: Attiva browser cache headers
}
```

### Asset Analysis

```php
// Minificazione
if (empty($status['minify_html'])) {
    → RACCOMANDAZIONE: HTML non minificato
    → Impatto: +10-20% dimensione pagine
    → Soluzione: Attiva minificazione
}

// Defer JS
if (empty($status['defer_js'])) {
    → WARNING: JS non differito
    → Impatto: blocking render per 200-500ms
    → Soluzione: Attiva defer JavaScript
}

// Heartbeat
if ($heartbeatInterval < 60) {
    → RACCOMANDAZIONE: Heartbeat troppo frequente
    → Impatto: richieste ogni X secondi
    → Soluzione: Imposta 60+ secondi (120 per shared hosting)
}
```

### Database Analysis

```php
// Overhead tabelle
if ($overhead >= 20) {
    → CRITICO: Overhead elevato
    → Impatto: query 30-50% più lente
    → Soluzione: Ottimizza tabelle + cleanup
}

// Numero query
if ($queries > 100) {
    → WARNING: Troppe query
    → Impatto: 1-5ms per query × N query
    → Soluzione: Plugin problematici + object caching
}
```

### Image Analysis

```php
// Copertura WebP
if ($coverage < 40) {
    → WARNING: Bassa copertura WebP
    → Impatto: immagini 25-35% più pesanti
    → Soluzione: Bulk WebP conversion
} elseif ($coverage < 80) {
    → RACCOMANDAZIONE: Copertura parziale
    → Impatto: perdita 15-25% ottimizzazione
    → Soluzione: Completa conversione
}
```

### Server Configuration Analysis

```php
// Compressione
if (!$hasCompression) {
    → CRITICO: GZIP/Brotli non rilevato
    → Impatto: file 3-5x più grandi (300KB vs 60KB)
    → Soluzione: Contatta hosting o .htaccess
}

// Memoria PHP
if ($memoryLimit < 256MB) {
    → RACCOMANDAZIONE: Memoria limitata
    → Impatto: errori su operazioni intensive
    → Soluzione: Aumenta memory_limit a 256M+
}
```

### Historical Metrics Analysis

```php
// Tempo di caricamento
if ($avgLoadTime > 2.0) {
    → CRITICO: Caricamento molto lento
    → Impatto: 50%+ abbandono visitatori + penalizzazione SEO
    → Soluzione: Segui TUTTE le raccomandazioni
} elseif ($avgLoadTime > 1.0) {
    → WARNING: Caricamento lento
    → Impatto: esperienza utente sub-ottimale
    → Soluzione: Implementa ottimizzazioni
}

// Memory usage
if ($avgMemory > 100) {
    → RACCOMANDAZIONE: Uso memoria elevato
    → Impatto: rischio errori su shared hosting
    → Soluzione: Disattiva plugin pesanti
}
```

## 📱 Interfaccia Utente

### Layout della Sezione

```
┌─────────────────────────────────────────────────┐
│ 🔍 Analisi Problemi di Performance              │
├─────────────────────────────────────────────────┤
│                                                 │
│  ┌───────────────────────────────────────┐     │
│  │  Health Score: 72/100                 │     │
│  │  Stato: Buona Salute                  │     │
│  │  Risolvi 2 warning e 3 raccomandazioni│     │
│  └───────────────────────────────────────┘     │
│                                                 │
│  🚨 Problemi Critici (0)                        │
│  [vuoto]                                        │
│                                                 │
│  ⚠️ Avvisi (2)                                  │
│  ┌─────────────────────────────────────┐       │
│  │ JavaScript non differito            │       │
│  │ Impatto: blocca rendering 200-500ms │       │
│  │ 💡 Soluzione: Attiva defer in Assets│       │
│  └─────────────────────────────────────┘       │
│  ┌─────────────────────────────────────┐       │
│  │ Copertura WebP 35%                  │       │
│  │ Impatto: immagini 25-35% più pesanti│       │
│  │ 💡 Soluzione: Bulk conversion Media │       │
│  └─────────────────────────────────────┘       │
│                                                 │
│  💡 Raccomandazioni (3)                         │
│  [... altre card ...]                           │
│                                                 │
└─────────────────────────────────────────────────┘
```

### Codifica Colori

- 🔴 **Rosso** (#fee2e2, #dc2626): Problemi critici
- 🟡 **Giallo** (#fef3c7, #f59e0b): Warning
- 🔵 **Blu** (#dbeafe, #3b82f6): Raccomandazioni
- 🟢 **Verde** (#d1fae5, #059669): OK / Soluzioni

## 🛠️ Uso Programmatico

### Ottenere l'Analisi

```php
use FP\PerfSuite\Services\Monitoring\PerformanceAnalyzer;

$analyzer = $container->get(PerformanceAnalyzer::class);
$analysis = $analyzer->analyze();

// Struttura risultato
[
    'critical' => [
        [
            'issue' => 'Cache disabilitata',
            'impact' => 'Ogni richiesta rigenera HTML...',
            'solution' => 'Vai su FP Performance > Cache...',
            'priority' => 100
        ],
        // ...
    ],
    'warnings' => [
        // ...
    ],
    'recommendations' => [
        // ...
    ],
    'score' => 72,
    'summary' => 'Buona configurazione, ma ci sono margini...'
]
```

### Hook Personalizzati

Il sistema può essere esteso tramite filtri:

```php
// Modificare la soglia di memoria critica
add_filter('fp_ps_memory_critical_threshold', function($threshold) {
    return 512; // MB
});

// Aggiungere problemi custom
add_filter('fp_ps_performance_issues', function($issues) {
    if (my_custom_check_fails()) {
        $issues['critical'][] = [
            'issue' => 'Mio problema custom',
            'impact' => 'Descrizione impatto',
            'solution' => 'Come risolvere',
            'priority' => 85
        ];
    }
    return $issues;
});
```

## 🔄 Aggiornamento in Tempo Reale

L'analisi viene rieseguita ad ogni caricamento della pagina Performance Metrics, quindi:

- ✅ Riflette sempre lo stato corrente
- ✅ Mostra immediatamente i miglioramenti dopo modifiche
- ✅ Non richiede cache clearing o refresh speciali

## 📈 Best Practices

### Per Amministratori

1. **Controlla regolarmente** la pagina Performance Metrics
2. **Risolvi prima i critici**, poi i warning, infine le raccomandazioni
3. **Verifica dopo ogni modifica** che il problema sia risolto
4. **Documenta le modifiche** per reference futuro

### Per Sviluppatori

1. **Usa i filtri** per estendere l'analisi
2. **Integra con monitoring esterno** tramite hook
3. **Testa le performance** dopo ogni deploy
4. **Monitora i trend** usando le metriche storiche

## 🎓 Esempi Pratici

### Scenario 1: Sito Lento

**Problema:**
- Score: 35/100
- 3 problemi critici, 5 warning

**Soluzione:**
1. Attiva page cache (15 punti)
2. Abilita compressione GZIP (15 punti)
3. Ottimizza database (15 punti)
4. Configura browser cache (8 punti)
5. Defer JavaScript (8 punti)

**Risultato:** Score passa da 35 a 96

### Scenario 2: Ottimizzazione Incrementale

**Problema:**
- Score: 75/100
- 0 critici, 2 warning, 5 raccomandazioni

**Soluzione:**
1. Bulk WebP conversion (8 punti)
2. Attiva defer JS (8 punti)
3. Minificazione HTML (3 punti)
4. Rimuovi emoji (3 punti)

**Risultato:** Score passa da 75 a 97

## 🔐 Sicurezza

- ✅ **Nessuna esecuzione di codice arbitrario**
- ✅ **Solo lettura** (no modifiche automatiche)
- ✅ **Output escaped** con `esc_html()` / `esc_attr()`
- ✅ **Rispetta permessi** WordPress standard
- ✅ **Nessuna query diretta** al database dall'UI

## 📝 Note Tecniche

### Dipendenze

Il PerformanceAnalyzer richiede:
- `PageCache` service
- `Headers` service
- `Optimizer` service
- `WebPConverter` service
- `Cleaner` service
- `PerformanceMonitor` service

### Performance

- Analisi completa: ~20-50ms
- Nessuna query extra (usa service cache)
- Impatto minimo sul caricamento pagina admin

### Compatibilità

- WordPress 6.2+
- PHP 8.0+
- Tutti gli hosting (shared, VPS, dedicati)

## 🤝 Contribuire

Per aggiungere nuovi tipi di analisi:

1. Crea metodo `analyzeXXX()` in `PerformanceAnalyzer`
2. Popola array `$issues` con problemi trovati
3. Usa priorità appropriate (40-100)
4. Fornisci descrizioni chiare e soluzioni actionable
5. Testa su vari scenari

## 📞 Supporto

Per domande o problemi:
- GitHub Issues: https://github.com/franpass87/FP-Performance/issues
- Documentazione: https://francescopasseri.com
- Email: info@francescopasseri.com

---

**Versione:** 1.2.0  
**Ultima modifica:** 2025-10-11  
**Autore:** Francesco Passeri
