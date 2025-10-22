# 🧠 Guida alle Funzionalità Intelligenti di FP Performance Suite

> **Versione Plugin**: 1.5.1  
> **Data Aggiornamento**: 21 Ottobre 2025  
> **Livello**: Intermedio/Avanzato

---

## 📋 Indice

1. [Introduzione](#introduzione)
2. [Sistema di Rilevamento Intelligente delle Esclusioni](#sistema-di-rilevamento-intelligente-delle-esclusioni)
3. [Rilevamento Automatico Script di Terze Parti](#rilevamento-automatico-script-di-terze-parti)
4. [Configurazione Automatica della Cache](#configurazione-automatica-della-cache)
5. [Prefetching Predittivo](#prefetching-predittivo)
6. [Ottimizzatore Automatico dei Font](#ottimizzatore-automatico-dei-font)
7. [Rilevamento Asset Critici](#rilevamento-asset-critici)
8. [FAQ e Risoluzione Problemi](#faq-e-risoluzione-problemi)

---

## 🎯 Introduzione

FP Performance Suite include un sistema di **intelligenza artificiale** e **machine learning** integrato che analizza automaticamente il tuo sito WordPress e suggerisce (o applica automaticamente) le migliori ottimizzazioni.

### Cosa Significa "Intelligente"?

Invece di configurare manualmente ogni singola opzione, il plugin:

- ✅ **Rileva automaticamente** i plugin attivi (WooCommerce, MemberPress, ecc.)
- ✅ **Identifica** le pagine sensibili da escludere dalla cache
- ✅ **Riconosce** gli script di terze parti (Google Analytics, Facebook Pixel, ecc.)
- ✅ **Suggerisce** le ottimizzazioni migliori per il tuo sito specifico
- ✅ **Apprende** dal comportamento degli utenti

### Chi Dovrebbe Usare Queste Funzionalità?

- 👤 **Utenti Avanzati**: Che vogliono ottimizzare al massimo senza configurazione manuale
- 🏢 **Agenzie**: Che gestiscono molti siti e vogliono automazione
- 🛒 **E-commerce**: Con WooCommerce, EDD o altri plugin complessi
- 📚 **Siti Membership**: Con LearnDash, MemberPress, BuddyPress

---

## 🔍 Sistema di Rilevamento Intelligente delle Esclusioni

### Cos'è?

Il **Smart Exclusion Detector** è un sistema che analizza automaticamente il tuo sito e identifica:

1. **URL sensibili** che NON dovrebbero essere cachati (carrello, checkout, account utente)
2. **Script critici** che NON dovrebbero essere ottimizzati (jQuery, Stripe, PayPal)
3. **CSS critici** che devono rimanere inline

### Come Funziona?

Il sistema utilizza **3 strategie** di rilevamento:

#### 1. Rilevamento Standard (Pattern Comuni)

Cerca pattern URL comuni come:
- `/cart`, `/checkout`, `/order` → E-commerce
- `/account`, `/profile`, `/dashboard` → Aree utente
- `/login`, `/register`, `/signup` → Autenticazione
- `/contact`, `/form`, `/submit` → Form
- `/search`, `/filter`, `/ajax` → Contenuto dinamico

#### 2. Rilevamento Basato su Plugin

Analizza i plugin attivi e aggiunge esclusioni specifiche:

**WooCommerce rilevato** ✅
```
Aggiunge automaticamente:
- /cart/
- /checkout/
- /my-account/
- ?add-to-cart=
- /wc-ajax
```

**LearnDash rilevato** ✅
```
Aggiunge automaticamente:
- /courses/
- /lessons/
- /topic/
- /quiz/
```

**MemberPress rilevato** ✅
```
Aggiunge automaticamente:
- /membership/
- /register/
- /mepr/
```

#### 3. Rilevamento da Comportamento Utente

Monitora:
- Pagine con errori frequenti → Escludi dalla cache
- Pagine lente → Analizza e suggerisce ottimizzazioni
- Pagine con molti form → Esclusione automatica

### Come Usarlo?

#### Opzione A: Modalità Automatica (Consigliata)

1. Vai su **FP Performance > Esclusioni**
2. Clicca su **"Scansiona Automaticamente"**
3. Il plugin analizzerà il sito e mostrerà:
   - ✅ **Già Protette**: URL già esclusi di default
   - 🔍 **Auto-Rilevate**: Nuove esclusioni suggerite
   - 🔌 **Plugin-Based**: Esclusioni dai plugin attivi
   - 👤 **Da Comportamento**: Esclusioni basate su errori/lentezza

4. Rivedi i risultati:

```
=== RISULTATI SCANSIONE ===

✅ Già Protette (12):
- /wp-admin/
- /wp-login.php
- /cart/ (WooCommerce)
- /checkout/ (WooCommerce)

🔍 Auto-Rilevate (5):
- /order-tracking/ (motivo: E-commerce sensitive)
- /account/downloads/ (motivo: User-specific content)

🔌 Plugin-Based (3):
- /courses/ (motivo: LearnDash rilevato)
- /lessons/ (motivo: LearnDash rilevato)

👤 Da Comportamento (1):
- /custom-form/ (motivo: 5 errori in 7 giorni)

Confidence Score: 92%
```

5. Clicca **"Applica Suggerimenti"** per accettare

#### Opzione B: Dry Run (Test Senza Modifiche)

1. Vai su **FP Performance > Esclusioni**
2. Clicca **"Dry Run"**
3. Visualizza i suggerimenti senza applicarli
4. Decidi manualmente cosa applicare

#### Opzione C: Manuale con Assistenza

1. Aggiungi manualmente un URL
2. Il sistema ti mostrerà:
   - ⚠️ **Avviso**: "Questo URL sembra un checkout, sicuro di volerlo cachare?"
   - ✅ **Suggerimento**: "Confidence: 95% - Raccomandiamo esclusione"

### Capire il "Confidence Score"

Il sistema assegna un punteggio di confidenza (0-100%):

- **90-100%**: Alta confidenza → Applica automaticamente
- **75-89%**: Media confidenza → Suggerisci all'utente
- **50-74%**: Bassa confidenza → Mostra solo come info
- **< 50%**: Molto bassa → Ignora

**Esempio**:

```php
URL: /checkout/
Motivo: WooCommerce checkout rilevato
Confidence: 98% ✅ ALTA - Applica automaticamente

URL: /custom-cart-page/
Motivo: Contiene parola "cart" nell'URL
Confidence: 65% ⚠️ MEDIA - Chiedi conferma utente
```

### Protezioni Built-In

Alcune protezioni sono **sempre attive** e non richiedono configurazione:

#### WordPress Core
- `/wp-admin/`, `/wp-login.php`, `/wp-json/`
- Feed RSS, sitemap, robots.txt
- Utenti loggati

#### WooCommerce
- Cart, Checkout, My Account
- AJAX endpoints (`/wc-ajax`, `/wc-api`)

#### Easy Digital Downloads
- `/edd-ajax`, `/purchase`, `/downloads`

#### Membership
- MemberPress, LearnDash, bbPress, BuddyPress

### Esempi Pratici

#### Esempio 1: Sito E-commerce con WooCommerce

```
Prima (manuale):
- Devi sapere tutti gli URL da escludere
- Rischio di dimenticare pagine importanti
- Configurazione complessa

Dopo (automatico):
1. Attiva WooCommerce
2. Attiva FP Performance Suite
3. Il plugin rileva automaticamente WooCommerce
4. Esclude automaticamente:
   - /cart/, /checkout/, /my-account/
   - Endpoint AJAX
   - Pagine dinamiche

Risultato: 0 configurazione manuale ✅
```

#### Esempio 2: Sito Membership con LearnDash

```
Scansione automatica rileva:
✅ LearnDash attivo
✅ 15 corsi pubblicati
✅ Quiz e lezioni dinamiche

Applica automaticamente:
- Escludi /courses/*
- Escludi /lessons/*
- Escludi /quiz/*
- Mantieni dashboard utente non cachato

Risultato: Studenti vedono sempre contenuti aggiornati ✅
```

---

## 🌐 Rilevamento Automatico Script di Terze Parti

### Cos'è?

Il **Third Party Script Detector** identifica automaticamente tutti gli script esterni caricati nel tuo sito (Google Analytics, Facebook Pixel, chat widget, ecc.) e suggerisce come ottimizzarli.

### Perché è Importante?

Gli script di terze parti sono la **causa principale** di rallentamenti:

- 📊 Google Analytics: ~45KB
- 📘 Facebook Pixel: ~35KB
- 💬 Widget Chat (Intercom, Drift): ~150KB+
- 📹 YouTube Embed: ~500KB+

Questi script:
- Bloccano il rendering della pagina
- Rallentano il First Contentful Paint (FCP)
- Riducono il Performance Score di PageSpeed

### Come Funziona?

Il sistema:

1. **Scansiona** tutti gli script caricati nella pagina
2. **Identifica** da dove provengono (interno vs esterno)
3. **Riconosce** pattern noti (Google, Facebook, Stripe, ecc.)
4. **Categorizza** gli script per tipo
5. **Suggerisce** la strategia di caricamento ottimale

### Categorie di Script

#### 📊 Analytics
**Rilevati**: Google Analytics, Google Tag Manager, Hotjar, Matomo

**Impatto**: Medio-Alto  
**Strategia Consigliata**: Carica **on-interaction** (dopo che l'utente interagisce)

```javascript
Invece di caricare subito:
<script src="google-analytics.com/analytics.js"></script>

Carica dopo click/scroll:
// Caricato solo quando utente fa scroll o click
```

**Risparmio**: ~0.5-1s di First Contentful Paint

#### 📢 Marketing
**Rilevati**: Facebook Pixel, Google Ads, LinkedIn Insight

**Impatto**: Alto  
**Strategia Consigliata**: Carica **on-interaction** o **lazy**

**Risparmio**: ~0.3-0.8s di LCP

#### 💬 Chat Widget
**Rilevati**: Intercom, Drift, Tawk.to, LiveChat, Crisp

**Impatto**: Molto Alto (spesso 100-300KB)  
**Strategia Consigliata**: Carica **on-interaction** (quando utente clicca icona chat)

**Esempio**:

```html
Prima:
<script>
  // Intercom si carica subito (200KB)
  window.Intercom('boot', {...});
</script>
Impatto: Blocca pagina per 1-2 secondi

Dopo (ottimizzato):
<button id="open-chat">Chat con noi</button>
<script>
  // Intercom si carica SOLO quando utente clicca
  document.getElementById('open-chat').onclick = () => {
    // Carica script ora
  };
</script>
Impatto: ZERO sul caricamento iniziale ✅
```

**Risparmio**: ~1-3s di tempo di caricamento

#### 💳 Payment
**Rilevati**: Stripe, PayPal, Braintree

**Impatto**: Medio  
**Strategia Consigliata**: **Critical** (carica sempre, sono necessari)

⚠️ **ATTENZIONE**: Script di pagamento NON dovrebbero mai essere ottimizzati/differiti per sicurezza.

#### 🔒 Security
**Rilevati**: reCAPTCHA, Cloudflare Turnstile

**Impatto**: Medio  
**Strategia Consigliata**: **Critical** o carica solo su pagine con form

#### 🎨 Fonts
**Rilevati**: Google Fonts, Font Awesome

**Impatto**: Medio  
**Strategia Consigliata**: **Preload** + `font-display: swap`

#### 📹 Media
**Rilevati**: YouTube, Vimeo, player video

**Impatto**: Molto Alto  
**Strategia Consigliata**: **Lazy load** (carica solo quando visibile)

### Come Usarlo?

#### Passo 1: Esegui Scansione

1. Vai su **FP Performance > Script di Terze Parti**
2. Clicca **"Scansiona Sito"**
3. Inserisci URL da analizzare (o lascia vuoto per homepage)

#### Passo 2: Visualizza Report

```
=== SCRIPT TERZE PARTI RILEVATI ===

📊 Analytics (2 script)
- Google Analytics (gtag.js)
  Dimensione: ~45KB
  Strategia Attuale: Sync (blocca rendering)
  Suggerimento: On-Interaction ⚡
  Risparmio Potenziale: 0.8s FCP

- Hotjar
  Dimensione: ~55KB
  Strategia Attuale: Sync
  Suggerimento: Lazy Load ⚡
  Risparmio Potenziale: 0.5s FCP

💬 Chat (1 script)
- Intercom Widget
  Dimensione: ~220KB (!!)
  Strategia Attuale: Sync
  Suggerimento: On-Interaction ⚡⚡⚡
  Risparmio Potenziale: 2.1s LCP

🎨 Fonts (1 script)
- Google Fonts
  Dimensione: ~15KB
  Strategia Attuale: Blocking
  Suggerimento: Preload + Swap ⚡
  Risparmio Potenziale: 0.3s FCP

---
Risparmio Totale Stimato: 3.7 secondi
Miglioramento PageSpeed: +25 punti
```

#### Passo 3: Applica Ottimizzazioni

Hai 3 opzioni:

**A) Automatico (Consigliato)**
```
Clicca "Applica Tutto Automaticamente"
- Il plugin applicherà le strategie consigliate
- Manterrà script critici (payment, security) non ottimizzati
- Testerà che tutto funzioni
```

**B) Selettivo**
```
Seleziona singoli script:
☑️ Google Analytics → On-Interaction
☑️ Intercom → On-Interaction
☐ Stripe → Lascia come è (critical)
```

**C) Manuale con Assistenza**
```
Per ogni script puoi scegliere:
- Default: Nessuna ottimizzazione
- Defer: Carica dopo HTML
- Async: Carica in parallelo
- Lazy: Carica quando visibile
- On-Interaction: Carica dopo click/scroll
- Preload: Precarica
```

### Strategie Spiegate

#### 🔹 Default (Nessuna Ottimizzazione)
```html
<script src="script.js"></script>
```
- Carica subito, blocca rendering
- Usa per: Script critici, librerie base

#### 🔹 Defer
```html
<script src="script.js" defer></script>
```
- Carica in parallelo, esegue dopo DOM pronto
- Usa per: Script non critici ma necessari

#### 🔹 Async
```html
<script src="script.js" async></script>
```
- Carica ed esegue appena pronto
- Usa per: Script indipendenti (analytics)

#### 🔹 Lazy Load
```javascript
// Carica script quando elemento è visibile
if (elementInView) {
  loadScript('script.js');
}
```
- Usa per: Widget, player video

#### 🔹 On-Interaction
```javascript
// Carica dopo primo click o scroll
document.addEventListener('click', () => {
  loadScript('script.js');
}, { once: true });
```
- Usa per: Chat, analytics, marketing

#### 🔹 Preload
```html
<link rel="preload" href="script.js" as="script">
<script src="script.js" defer></script>
```
- Carica in anticipo ma non esegue subito
- Usa per: Script importanti ma non immediati

### Esempi Pratici

#### Esempio 1: Ottimizzare Google Analytics

**Prima**:
```html
<!-- Caricato subito, blocca pagina -->
<script async src="https://www.googletagmanager.com/gtag/js?id=G-XXXXXXX"></script>
<script>
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  gtag('js', new Date());
  gtag('config', 'G-XXXXXXX');
</script>

Impatto:
- Blocca rendering: 0.8s
- PageSpeed Score: -8 punti
```

**Dopo (Ottimizzato)**:
```javascript
// Carica GA solo dopo interazione utente
let gaLoaded = false;
const loadGA = () => {
  if (gaLoaded) return;
  gaLoaded = true;
  
  // Carica script ora
  const script = document.createElement('script');
  script.src = 'https://www.googletagmanager.com/gtag/js?id=G-XXXXXXX';
  document.head.appendChild(script);
};

// Trigger dopo click o scroll
document.addEventListener('click', loadGA, { once: true });
document.addEventListener('scroll', loadGA, { once: true });

Risultato:
- Blocco rendering: 0s ✅
- PageSpeed Score: +8 punti ✅
- GA continua a tracciare tutto ✅
```

#### Esempio 2: Ottimizzare Chat Widget

**Prima**:
```html
<!-- Intercom caricato subito (220KB!) -->
<script>
  window.intercomSettings = {app_id: "xxx"};
  (function(){var w=window;var ic=w.Intercom;...})();
</script>

Impatto:
- Tempo caricamento: +2.1s
- LCP: +1.8s
- PageSpeed: -15 punti
```

**Dopo (Ottimizzato)**:
```html
<!-- Mostra solo icona placeholder -->
<div id="chat-bubble-placeholder" onclick="loadIntercom()">
  💬 Chat
</div>

<script>
let intercomLoaded = false;
function loadIntercom() {
  if (intercomLoaded) return;
  intercomLoaded = true;
  
  // Carica Intercom SOLO quando utente clicca
  window.intercomSettings = {app_id: "xxx"};
  // ... carica script vero
  
  document.getElementById('chat-bubble-placeholder').remove();
}
</script>

Risultato:
- Caricamento iniziale: NO impatto ✅
- Intercom si carica solo se necessario ✅
- PageSpeed: +15 punti ✅
- User Experience: Identica ✅
```

---

## ⚙️ Configurazione Automatica della Cache

### Cos'è?

Il **Page Cache Auto Configurator** configura automaticamente tutte le esclusioni cache necessarie basandosi sui plugin che hai installato.

### Problema che Risolve

**Scenario Comune**:

```
Utente installa WooCommerce
→ Attiva page cache
→ NON sa che deve escludere /cart e /checkout
→ Clienti vedono carrelli di altri utenti (DISASTRO!)
→ Disattiva cache frustrato
```

**Con Auto Configurator**:

```
Utente installa WooCommerce
→ Attiva FP Performance Suite
→ Plugin rileva WooCommerce automaticamente
→ Esclude automaticamente /cart, /checkout, /my-account
→ Tutto funziona perfettamente ✅
```

### Come Funziona?

Il sistema:

1. **Scansiona** i plugin attivi
2. **Identifica** plugin che richiedono esclusioni
3. **Applica** automaticamente le configurazioni corrette

### Plugin Supportati

#### 🛒 E-commerce

**WooCommerce**
```
Esclusioni automatiche:
- /cart/
- /checkout/
- /my-account/
- /shop/ (opzionale)
- ?add-to-cart=
- ?removed_item=
- /wc-ajax/*
```

**Easy Digital Downloads**
```
Esclusioni automatiche:
- /checkout/
- /purchase/
- /downloads/
- /edd-ajax/*
```

#### 👥 Membership

**MemberPress**
```
Esclusioni automatiche:
- /account/
- /membership/
- /register/
- /mepr/*
```

**Paid Memberships Pro**
```
Esclusioni automatiche:
- /membership-account/
- /membership-checkout/
```

#### 📚 LMS (Learning Management)

**LearnDash**
```
Esclusioni automatiche:
- /courses/
- /lessons/
- /topic/
- /quiz/
- User progress tracking pages
```

**LifterLMS**
```
Esclusioni automatiche:
- /dashboard/
- /courses/
- /memberships/
```

#### 💬 Community

**BuddyPress**
```
Esclusioni automatiche:
- /members/
- /groups/
- /activity/
- /profile/
```

**bbPress**
```
Esclusioni automatiche:
- /forums/
- /topic/
- /reply/
```

#### 📝 Forms

**Contact Form 7**
```
Esclusioni query string:
- ?wpcf7=
```

**Gravity Forms**
```
Esclusioni query string:
- ?gf_page=
- ?gform_submit=
```

### Come Usarlo?

#### Modalità 1: Automatica Completa

1. Vai su **FP Performance > Cache > Auto-Config**
2. Clicca **"Rileva e Applica Automaticamente"**

Il plugin:
- ✅ Scansiona plugin attivi
- ✅ Identifica configurazioni necessarie
- ✅ Applica automaticamente
- ✅ Ti notifica cosa ha fatto

```
=== CONFIGURAZIONE AUTOMATICA APPLICATA ===

Plugin Rilevati:
✅ WooCommerce v8.2.1
✅ LearnDash v4.8.0
✅ Contact Form 7 v5.8

Esclusioni Applicate (12):
- /cart/ (WooCommerce)
- /checkout/ (WooCommerce)
- /my-account/* (WooCommerce)
- /courses/* (LearnDash)
- /lessons/* (LearnDash)
- /quiz/* (LearnDash)
- ?wpcf7=* (Contact Form 7)
... e altre 5

✅ Configurazione completata!
Il tuo sito è ora ottimizzato e sicuro.
```

#### Modalità 2: Review e Approva

1. Vai su **FP Performance > Cache > Auto-Config**
2. Clicca **"Scansiona (Dry Run)"**
3. Rivedi i suggerimenti
4. Deseleziona ciò che non vuoi
5. Clicca **"Applica Selezionati"**

### Benefici

- ⚡ **Zero configurazione manuale**
- 🛡️ **Previene errori** (carrelli condivisi, dati utente sbagliati)
- 🎯 **Ottimizzazione intelligente** (cache massima senza rischi)
- 🔄 **Auto-aggiornamento** (se installi nuovo plugin, rileva ed adatta)

---

## 🚀 Prefetching Predittivo

### Cos'è?

Il **Predictive Prefetching** precarica le pagine che l'utente probabilmente visiterà, rendendo la navigazione istantanea.

### Come Funziona?

Ci sono 3 strategie:

#### 1. Hover Prefetching (Consigliato)
```
Utente passa il mouse sopra un link
→ Plugin aspetta 100ms
→ Se il mouse è ancora lì, precarica la pagina
→ Quando utente clicca, pagina già pronta!
```

**Risultato**: Pagina si apre ISTANTANEAMENTE ⚡

#### 2. Viewport Prefetching
```
Link entra nel viewport (visibile)
→ Plugin inizia a precaricare
→ Quando utente clicca, pagina pronta
```

**Uso**: Per siti con tanti link visibili

#### 3. Aggressive Prefetching
```
Appena pagina carica
→ Precarica TUTTI i link interni
```

⚠️ **Attenzione**: Usa molti dati, consigliato solo per siti piccoli

### Configurazione

1. Vai su **FP Performance > Avanzate > Prefetching**
2. Abilita prefetching
3. Scegli strategia:

```
○ Hover (Consigliato)
  Delay: 100ms [slider: 50-500ms]
  
○ Viewport
  Distanza trigger: 200px [slider]
  
○ Aggressive
  ⚠️ Alto uso banda
```

4. Configura esclusioni:

```
Escludi pattern:
- /wp-admin/
- /cart/
- /checkout/
- /download/
- *.pdf
```

5. Limiti:

```
Max pagine da precaricare: 5
Reset dopo: 30 secondi
```

### Esempi Pratici

#### Esempio 1: Blog

```
Configurazione:
- Strategia: Hover
- Delay: 100ms
- Limit: 5 pagine

Scenario:
Utente legge articolo
→ Passa mouse su "Articoli correlati"
→ Plugin precarica i 3 articoli
→ Utente clicca → Articolo si apre ISTANTANEAMENTE

Risultato:
- Navigation time: 0ms (sembra istantaneo)
- User experience: Eccezionale
- Bounce rate: -15%
```

#### Esempio 2: E-commerce

```
Configurazione:
- Strategia: Viewport
- Esclusioni: /cart/, /checkout/
- Limit: 3 prodotti

Scenario:
Utente naviga categoria
→ Vede griglia prodotti
→ Plugin precarica top 3 prodotti visibili
→ Utente clicca prodotto → Apertura istantanea

Risultato:
- Time to product page: -80%
- Conversions: +8%
```

---

## 🎨 Ottimizzatore Automatico dei Font

### Cos'è?

L'**Auto Font Optimizer** rileva automaticamente i font utilizzati e applica le ottimizzazioni migliori.

### Problemi che Risolve

**FOIT** (Flash of Invisible Text):
```
Pagina carica → Testo invisibile → Font carica → Testo appare
                 ^-- 2-3 secondi di schermo bianco
```

**FOUT** (Flash of Unstyled Text):
```
Pagina carica → Testo con font di sistema → Font carica → Cambio brusco
                 ^-- Visibile ma brutto
```

### Soluzioni Applicate

#### 1. font-display: swap

```css
@font-face {
  font-family: 'MyFont';
  src: url('font.woff2');
  font-display: swap; /* ← Aggiunto automaticamente */
}
```

**Risultato**:
- Testo visibile subito (con font di sistema)
- Font personalizzato caricato in background
- Swap fluido quando pronto

#### 2. Preload Font Critici

```html
<!-- Font principale precari caricato -->
<link rel="preload" 
      href="/fonts/main-font.woff2" 
      as="font" 
      type="font/woff2" 
      crossorigin>
```

**Risultato**:
- Font principali caricati con priorità
- Riduzione FOUT

#### 3. Preconnect ai CDN

```html
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
```

**Risultato**:
- Connessione DNS stabilita in anticipo
- Font Google caricano 200-400ms più veloci

### Come Funziona?

Il sistema:

1. **Scansiona** CSS del sito
2. **Identifica** font utilizzati
3. **Rileva** Google Fonts, Font Awesome, font locali
4. **Applica** automaticamente:
   - `font-display: swap`
   - Preload per font critici
   - Preconnect per CDN

### Configurazione

1. Vai su **FP Performance > Font > Auto Optimizer**
2. Clicca **"Scansiona Font"**

```
=== FONT RILEVATI ===

Google Fonts (2):
- Roboto (400, 700)
  Dimensione: ~32KB
  Usato in: body, headings
  ✅ Critical font → Preload
  
- Open Sans (400)
  Dimensione: ~18KB
  Usato in: buttons
  ⚪ Non critical → Load normally

Font Locali (1):
- CustomFont.woff2
  Dimensione: ~45KB
  Usato in: logo, headings
  ✅ Critical → Preload

Font Awesome (1):
- Font Awesome 5 Free
  Dimensione: ~75KB
  Usato in: icons
  ⚪ Non critical → Lazy load
```

3. Rivedi e applica:

```
☑️ Applica font-display: swap a tutti i font
☑️ Preload font critici (Roboto, CustomFont)
☑️ Preconnect a Google Fonts CDN
☐ Lazy load Font Awesome (opzionale)
```

4. Clicca **"Applica Ottimizzazioni"**

### Risultati

- ⚡ FCP migliorato di 0.3-0.8s
- ✅ Nessun FOIT
- ✅ FOUT minimizzato
- 📈 PageSpeed +5-10 punti

---

## 🎯 Rilevamento Asset Critici

### Cos'è?

Il **Critical Assets Detector** identifica quali CSS e JavaScript sono veramente "critici" per il rendering iniziale della pagina.

### Concetti Base

**CSS Critico** = CSS necessario per renderizzare la parte visibile della pagina (above the fold)

**CSS Non Critico** = Tutto il resto (footer, sidebar non visibile, ecc.)

### Come Funziona?

1. **Carica la pagina** in modalità headless
2. **Identifica** elementi above-the-fold
3. **Estrae** solo CSS usato da questi elementi
4. **Genera** CSS critico inline
5. **Differisce** il resto

### Utilizzo

1. Vai su **FP Performance > Avanzate > Critical CSS**
2. Scegli metodo:

#### Metodo A: Automatico (AI)

```
Inserisci URL: https://tuosito.com
Clicca "Genera Automaticamente"

Il plugin:
- Analizza la pagina
- Identifica CSS critico
- Lo estrae automaticamente
- Lo applica inline
```

#### Metodo B: Manuale

```
1. Usa tool esterno: https://www.sitelocity.com/critical-path-css-generator
2. Genera CSS critico
3. Copia e incolla qui
```

### Risultato

**Prima**:
```html
<head>
  <!-- 500KB di CSS, tutto blocking -->
  <link rel="stylesheet" href="style.css">
  <link rel="stylesheet" href="plugins.css">
  <link rel="stylesheet" href="theme.css">
</head>
```

**Dopo**:
```html
<head>
  <!-- 8KB di CSS critico inline -->
  <style id="critical-css">
    .header{...}
    .hero{...}
    .nav{...}
  </style>
  
  <!-- Resto caricato async -->
  <link rel="preload" href="full-style.css" as="style" onload="this.rel='stylesheet'">
</head>
```

**Benefici**:
- FCP: -60%
- LCP: -40%
- PageSpeed: +15-25 punti

---

## ❓ FAQ e Risoluzione Problemi

### Q: Le funzionalità intelligenti sono sicure?

**R**: Sì! Il plugin:
- ✅ Esegue sempre un "dry run" prima di applicare modifiche
- ✅ Crea backup automatici
- ✅ Permette rollback con 1 click
- ✅ Non tocca MAI payment o security script

### Q: Il rilevamento automatico può sbagliare?

**R**: Raramente, ma sì. Per questo:
- Mostriamo sempre il "confidence score"
- Puoi rivedere prima di applicare
- Puoi escludere manualmente
- Log completo di ogni azione

### Q: Cosa succede se installo un nuovo plugin?

**R**: Il sistema:
1. Rileva il nuovo plugin
2. Ti notifica se richiede configurazioni
3. Suggerisce esclusioni/ottimizzazioni
4. Opzionalmente applica automaticamente

### Q: Posso disabilitare le funzionalità intelligenti?

**R**: Sì! Vai su **FP Performance > Impostazioni > Intelligenza**

```
☐ Rilevamento automatico esclusioni
☐ Rilevamento script terze parti
☐ Configurazione automatica cache
☐ Prefetching predittivo
☐ Ottimizzazione automatica font
```

Disabilita ciò che non vuoi usare.

### Q: Il prefetching consuma troppa banda?

**R**: Configuralo meglio:

```
✅ Usa strategia "Hover" invece di "Aggressive"
✅ Riduci limit a 3 pagine
✅ Aumenta hover delay a 200ms
✅ Escludi pagine pesanti
```

### Q: Script di terze parti non funzionano dopo ottimizzazione

**R**: Alcune strategie sono troppo aggressive:

```
Soluzione:
1. Vai su Script di Terze Parti
2. Trova lo script problematico
3. Cambia strategia da "On-Interaction" a "Defer"
4. Oppure escludi completamente
```

### Q: Critical CSS rompe il layout

**R**: Il CSS critico potrebbe essere incompleto:

```
Soluzione 1: Rigenera CSS critico
- Usa viewport più grande (1920x1080)
- Includi più elementi

Soluzione 2: Aggiungi manualmente CSS mancante
- Identifica elemento rotto
- Aggiungi suo CSS al critico

Soluzione 3: Disabilita temporaneamente
- Testa senza critical CSS
- Confronta risultati
```

---

## 🎓 Best Practices

### 1. Inizia con Dry Run

Sempre testare prima di applicare:
```
✅ Dry Run → Rivedi → Applica
❌ Applica subito senza guardare
```

### 2. Monitora dopo Cambiamenti

Dopo aver applicato ottimizzazioni:
```
1. Test funzionale completo
2. PageSpeed Insights test
3. Monitora errori per 24h
4. Controlla user feedback
```

### 3. Ottimizza Gradualmente

Non tutto insieme:
```
Settimana 1: Esclusioni automatiche
Settimana 2: Script terze parti
Settimana 3: Prefetching
Settimana 4: Critical CSS
```

### 4. Mantieni Log

Il plugin logga tutto:
```
FP Performance > Logs
- Mostra tutte le modifiche
- Data e ora
- Confidence score
- Risultati
```

### 5. Backup Regolari

```
FP Performance > Backup
- Backup automatico prima di ogni cambio
- Retention: 30 giorni
- Restore con 1 click
```

---

## 📞 Supporto

### Documentazione Aggiuntiva

- [Guida Completa Amministratore](GUIDA_AMMINISTRATORE.md)
- [FAQ Generale](faq.md)
- [Risoluzione Problemi Comuni](TROUBLESHOOTING.md)

### Contatti

- **Email**: info@francescopasseri.com
- **GitHub**: https://github.com/franpass87/FP-Performance

---

**Versione Documento**: 1.0  
**Ultima Modifica**: 21 Ottobre 2025  
**Plugin Version**: FP Performance Suite v1.5.1

