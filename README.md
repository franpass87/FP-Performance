# FP Performance Suite

**Versione:** 1.6.0  
**Autore:** Francesco Passeri  
**WordPress:** 5.8+  
**PHP:** 7.4+

Plugin WordPress professionale per ottimizzazione completa delle performance del sito.

> 🆕 **v1.6.0** - Ottimizzazione completa per **Shared Hosting** con rilevamento automatico ambiente e configurazioni dinamiche!

---

## 📚 Documentazione

### 📖 Documentazione Corrente (Aggiornata 2025-01-25)

La documentazione più recente si trova in [`docs/00-current/`](docs/00-current/):

- **[UI Guidelines](docs/00-current/UI-GUIDELINES.md)** - Standard UI per pagine admin
- **[Standardizzazione UI](docs/00-current/STANDARDIZZAZIONE-UI-COMPLETATA.md)** - Report completamento UI
- **[Riepilogo UI](docs/00-current/RIEPILOGO-COMPLETO-UI.md)** - Overview completa interfaccia
- **[Analisi Servizi](docs/00-current/ANALISI-SERVIZI.md)** - Sicurezza e robustezza servizi

### 🗂️ Documentazione Completa

**➡️ [Indice Completo Documentazione](docs/INDEX.md)**

- **🚀 [Getting Started](docs/00-getting-started/)** - Guide introduttive
- **👤 [Guide Utente](docs/01-user-guides/)** - Configurazione e utilizzo
- **👨‍💻 [Developer](docs/02-developer/)** - Documentazione tecnica
- **📊 [Technical](docs/03-technical/)** - Implementazioni tecniche
- **🚀 [Deployment](docs/04-deployment/)** - Guide produzione
- **📋 [Changelog](docs/05-changelog/)** - Storico modifiche
- **📦 [Archive](docs/06-archive/)** - Documentazione storica

---

## 🚀 Features Principali

### Cache & Performance
- ✅ **Page Cache** - Cache statica delle pagine
- ✅ **Browser Cache** - Headers di cache ottimizzati
- ✅ **Object Cache** - Redis/Memcached support
- ✅ **Edge Cache** - Cloudflare, CloudFront integration
- ✅ **PWA** - Service Worker per offline support

### Asset Optimization
- ✅ **JavaScript** - Minify, defer, async, combine
- ✅ **CSS** - Minify, async, critical CSS inline
- ✅ **Fonts** - Preload, display swap, optimization
- ✅ **Images** - Lazy loading, WebP, responsive
- ✅ **Third-Party** - Gestione script esterni

### Database
- ✅ **Cleanup** - Revisioni, spam, transient
- ✅ **Optimization** - Tabelle, indici, frammentazione
- ✅ **Query Cache** - Cache query database
- ✅ **Monitoring** - Monitoraggio query lente

### Mobile
- ✅ **Mobile Optimization** - Ottimizzazioni specifiche mobile
- ✅ **Touch Targets** - Miglioramento tap targets
- ✅ **Responsive Images** - Immagini ottimizzate per device

### Security
- ✅ **Security Headers** - HSTS, CSP, X-Frame-Options
- ✅ **File Protection** - Protezione file sensibili
- ✅ **XML-RPC** - Blocco XML-RPC
- ✅ **Anti-Hotlink** - Protezione immagini

### Monitoring & Intelligence
- ✅ **Performance Monitor** - Metriche prestazioni
- ✅ **Core Web Vitals** - LCP, FID, CLS tracking
- ✅ **AI Auto-Config** - Configurazione automatica AI
- ✅ **Intelligence Dashboard** - Dashboard AI
- ✅ **ML Predictions** - Machine Learning per anomalie

---

## 📦 Installazione

### Via WordPress Admin
1. Scarica l'ultimo release da GitHub
2. Vai su **Plugin → Aggiungi nuovo → Carica plugin**
3. Carica il file ZIP e attiva

### Via Git (Sviluppo)
```bash
cd wp-content/plugins/
git clone https://github.com/franpass87/FP-Performance.git FP-Performance
cd FP-Performance
composer install
```

---

## ⚙️ Configurazione Rapida

### 1. Primo Setup
1. Attiva il plugin
2. Vai su **FP Performance → Overview**
3. Clicca su **AI Auto-Config** per configurazione automatica

### 2. Configurazione Manuale
1. **Cache:** Abilita Page Cache (TTL: 3600s)
2. **Assets:** Attiva defer JS e async CSS
3. **Database:** Imposta pulizia automatica settimanale
4. **Mobile:** Abilita ottimizzazioni mobile

### 3. Monitoraggio
- **Overview:** Dashboard con score e metriche
- **Monitoring & Reports:** Core Web Vitals e trend
- **Diagnostics:** Verifica servizi attivi

---

## 🎨 Interfaccia Admin

Tutte le pagine admin hanno ora un design standardizzato:

- **📦 Intro Box** con gradiente viola in ogni pagina
- **🎯 Emoji** contestuali per identificazione rapida
- **📊 Cards** uniformi per sezioni
- **🚦 Sistema Semaforo** (red/amber/green) per opzioni rischiose
- **📝 Notice** uniformi per feedback

[Vedi UI Guidelines complete](docs/00-current/UI-GUIDELINES.md)

---

## 🏗️ Architettura

### Struttura Plugin
```
FP-Performance/
├── src/
│   ├── Admin/          # Pagine e menu admin
│   ├── Services/       # Servizi del plugin
│   ├── Utils/          # Utility classes
│   ├── Http/           # Routes e AJAX
│   └── Plugin.php      # Classe principale
├── assets/             # CSS e JS
├── views/              # Template PHP
├── docs/               # Documentazione
└── fp-performance-suite.php  # Main file
```

### Service Container
Il plugin usa un **ServiceContainer** per dependency injection:
- Caricamento lazy dei servizi
- Registrazione condizionale (solo se abilitati)
- Cache settings per ridurre query database

---

## 🔧 Requisiti Tecnici

### Requisiti Minimi
- **WordPress:** 5.8+
- **PHP:** 7.4+
- **MySQL:** 5.6+

### Requisiti Raccomandati
- **WordPress:** 6.0+
- **PHP:** 8.0+
- **MySQL:** 8.0+
- **Redis/Memcached** per Object Cache

### Estensioni PHP
- **json** (richiesta)
- **mbstring** (richiesta)
- **fileinfo** (richiesta)
- **gd** o **imagick** (consigliata per immagini)
- **curl** (consigliata)
- **redis** o **memcached** (opzionale)

---

## 📈 Performance

### Miglioramenti Tipici
- ⚡ **Load Time:** -40% a -70%
- 📉 **Page Size:** -30% a -50%
- 🚀 **Core Web Vitals:** Miglioramento significativo
- 💾 **Database:** Riduzione overhead fino al 80%

---

## 🛠️ Sviluppo

### Setup Ambiente Dev
```bash
composer install
npm install
```

### Code Quality
```bash
# PHPCS check
composer phpcs

# PHPCS fix
composer phpcbf
```

### Testing
- **Dev Scripts:** `dev-scripts/` - Script di utilità
- **Tests:** `tests/` - Test automatizzati
- **Diagnostics:** Dashboard admin → Diagnostics

---

## 📝 Changelog

Vedi [CHANGELOG.md](CHANGELOG.md) per lo storico completo delle versioni.

### Ultima Versione: 1.5.1 (2025-01-25)
- ✅ 38 metodi aggiunti ai servizi
- ✅ UI standardizzata al 100%
- ✅ Sistema diagnostica servizi
- ✅ Correzioni errori critici
- ✅ Menu riorganizzato

---

## 🤝 Contribuire

1. Fork del repository
2. Crea feature branch (`git checkout -b feature/AmazingFeature`)
3. Commit delle modifiche (`git commit -m 'Add AmazingFeature'`)
4. Push al branch (`git push origin feature/AmazingFeature`)
5. Apri Pull Request

---

## 📄 Licenza

Questo plugin è rilasciato sotto licenza GPL v2 o successiva.

---

## 👨‍💻 Autore

**Francesco Passeri**
- Website: [francescopasseri.com](https://francescopasseri.com)
- GitHub: [@franpass87](https://github.com/franpass87)
- Email: info@francescopasseri.com

---

## 🙏 Ringraziamenti

Grazie a tutti i contributor e alla community WordPress per il supporto.

---

**🚀 Per iniziare, consulta la [Guida Rapida](docs/00-getting-started/) o la [Documentazione Completa](docs/INDEX.md)!**
