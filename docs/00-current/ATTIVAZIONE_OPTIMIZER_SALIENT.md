# ⚡ Guida Rapida: Attivazione Optimizer Salient + WPBakery

## 🚀 Quick Start (3 passi)

### 1️⃣ Verifica Prerequisiti

Prima di procedere, assicurati di avere:
- ✅ Tema **Salient** attivo
- ✅ Plugin **WPBakery Page Builder** installato e attivo
- ✅ **FP Performance Suite v1.7.0+** installato

### 2️⃣ Attiva Compatibilità Tema

1. Vai su **WP Admin** → **FP Performance** → **🔧 Settings**
2. Nella sezione **"General Settings"**, cerca l'opzione:
   ```
   ☑️ Abilita Theme Compatibility
   ```
3. **Spunta la checkbox** e clicca **"Salva Impostazioni"**

> **Nota:** Questo abilita il sistema di compatibilità temi che include l'optimizer Salient/WPBakery

### 3️⃣ Configura Ottimizzazioni

1. Vai su **FP Performance** → **🎨 Theme**
2. Verifica che il tema sia rilevato correttamente:
   ```
   🎨 Tema Attivo: Salient
   🛠️ Page Builder: wpbakery
   ```
3. Abilita le ottimizzazioni desiderate (tutte consigliate):
   - ✅ **Abilita Ottimizzazioni** - Master switch
   - ✅ **Ottimizza Script** - Preserva script critici
   - ✅ **Ottimizza Stili** - Rimuove CSS non necessari
   - ✅ **Fix CLS** - Previene layout shift
   - ✅ **Ottimizza Animazioni** - Lazy load intelligente
   - ✅ **Ottimizza Parallax** - Disabilita su rete lenta
   - ✅ **Precarica Asset Critici** - Font icons
   - ✅ **Cache Contenuto Builder** - Purge automatico
4. Clicca **"💾 Salva Configurazione"**

## ✅ Verifica Funzionamento

### Test Rapido

1. **Frontend**: Apri la tua homepage
2. **DevTools**: Apri Console (F12)
3. Cerca il messaggio:
   ```
   [FP Performance] Salient/WPBakery optimizer: active
   ```

### Test Completo

1. **PageSpeed Insights**: https://pagespeed.web.dev/
2. Inserisci l'URL del tuo sito
3. Verifica miglioramenti in:
   - **LCP** (Large Contentful Paint) < 2.5s
   - **CLS** (Cumulative Layout Shift) < 0.1
   - **FID** (First Input Delay) < 100ms

## 🔧 Troubleshooting

### "Tema non rilevato"

**Problema:** La pagina Theme mostra "Tema Non Ottimizzato Automaticamente"

**Soluzioni:**
1. Verifica che Salient sia il tema **genitore** (non child theme)
2. Se usi un child theme, il rilevamento funziona comunque basandosi sul template
3. Controlla che WPBakery sia nella lista plugin attivi

### "Slider non funziona dopo attivazione"

**Problema:** Lo slider Nectar non si carica

**Soluzione:**
1. Vai su **🎨 Theme**
2. Verifica che **"Ottimizza Script"** sia **abilitato** (preserva script slider)
3. Pulisci cache: **FP Performance** → **Cache** → **Purge All**
4. Ricarica la pagina con Ctrl+F5

### "Animazioni non partono"

**Problema:** Le animazioni Salient non si attivano

**Soluzione:**
1. Controlla console browser per errori JS
2. Disabilita temporaneamente **"Ottimizza Animazioni"**
3. Se funziona, potrebbe esserci conflitto con altro plugin
4. Contatta supporto con dettagli

## 🎯 Configurazione Consigliata

### Setup Base (Tutte le installazioni)

```
✅ Abilita Ottimizzazioni
✅ Ottimizza Script
✅ Fix CLS
✅ Precarica Asset Critici
```

### Setup Avanzato (Per massime performance)

```
✅ Abilita Ottimizzazioni
✅ Ottimizza Script
✅ Ottimizza Stili
✅ Fix CLS
✅ Ottimizza Animazioni
✅ Ottimizza Parallax
✅ Precarica Asset Critici
✅ Cache Contenuto Builder
```

### Setup Conservativo (In caso di problemi)

```
✅ Abilita Ottimizzazioni
✅ Fix CLS
✅ Precarica Asset Critici
❌ Ottimizza Script (disabilitato)
❌ Ottimizza Stili (disabilitato)
❌ Ottimizza Animazioni (disabilitato)
```

## 📊 Risultati Attesi

Dopo aver abilitato le ottimizzazioni, dovresti vedere:

| Metrica | Miglioramento Tipico |
|---------|---------------------|
| **Velocità Caricamento** | 🔽 40-60% |
| **LCP** | 🔽 50% |
| **CLS** | 🔽 70% |
| **Dimensione Pagina** | 🔽 30-50% |
| **Richieste HTTP** | 🔽 40% |

## 🔄 Prossimi Passi

Dopo aver attivato l'optimizer:

1. **Abilita Edge Cache** (Cloudflare)
   - Vai su **FP Performance** → **Cache**
   - Configura Cloudflare per purge automatico

2. **Abilita Object Cache** (Redis/Memcached)
   - Se il tuo hosting lo supporta
   - Migliora performance database

3. **Monitora Core Web Vitals**
   - Vai su **FP Performance** → **📊 Monitoring**
   - Abilita tracking LCP, CLS, FID

4. **Test su Mobile**
   - Usa Chrome DevTools mobile emulation
   - Verifica performance su 3G lento

## 📚 Risorse

- [Documentazione Completa](./SALIENT_WPBAKERY_OPTIMIZER.md)
- [Guida Configurazione Dettagliata](../01-user-guides/CONFIGURAZIONE_SALIENT_WPBAKERY.md)
- [Core Web Vitals](https://web.dev/vitals/)

## 💬 Supporto

Hai bisogno di aiuto?

- 📧 Email: support@francescopasseri.com
- 🌐 Website: https://francescopasseri.com/support
- 📖 Docs: `/docs` folder nel plugin

---

**Versione:** 1.7.0  
**Ultimo Aggiornamento:** 26 Ottobre 2025

