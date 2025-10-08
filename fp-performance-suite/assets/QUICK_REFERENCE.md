# Quick Reference - Asset Modulari

## 🚀 Quick Start

### Verifica Struttura
```bash
cd assets && ./verify-structure.sh
```

### Aggiungere un Componente CSS
```bash
# 1. Creare file
touch css/components/nuovo.css

# 2. Aggiungere import
echo "@import url('components/nuovo.css');" >> css/admin.css
```

### Aggiungere una Feature JavaScript
```javascript
// 1. Creare js/features/nuova.js
export function nuovaFeature() {
    // codice
}

// 2. In js/main.js
import { nuovaFeature } from './features/nuova.js';
document.addEventListener('DOMContentLoaded', () => {
    nuovaFeature();
});
```

## 📁 Struttura Rapida

```
assets/
├── css/
│   ├── admin.css (entry point)
│   ├── base/ (variabili)
│   ├── layout/ (wrap, header, grid, card)
│   ├── components/ (badge, toggle, tooltip, table, log-viewer, actions)
│   ├── utilities/ (score)
│   └── themes/ (dark-mode, high-contrast, reduced-motion, print)
├── js/
│   ├── main.js (entry point)
│   ├── utils/ (http, dom)
│   ├── components/ (notice, progress, confirmation)
│   └── features/ (log-viewer, presets, bulk-actions)
└── legacy/ (backup originali)
```

## 🎨 Variabili CSS Disponibili

```css
/* Colors */
--fp-bg, --fp-card, --fp-accent
--fp-ok, --fp-warn, --fp-danger, --fp-text

/* Spacing */
--fp-spacing-xs, -sm, -md, -lg, -xl (8-24px)

/* Border Radius */
--fp-radius-sm, -md, -lg, -xl (8-16px)

/* Shadows */
--fp-shadow-sm, --fp-shadow-md

/* Typography */
--fp-font-size-xs, -sm, -base, -lg, -xl, -xxl (11-48px)

/* Transitions */
--fp-transition-fast, --fp-transition-base
```

## ⚡ API JavaScript Pubblica

```javascript
// Notifica
window.fpPerfSuiteUtils.showNotice('Messaggio', 'success');

// Progress bar
window.fpPerfSuiteUtils.showProgress(container, 50, 100);
window.fpPerfSuiteUtils.removeProgress(container);
```

## 🧪 Test Rapidi

### Browser Console
```javascript
// Verifica API
console.log(window.fpPerfSuiteUtils);

// Test notifica
window.fpPerfSuiteUtils.showNotice('Test', 'success');

// Verifica ES6 support
console.log('noModule' in document.createElement('script'));
```

### Bash
```bash
# Conta file
find css -name "*.css" | wc -l  # Dovrebbe essere 17
find js -name "*.js" | wc -l    # Dovrebbe essere 9

# Verifica import
grep -c "@import" css/admin.css  # Dovrebbe essere 16
grep -c "^import" js/main.js     # Dovrebbe essere 6
```

## 🐛 Troubleshooting Veloce

### CSS non carica
```bash
# Verifica file
ls -la css/base/ css/layout/ css/components/ css/utilities/ css/themes/

# Controlla DevTools Network per 404
```

### JS non funziona
```javascript
// Console browser
console.error.bind(console);  // Mostra errori
```

### Rollback rapido
```bash
# Ripristina originali
cp legacy/admin.css.bak admin.css
cp legacy/admin.js.bak admin.js
rm -rf css/ js/
```

## 📚 Documentazione Completa

- `README.md` - Guida completa
- `VERIFICATION.md` - Checklist test
- `../MODULARIZATION_REPORT.md` - Dettagli tecnici
- `../MODULARIZATION_SUMMARY.md` - Riepilogo
- `../DEPLOYMENT_MODULAR_ASSETS.md` - Guida deployment

## 🎯 Classi CSS Principali

```css
/* Layout */
.fp-ps-wrap, .fp-ps-header, .fp-ps-content
.fp-ps-grid, .fp-ps-card

/* Components */
.fp-ps-badge, .fp-ps-toggle, .fp-ps-tooltip
.fp-ps-table, .fp-ps-log-viewer, .fp-ps-actions

/* Utilities */
.fp-ps-score, .fp-ps-breadcrumbs
```

## 🔧 Convenzioni

### Naming
- Prefisso CSS: `fp-ps-`
- File CSS: lowercase-with-dash.css
- File JS: camelCase.js (ma preferibile dash)

### Organizzazione
- CSS: base → layout → components → utilities → themes
- JS: utils → components → features

### Import Order
CSS segue ordine: base, layout, components, utilities, themes
JS: ordine alfabetico per categoria

---

**Versione**: 1.0  
**Ultimo Aggiornamento**: Ottobre 2025