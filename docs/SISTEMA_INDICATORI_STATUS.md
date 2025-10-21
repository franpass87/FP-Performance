# Sistema Unificato di Indicatori di Stato

## 📋 Panoramica

Il sistema **StatusIndicator** è un componente unificato che fornisce indicatori di stato consistenti in tutte le pagine admin del plugin FP Performance Suite.

### ✅ Benefici

- **Consistenza**: Colori, emoji e simboli uniformi in tutto il plugin
- **Accessibilità**: Support per high contrast, reduced motion e dark mode
- **Flessibilità**: Multipli stili disponibili (emoji, simboli, badge, card)
- **Manutenibilità**: Un solo punto di modifica per tutti gli indicatori

---

## 🎨 Stati Disponibili

| Stato | Colore | Emoji | Simbolo | Utilizzo |
|-------|--------|-------|---------|----------|
| `success` | Verde (#10b981) | 🟢 | ✓ | Tutto funziona correttamente |
| `warning` | Giallo (#f59e0b) | 🟡 | ⚠ | Richiede attenzione |
| `error` | Rosso (#ef4444) | 🔴 | ✗ | Problema critico |
| `info` | Blu (#3b82f6) | 🔵 | ℹ | Informazione generale |
| `inactive` | Grigio (#6b7280) | ⚫ | ○ | Disabilitato/Non attivo |

---

## 📦 Installazione

Il sistema è già integrato nel plugin. Per usarlo nelle pagine admin:

```php
use FP\PerfSuite\Admin\Components\StatusIndicator;
```

Il CSS è automaticamente caricato tramite `assets/css/admin.css`.

---

## 🚀 Utilizzo

### 1. Indicatore Inline

Mostra un indicatore di stato semplice inline.

```php
// Con emoji (default)
echo StatusIndicator::render('success', 'Tutto ok!');
// Output: 🟢 Tutto ok!

// Con simbolo
echo StatusIndicator::render('warning', 'Attenzione', 'symbol');
// Output: ⚠ Attenzione

// Con dot
echo StatusIndicator::render('error', 'Errore critico', 'dot');
// Output: ● Errore critico

// Con badge
echo StatusIndicator::render('info', 'Info', 'badge');
// Output: [INFO] (badge colorato)
```

**Parametri:**
- `$status` (string): Lo stato (`success`, `warning`, `error`, `info`, `inactive`)
- `$label` (string): Etichetta personalizzata (opzionale, usa default se vuoto)
- `$style` (string): Stile di rendering (`emoji`, `symbol`, `dot`, `badge`)

---

### 2. Card di Stato

Mostra una card colorata con icona, titolo, descrizione e valore.

```php
echo StatusIndicator::renderCard(
    'success',
    'Cache Attiva',
    'La cache sta funzionando correttamente',
    '98% Hit Rate'
);
```

**Parametri:**
- `$status` (string): Lo stato
- `$title` (string): Titolo della card
- `$description` (string): Descrizione opzionale
- `$value` (string): Valore da evidenziare (es. "98%")

**Output visivo:**
```
┌─────────────────────────────────┐
│ 🟢  Cache Attiva               │
│                                 │
│ 98% Hit Rate                    │
│                                 │
│ La cache sta funzionando        │
│ correttamente                   │
└─────────────────────────────────┘
```

---

### 3. Progress Bar

Mostra una barra di progresso con colore basato sullo stato.

```php
// Auto-determina lo stato in base alla percentuale
echo StatusIndicator::renderProgressBar(85, null, 'Completamento');
// Verde se >= 80, giallo se >= 50, rosso se < 50

// Stato manuale
echo StatusIndicator::renderProgressBar(45, 'warning', 'Spazio disco');
```

**Parametri:**
- `$percentage` (int): Valore 0-100
- `$status` (string|null): Stato (auto se null)
- `$label` (string): Etichetta opzionale

---

### 4. List Item

Mostra un elemento di lista con indicatore.

```php
echo '<ul class="fp-ps-status-list">';
echo StatusIndicator::renderListItem(
    'success',
    'Compressione attiva'
);
echo StatusIndicator::renderListItem(
    'warning',
    'Brotli supportato',
    'Richiede mod_brotli'
);
echo '</ul>';
```

**Parametri:**
- `$status` (string): Lo stato
- `$label` (string): Etichetta principale
- `$description` (string): Descrizione opzionale a destra

---

### 5. Indicatore di Confronto

Mostra variazione con frecce (↑↓→) e colori.

```php
echo StatusIndicator::renderComparison(
    125, // Valore attuale
    100, // Valore precedente
    true // true = valori alti sono positivi
);
// Output: ↑ 25.0% (verde)

echo StatusIndicator::renderComparison(
    75,   // Valore attuale
    100,  // Valore precedente  
    false // false = valori bassi sono positivi (es. tempo caricamento)
);
// Output: ↓ 25.0% (verde)
```

---

## 🔧 Funzioni Utility

### Auto-determinazione Stato

```php
$status = StatusIndicator::autoStatus(
    $value,              // Valore (es. 75)
    $goodThreshold,      // Soglia "success" (default: 80)
    $warningThreshold    // Soglia "warning" (default: 50)
);
// Restituisce: 'success', 'warning' o 'error'
```

### Ottenere Configurazione

```php
$config = StatusIndicator::getConfig('success');
// Restituisce:
// [
//     'color' => '#10b981',
//     'bg_color' => '#d1fae5',
//     'border_color' => '#10b981',
//     'emoji' => '🟢',
//     'symbol' => '✓',
//     'label' => 'Attivo'
// ]

$color = StatusIndicator::getColor('warning');
// Restituisce: '#f59e0b'
```

---

## 📊 Esempi Pratici

### Esempio 1: Overview con Card Multiple

```php
<div class="fp-ps-status-overview">
    <?php
    // Determina stati
    $cacheStatus = $cacheEnabled ? 'success' : 'inactive';
    $dbStatus = StatusIndicator::autoStatus($dbHealth, 80, 50);
    
    // Render cards
    echo StatusIndicator::renderCard(
        $cacheStatus,
        __('Cache', 'fp-performance-suite'),
        __('Sistema di caching', 'fp-performance-suite'),
        $cacheEnabled ? __('Attiva', 'fp-performance-suite') : __('Inattiva', 'fp-performance-suite')
    );
    
    echo StatusIndicator::renderCard(
        $dbStatus,
        __('Database', 'fp-performance-suite'),
        sprintf(__('Health Score: %d%%', 'fp-performance-suite'), $dbHealth),
        $dbHealth . '/100'
    );
    ?>
</div>
```

### Esempio 2: Lista di Controlli

```php
<div class="fp-ps-card">
    <h2><?php esc_html_e('Stato Sistema', 'fp-performance-suite'); ?></h2>
    <ul class="fp-ps-status-list">
        <?php
        echo StatusIndicator::renderListItem(
            $phpVersion >= 8.0 ? 'success' : 'warning',
            sprintf(__('PHP Version: %s', 'fp-performance-suite'), $phpVersion),
            $phpVersion >= 8.0 ? '' : __('Aggiornamento consigliato', 'fp-performance-suite')
        );
        
        echo StatusIndicator::renderListItem(
            $memoryLimit >= 256 ? 'success' : 'error',
            sprintf(__('Memory Limit: %dMB', 'fp-performance-suite'), $memoryLimit),
            $memoryLimit >= 256 ? '' : __('Aumentare a 256MB', 'fp-performance-suite')
        );
        
        echo StatusIndicator::renderListItem(
            $httpsEnabled ? 'success' : 'warning',
            __('HTTPS', 'fp-performance-suite'),
            $httpsEnabled ? __('Certificato valido', 'fp-performance-suite') : __('Installa certificato SSL', 'fp-performance-suite')
        );
        ?>
    </ul>
</div>
```

### Esempio 3: Dashboard con Metriche

```php
<div class="fp-ps-status-overview">
    <?php
    // Score generale
    $overallScore = 85;
    $scoreStatus = StatusIndicator::autoStatus($overallScore);
    
    echo StatusIndicator::renderCard(
        $scoreStatus,
        __('Performance Score', 'fp-performance-suite'),
        __('Punteggio complessivo del sito', 'fp-performance-suite'),
        $overallScore . '/100'
    );
    ?>
</div>

<!-- Progress bars -->
<?php
echo StatusIndicator::renderProgressBar(
    $cacheHitRate,
    null,
    __('Cache Hit Rate', 'fp-performance-suite')
);

echo StatusIndicator::renderProgressBar(
    $optimizationsActive,
    null,
    __('Ottimizzazioni Attive', 'fp-performance-suite')
);
?>

<!-- Confronti -->
<p>
    <?php esc_html_e('Rispetto alla settimana scorsa:', 'fp-performance-suite'); ?>
    <?php echo StatusIndicator::renderComparison($currentSpeed, $lastWeekSpeed, false); ?>
</p>
```

---

## 🎨 Classi CSS Disponibili

### Container e Layout

- `.fp-ps-status-overview` - Grid per card multiple
- `.fp-ps-status-list` - Lista di indicatori

### Componenti Individuali

- `.fp-ps-status-indicator` - Indicatore inline
- `.fp-ps-status-badge` - Badge colorato
- `.fp-ps-status-card` - Card di stato
- `.fp-ps-progress-wrapper` - Wrapper progress bar
- `.fp-ps-progress-bar` - Progress bar
- `.fp-ps-progress-fill` - Fill della progress bar

### Varianti di Stato

Tutte le classi supportano varianti per stato:
- `.fp-ps-status-success`
- `.fp-ps-status-warning`
- `.fp-ps-status-error`
- `.fp-ps-status-info`
- `.fp-ps-status-inactive`

---

## ♿ Accessibilità

Il sistema include supporto completo per:

- **High Contrast Mode**: Bordi e colori adattati automaticamente
- **Reduced Motion**: Transizioni disabilitate quando richiesto
- **Dark Mode**: Colori ottimizzati per tema scuro (future)
- **Screen Readers**: Markup semantico con label appropriate

---

## 🔄 Migrazione da Sistema Vecchio

### Prima (Vecchio Sistema)

```php
<?php if ($status['active']): ?>
    <span style="color: #00a32a;">✓</span> Compressione attiva
<?php else: ?>
    <span style="color: #d63638;">✗</span> Compressione non attiva
<?php endif; ?>
```

### Dopo (Nuovo Sistema)

```php
<?php
echo StatusIndicator::renderListItem(
    $status['active'] ? 'success' : 'error',
    __('Compressione attiva', 'fp-performance-suite')
);
?>
```

### Benefici della Migrazione

1. ✅ Codice più pulito e leggibile
2. ✅ Colori consistenti (niente più hardcoded hex)
3. ✅ Supporto accessibilità automatico
4. ✅ Manutenzione centralizzata
5. ✅ Facile da testare e modificare

---

## 📝 Best Practices

### ✅ Do

```php
// Usa auto-status quando possibile
$status = StatusIndicator::autoStatus($percentage);

// Sfrutta le descrizioni
echo StatusIndicator::renderListItem(
    'warning',
    'Backup attivo',
    'Ultimo backup: 2 giorni fa' // Descrizione utile
);

// Raggruppa card correlate
echo '<div class="fp-ps-status-overview">';
// ... cards
echo '</div>';
```

### ❌ Don't

```php
// Non usare colori hardcoded
<span style="color: #00a32a;">✓ Attivo</span> // ❌

// Non mescolare vecchio e nuovo sistema
<?php if ($active): ?>
    <span style="color: green;">Active</span>
<?php else: ?>
    <?php echo StatusIndicator::render('inactive', 'Inactive'); ?>
<?php endif; ?>

// Non creare stati custom
echo StatusIndicator::render('super-success', 'Ok'); // ❌
```

---

## 🐛 Troubleshooting

### Gli indicatori non hanno stile

**Problema**: Manca il CSS  
**Soluzione**: Verifica che `assets/css/admin.css` includa:
```css
@import url('components/status-indicator.css');
```

### I colori non sono consistenti

**Problema**: Mix di vecchio e nuovo sistema  
**Soluzione**: Sostituisci tutti gli indicatori inline con `StatusIndicator::render()`

### Le card non sono responsive

**Problema**: Manca la classe container  
**Soluzione**: Avvolgi le card in:
```html
<div class="fp-ps-status-overview">
    <!-- cards qui -->
</div>
```

---

## 📚 Risorse Aggiuntive

- **Componente**: `src/Admin/Components/StatusIndicator.php`
- **CSS**: `assets/css/components/status-indicator.css`
- **Esempi**: `src/Admin/Pages/Backend.php`, `Advanced.php`, `InfrastructureCdn.php`

---

## 🔮 Sviluppi Futuri

- [ ] Animazioni per cambio stato
- [ ] Tooltip automatici con suggerimenti
- [ ] Integrazione con sistema notifiche
- [ ] Widget per dashboard WordPress
- [ ] Export metriche in PDF con indicatori
- [ ] A/B testing per differenti visualizzazioni

---

## 👥 Contributi

Per migliorare il sistema:

1. Mantieni consistenza con gli stati esistenti
2. Testa accessibilità (screen reader, high contrast)
3. Aggiungi documentazione per nuove funzionalità
4. Verifica responsive su mobile

---

**Versione**: 1.0.0  
**Autore**: Francesco Passeri  
**Data**: Ottobre 2025

