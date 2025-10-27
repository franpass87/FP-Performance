# 🎨 Linee Guida UI - Pagine Admin FP-Performance

## 📊 Analisi Coerenza Attuale

### Risultati Analisi
- **Pagine Analizzate:** 15
- **Coerenza fp-ps-card:** ✅ 100%
- **Coerenza breadcrumbs:** ✅ 87%
- **Coerenza description:** ✅ 87%

### ⚠️ Problemi Rilevati
- 🔴 **H1 con wrap div:** Solo 27% (4/15 pagine)
- 🔴 **Sistema semaforo:** Solo 33% (5/15 pagine)
- 🔴 **Intro box:** Solo 13% (2/15 pagine)
- 🔴 **Pagine senza wrap div:** 10/15 pagine

---

## 🎯 STANDARD UI OBBLIGATORIO

### 1. **Struttura Base Pagina**

```php
protected function content(): string
{
    ob_start();
    ?>
    <!-- INTRO BOX (Sempre presente) -->
    <div class="fp-ps-page-intro" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 30px; border-radius: 8px; margin-bottom: 30px; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
        <h2 style="margin: 0 0 15px 0; color: white; font-size: 28px;">
            [EMOJI] Titolo Pagina
        </h2>
        <p style="margin: 0; font-size: 16px; line-height: 1.6; opacity: 0.95;">
            Breve descrizione della pagina (1-2 righe)
        </p>
    </div>

    <!-- SEZIONI CON CARD -->
    <section class="fp-ps-card">
        <h2>Titolo Sezione</h2>
        <p class="description">Descrizione della sezione</p>
        
        <!-- Contenuto -->
    </section>
    
    <?php
    return ob_get_clean();
}
```

---

### 2. **Sistema Semaforo (Risk Indicators)**

#### Colori Standard
- 🔴 **RED (Rischio Alto):** `class="fp-ps-risk-indicator red"`
- 🟡 **AMBER (Rischio Medio):** `class="fp-ps-risk-indicator amber"`
- 🟢 **GREEN (Rischio Basso):** `class="fp-ps-risk-indicator green"`

#### HTML Template
```html
<span class="fp-ps-risk-indicator amber">
    <div class="fp-ps-risk-tooltip amber">
        <div class="fp-ps-risk-tooltip-title">
            <span class="icon">⚠</span>
            <?php esc_html_e('Rischio Medio', 'fp-performance-suite'); ?>
        </div>
        <div class="fp-ps-risk-tooltip-section">
            <div class="fp-ps-risk-tooltip-label">Descrizione</div>
            <div class="fp-ps-risk-tooltip-text">Cosa fa questa opzione</div>
        </div>
        <div class="fp-ps-risk-tooltip-section">
            <div class="fp-ps-risk-tooltip-label">Rischi</div>
            <div class="fp-ps-risk-tooltip-text">Possibili problemi</div>
        </div>
        <div class="fp-ps-risk-tooltip-section">
            <div class="fp-ps-risk-tooltip-label">Consiglio</div>
            <div class="fp-ps-risk-tooltip-text">⚡ Raccomandazione</div>
        </div>
    </div>
</span>
```

---

### 3. **Notice/Messaggi**

#### Tipi Standard
```php
// SUCCESS (Verde)
<div class="notice notice-success is-dismissible">
    <p>✅ <strong>Operazione completata!</strong> Messaggio di successo.</p>
</div>

// ERROR (Rosso)
<div class="notice notice-error">
    <p>❌ <strong>Errore!</strong> Descrizione errore.</p>
</div>

// WARNING (Giallo)
<div class="notice notice-warning">
    <p>⚠️ <strong>Attenzione!</strong> Messaggio di avviso.</p>
</div>

// INFO (Blu)
<div class="notice notice-info">
    <p>ℹ️ <strong>Informazione:</strong> Dettaglio informativo.</p>
</div>
```

---

### 4. **Emoji Standard per Pagine**

| Pagina | Emoji | Codice |
|--------|-------|--------|
| Overview | 📊 | `\u{1F4CA}` |
| Cache | 🚀 | `\u{1F680}` |
| Assets | 📦 | `\u{1F4E6}` |
| Database | 💾 | `\u{1F4BE}` |
| Mobile | 📱 | `\u{1F4F1}` |
| Security | 🔒 | `\u{1F512}` |
| Compression | 🗜️ | `\u{1F5DC}` |
| Backend | ⚙️ | `\u{2699}` |
| Media | 🖼️ | `\u{1F5BC}` |
| CDN | 🌐 | `\u{1F310}` |
| Logs | 📝 | `\u{1F4DD}` |
| Settings | ⚙️ | `\u{2699}` |
| Diagnostics | 🔍 | `\u{1F50D}` |
| Monitoring | 📈 | `\u{1F4C8}` |

---

### 5. **Card Standard**

```html
<section class="fp-ps-card">
    <h2>[Emoji] Titolo Sezione</h2>
    <p class="description">
        Descrizione breve della funzionalità
    </p>
    
    <!-- Box informativi colorati -->
    <div style="background: #f0f9ff; border-left: 4px solid #0ea5e9; padding: 15px; margin: 15px 0; border-radius: 4px;">
        <strong>💡 Suggerimento:</strong>
        Testo del suggerimento
    </div>
    
    <!-- Contenuto form/dati -->
</section>
```

---

### 6. **Box Informativi Colorati**

#### Success (Verde)
```html
<div style="background: #d1f2eb; border-left: 4px solid #28a745; padding: 15px; margin: 15px 0; border-radius: 4px;">
    <strong>✅ Successo:</strong> Messaggio positivo
</div>
```

#### Info (Blu)
```html
<div style="background: #f0f9ff; border-left: 4px solid #0ea5e9; padding: 15px; margin: 15px 0; border-radius: 4px;">
    <strong>ℹ️ Informazione:</strong> Messaggio informativo
</div>
```

#### Warning (Giallo)
```html
<div style="background: #fff3cd; border-left: 4px solid #ffc107; padding: 15px; margin: 15px 0; border-radius: 4px;">
    <strong>⚠️ Attenzione:</strong> Messaggio di avviso
</div>
```

#### Danger (Rosso)
```html
<div style="background: #f8d7da; border-left: 4px solid #dc3545; padding: 15px; margin: 15px 0; border-radius: 4px;">
    <strong>🔴 Pericolo:</strong> Messaggio critico
</div>
```

---

## 📋 CHECKLIST PAGINA

Prima di considerare completa una pagina admin, verificare:

- [ ] **Intro Box** presente con gradiente viola
- [ ] **Emoji** nel titolo appropriato per la pagina
- [ ] **Descrizione** chiara (1-2 righe)
- [ ] **Cards (fp-ps-card)** per ogni sezione
- [ ] **Sistema Semaforo** per opzioni rischiose
- [ ] **Notice** per feedback utente
- [ ] **Box informativi** colorati per suggerimenti
- [ ] **Breadcrumbs** configurati
- [ ] **Stile consistente** con altre pagine

---

## 🔧 PAGINE DA SISTEMARE

### Priorità Alta (Manca wrap div)
1. AIConfig
2. Cache
3. Database
4. Security
5. Compression
6. Backend
7. Cdn
8. Logs
9. Settings
10. MonitoringReports

### Priorità Media (Aggiungere intro box)
1. Assets
2. Mobile
3. Security
4. Compression
5. Media
6. Cdn
7. Logs
8. Settings
9. Backend
10. MonitoringReports

### Priorità Bassa (Standardizzare emoji)
- Tutte le pagine dovrebbero avere emoji consistenti

---

## 🎨 ESEMPI COMPLETI

### Esempio Pagina Semplice
```php
protected function content(): string
{
    ob_start();
    ?>
    <div class="fp-ps-page-intro" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 30px; border-radius: 8px; margin-bottom: 30px;">
        <h2 style="margin: 0 0 15px 0; color: white; font-size: 28px;">
            🚀 Cache Management
        </h2>
        <p style="margin: 0; font-size: 16px; opacity: 0.95;">
            Gestisci la cache del sito per migliorare le prestazioni
        </p>
    </div>

    <section class="fp-ps-card">
        <h2>⚙️ Configurazione Cache</h2>
        <p class="description">Imposta le opzioni di cache</p>
        
        <form method="post">
            <?php wp_nonce_field('fp_ps_cache', 'nonce'); ?>
            
            <label class="fp-ps-toggle">
                <span class="info">
                    <strong>Page Cache</strong>
                    <span class="fp-ps-risk-indicator green">
                        <!-- Tooltip -->
                    </span>
                </span>
                <input type="checkbox" name="enabled" value="1" />
            </label>
            
            <button type="submit" class="button button-primary">
                Salva Impostazioni
            </button>
        </form>
    </section>
    <?php
    return ob_get_clean();
}
```

---

**Ultimo Aggiornamento:** 2025-01-25  
**Versione:** 1.0

