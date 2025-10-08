# FP Performance Suite - Assets Documentation

## 📁 Struttura Modulare

Gli asset del plugin sono ora organizzati in una struttura modulare per migliorare la manutenibilità, la riusabilità e la scalabilità del codice.

### 🎨 CSS Modulare

La struttura CSS è organizzata come segue:

```
css/
├── admin.css                    # Entry point principale (usa @import)
├── base/
│   └── variables.css           # CSS Custom Properties
├── layout/
│   ├── wrap.css               # Layout principale
│   ├── header.css             # Header e breadcrumbs
│   ├── grid.css               # Sistema griglia
│   └── card.css               # Card component
├── components/
│   ├── badge.css              # Badge component
│   ├── toggle.css             # Toggle component
│   ├── tooltip.css            # Tooltip component
│   ├── table.css              # Table component
│   ├── log-viewer.css         # Log viewer
│   └── actions.css            # Actions bar
├── utilities/
│   └── score.css              # Score display
└── themes/
    ├── dark-mode.css          # Dark mode support
    ├── high-contrast.css      # High contrast mode
    ├── reduced-motion.css     # Reduced motion support
    └── print.css              # Print styles
```

**Caratteristiche:**
- ✅ Variabili CSS centralizzate in `base/variables.css`
- ✅ Separazione tra layout, componenti e temi
- ✅ Supporto completo per accessibilità (dark mode, high contrast, reduced motion)
- ✅ Facile da estendere e mantenere

### 🚀 JavaScript Modulare (ES6)

La struttura JavaScript è organizzata come segue:

```
js/
├── main.js                     # Entry point principale
├── utils/
│   ├── http.js                # Utility per richieste HTTP
│   └── dom.js                 # Utility DOM
├── components/
│   ├── notice.js              # Sistema notifiche WordPress
│   ├── progress.js            # Progress bar
│   └── confirmation.js        # Conferme per azioni rischiose
└── features/
    ├── log-viewer.js          # Log viewer in tempo reale
    ├── presets.js             # Gestione preset
    └── bulk-actions.js        # Operazioni bulk
```

**Caratteristiche:**
- ✅ Moduli ES6 (`import`/`export`)
- ✅ Separazione delle responsabilità
- ✅ Riutilizzabilità del codice
- ✅ Facile testing e manutenzione
- ✅ API pubbliche esposte globalmente per compatibilità

## 🔧 Come Funziona

### Caricamento CSS

Il file `css/admin.css` è il punto di ingresso che importa tutti i moduli usando `@import`:

```css
@import url('base/variables.css');
@import url('layout/wrap.css');
/* ... altri import ... */
```

WordPress carica solo il file principale, e il browser si occupa di caricare i moduli.

### Caricamento JavaScript

Il file `js/main.js` è il punto di ingresso che importa tutti i moduli ES6:

```javascript
import { showNotice } from './components/notice.js';
import { initLogViewer } from './features/log-viewer.js';
// ... altri import ...
```

Il file viene caricato con `type="module"` grazie al filtro in `Assets.php`.

## 🔄 Retrocompatibilità

Le utility principali sono esposte globalmente per mantenere la compatibilità con codice esterno:

```javascript
window.fpPerfSuiteUtils = {
    showNotice,
    showProgress,
    removeProgress
};
```

## 📦 File Legacy

I file originali monolitici sono stati spostati in `legacy/` come backup:
- `legacy/admin.css.bak`
- `legacy/admin.js.bak`

## 🎯 Vantaggi della Modularizzazione

### CSS
1. **Manutenibilità**: Ogni componente ha il suo file
2. **Riusabilità**: Componenti possono essere riutilizzati
3. **Scalabilità**: Facile aggiungere nuovi componenti
4. **Performance**: Browser può cachare i singoli moduli
5. **Collaborazione**: Più sviluppatori possono lavorare su file diversi

### JavaScript
1. **Separazione delle responsabilità**: Ogni modulo ha uno scopo specifico
2. **Testing**: Più facile testare singoli moduli
3. **Tree-shaking**: I bundler possono ottimizzare il codice
4. **Leggibilità**: Codice più organizzato e comprensibile
5. **Debugging**: Più facile individuare e risolvere problemi

## 🛠️ Come Estendere

### Aggiungere un nuovo componente CSS

1. Crea il file in `css/components/nuovo-componente.css`
2. Aggiungi `@import url('components/nuovo-componente.css');` in `css/admin.css`

### Aggiungere un nuovo modulo JavaScript

1. Crea il file in `js/features/nuova-feature.js`
2. Esporta le funzioni: `export function nuovaFunzione() { ... }`
3. Importa in `js/main.js`: `import { nuovaFunzione } from './features/nuova-feature.js';`
4. Inizializza in `main.js` nel `DOMContentLoaded`

## 📝 Note per Sviluppatori

- **CSS**: Usa sempre le variabili CSS definite in `base/variables.css`
- **JavaScript**: Mantieni i moduli piccoli e focalizzati su un compito specifico
- **Naming**: Usa il prefixo `fp-ps-` per le classi CSS
- **Accessibilità**: Mantieni il supporto per dark mode, high contrast, ecc.
- **Performance**: Evita dipendenze circolari nei moduli JavaScript

## 🐛 Troubleshooting

**Problema**: Gli stili non vengono caricati
- **Soluzione**: Verifica che tutti i path in `@import` siano corretti

**Problema**: JavaScript non funziona
- **Soluzione**: Verifica che il browser supporti ES6 modules (tutti i browser moderni)
- **Soluzione**: Controlla la console per errori di import

**Problema**: Conflitti con altri plugin
- **Soluzione**: Usa sempre il prefisso `fp-ps-` per le classi e gli ID

## 📚 Risorse

- [CSS @import](https://developer.mozilla.org/en-US/docs/Web/CSS/@import)
- [JavaScript Modules](https://developer.mozilla.org/en-US/docs/Web/JavaScript/Guide/Modules)
- [WordPress Script Enqueue](https://developer.wordpress.org/reference/functions/wp_enqueue_script/)

---

**Versione**: 1.1.0  
**Data**: Ottobre 2025  
**Autore**: Francesco Passeri