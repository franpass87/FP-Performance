# 🚀 Guida Rapida - Ottimizzazione Database Avanzata

## Primi Passi

### 1. Controlla lo Stato del Tuo Database

**Via Admin:**
1. Vai su `FP Performance > Database`
2. Guarda il **Health Score** in alto (sezione viola con gradiente)
3. Se vedi **score < 80%**, hai margini di miglioramento!

**Via WP-CLI:**
```bash
wp fp-performance db health
```

---

## 🎯 Azioni Immediate Raccomandate

### Se hai Score < 60% (Voto D/F) - URGENTE 🔴

1. **Ottimizza Immediatamente:**
   ```bash
   wp fp-performance db optimize
   ```

2. **Controlla Frammentazione:**
   - Vai su "Analisi Avanzate" → "Frammentazione Tabelle"
   - Se vedi > 50MB recuperabili, clicca "Ottimizza Tutte le Tabelle"

3. **Controlla Autoload:**
   - Se vedi "Autoload > 1MB", disabilita le opzioni più grandi
   - Click su "Disabilita Autoload" per le prime 5-10 opzioni

### Se hai Score 60-79% (Voto C) - IMPORTANTE 🟡

1. **Converti Tabelle MyISAM (se presenti):**
   - Vai su "Storage Engine"
   - Click "Converti a InnoDB" per ogni tabella
   
   Oppure via CLI:
   ```bash
   wp fp-performance db convert-engine --all
   ```

2. **Aggiorna Charset Obsoleti:**
   - Vai su "Charset Obsoleti"
   - Converti tabelle a utf8mb4

3. **Plugin-Specific Cleanup:**
   - Se usi WooCommerce/Elementor, vai alla sezione dedicata
   - Seleziona cosa pulire e clicca "Esegui Pulizia"

### Se hai Score > 80% (Voto A/B) - MANTENIMENTO ✅

1. **Crea uno Snapshot:**
   - Vai su "Report & Trend"
   - Crea snapshot con etichetta "Stato Ottimale"

2. **Programma Manutenzione:**
   ```bash
   # Aggiungi a crontab - Ogni domenica alle 2am
   0 2 * * 0 wp fp-performance db health
   ```

---

## 💾 Operazioni di Pulizia Rapide

### WooCommerce

**Via Admin:**
1. Vai su "Ottimizzazioni Plugin-Specific"
2. Trova sezione WooCommerce
3. Seleziona:
   - ✅ Pulisci sessioni scadute
   - ✅ Elimina ordini vecchi (>30gg)
   - ✅ Pulisci log Action Scheduler
4. Click "Esegui Pulizia WooCommerce"

**Risparmio Tipico:** 20-200 MB

### Elementor

**Via Admin:**
1. Sezione "Elementor" 
2. Seleziona:
   - ✅ Elimina revisioni Elementor
   - ✅ Rigenera cache CSS
3. Click "Esegui Pulizia Elementor"

**Risparmio Tipico:** 50-500 MB (le revisioni Elementor sono enormi!)

---

## 📊 Monitoraggio e Report

### Crea Report Mensile

```bash
# Genera report JSON con tutte le metriche
wp fp-performance db report --format=json > report-$(date +%Y%m).json

# Oppure CSV per Excel
wp fp-performance db report --format=csv > report-$(date +%Y%m).csv
```

### Analizza Trend Crescita

**Via Admin:**
1. Vai su "Report & Trend"
2. Controlla "Crescita Database"
3. Vedi proiezioni a 30 e 90 giorni

Se crescita > 10 MB/giorno → Identifica la fonte:
- Troppi log?
- Plugin che salva troppi dati?
- Revisioni non limitate?

---

## 🔍 Troubleshooting

### Problema: Database Troppo Grande

**Soluzione:**
```bash
# 1. Analizza cosa occupa spazio
wp fp-performance db fragmentation

# 2. Cleanup plugin-specific
wp fp-performance db plugin-cleanup

# 3. Ottimizza
wp fp-performance db optimize
```

### Problema: Query Lente

**Soluzione:**
1. Vai su "Indici Mancanti"
2. Crea gli indici suggeriti ad alta priorità
3. Esegui `OPTIMIZE TABLE` sulle tabelle più grandi

### Problema: Autoload Pesante (> 1MB)

**Soluzione:**
1. Vai su "Opzioni Autoload Grandi"
2. Identifica plugin responsabili
3. Disabilita autoload per opzioni > 100KB
4. Test del sito dopo ogni modifica

---

## ⚡ Comandi Utili WP-CLI

```bash
# Health check completo
wp fp-performance db health

# Analisi frammentazione
wp fp-performance db fragmentation

# Cleanup plugin automatico
wp fp-performance db plugin-cleanup

# Report completo
wp fp-performance db report --format=json

# Converti tutto a InnoDB
wp fp-performance db convert-engine --all

# Ottimizza tabelle
wp fp-performance db optimize

# Analisi database
wp fp-performance db analyze
```

---

## 📅 Routine di Manutenzione Raccomandata

### **Settimanale (5 minuti):**
- [ ] Controlla Health Score
- [ ] Se score < 80%, esegui ottimizzazioni

### **Mensile (15 minuti):**
- [ ] Crea snapshot
- [ ] Genera report
- [ ] Analizza trend
- [ ] Plugin-specific cleanup

### **Trimestrale (30 minuti):**
- [ ] Review completo database
- [ ] Conversione charset se necessario
- [ ] Conversione storage engine se necessario
- [ ] Audit indici

---

## 🎯 KPI da Monitorare

### Metriche Chiave:
- **Health Score:** Mantenere > 80%
- **Autoload Size:** < 1 MB
- **Frammentazione:** < 5% o < 50 MB
- **Storage Engine:** 100% InnoDB
- **Charset:** 100% utf8mb4

### Trend:
- **Crescita DB:** < 5 MB/giorno per blog, < 20 MB/giorno per e-commerce
- **Numero Tabelle:** Stabile o crescita lenta
- **Revisioni:** Configurare limite automatico

---

## 💡 Tips Pro

1. **Prima di Grossi Update:**
   - Crea snapshot con label "Pre-Update Plugin-X"
   - Esporta report JSON
   - Score > 70% raccomandato

2. **Siti E-commerce:**
   - Pulisci WooCommerce sessioni settimanalmente
   - Archivia ordini vecchi mensilmente
   - Monitora tabelle Action Scheduler

3. **Siti con Page Builder:**
   - Limita revisioni a 3-5
   - Pulisci revisioni Elementor/Divi mensilmente
   - Cache CSS rigenera dopo pulizia

4. **Multisite:**
   - Ottimizza ogni sub-site separatamente
   - Attenzione a site-transients condivisi
   - Usa snapshot per tracciare crescita per site

---

## ❓ FAQ Rapide

**Q: È sicuro disabilitare autoload?**  
A: Sì, ma testa dopo. Alcune opzioni critiche potrebbero rallentare se non autoload. Inizia con le più grandi.

**Q: MyISAM vs InnoDB - quale differenza?**  
A: InnoDB è più moderno: supporta transazioni, foreign keys, crash recovery. MyISAM è obsoleto.

**Q: Quanto spesso ottimizzare?**  
A: Dipende dal traffico. Sito medio: mensile. E-commerce alto traffico: settimanale.

**Q: Posso automatizzare tutto?**  
A: Sì! Usa WP-CLI + cron per health check e report automatici. Operazioni critiche falle manualmente.

---

## 🆘 Supporto

Se incontri problemi:
1. Controlla i log: `wp-content/debug.log`
2. Verifica permessi database
3. Crea snapshot prima di operazioni critiche
4. I backup automatici sono in `wp_options` (chiave: `fp_ps_db_optimizer_backup`)

---

**Buona ottimizzazione! 🚀**

*Ultima revisione: Ottobre 2025*

