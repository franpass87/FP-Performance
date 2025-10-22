# 💼 Esempi Pratici - Ottimizzazione Database

## Scenario 1: E-commerce WooCommerce Lento

### Sintomi:
- Database 3.2 GB
- Checkout lento (2-3 secondi)
- Admin ordini lento
- Health Score: 48% (Voto F)

### Diagnosi (via WP-CLI):
```bash
$ wp fp-performance db health

Database Health Score
Score: 48%
Grade: F
Status: poor

Issues Found:
🔴 Frammentazione elevata (>285 MB) (penalty: -20)
🟡 12 tabelle MyISAM (raccomandato InnoDB) (penalty: -15)
🟡 Autoload eccessivo (3.2 MB) (penalty: -15)

$ wp fp-performance db plugin-cleanup

Plugin Cleanup Opportunities
Total potential savings: 312.45 MB
Total items to clean: 18,542

WooCommerce:
  Items: 15,234
  Potential savings: ~298.12 MB
  → 12,450 sessioni scadute
  → 2,784 ordini pending/failed vecchi
```

### Soluzione Implementata:

**Step 1: Cleanup WooCommerce**
```bash
# Via Admin: FP Performance > Database > Ottimizzazioni Plugin-Specific
# Seleziona tutto e click "Esegui Pulizia WooCommerce"
```

**Risultato:** -298 MB, 15,234 record eliminati

**Step 2: Ottimizza Frammentazione**
```bash
wp fp-performance db optimize
```

**Risultato:** Recuperati 285 MB di frammentazione

**Step 3: Converti a InnoDB**
```bash
wp fp-performance db convert-engine --all

Converting wp_wc_sessions...
✓ Success!
Converting wp_actionscheduler_logs...
✓ Success!
...
Converted 12 tables. Failed: 0
```

**Step 4: Ottimizza Autoload**
```bash
# Via Admin: Disabilita autoload per top 10 opzioni grandi
```

**Risultato:** Autoload da 3.2 MB → 0.9 MB

### Risultati Finali:

**Database:**
- Dimensione: 3.2 GB → 2.6 GB (**-18.7%**)
- Health Score: 48% → 89% (**+41 punti!**)
- Grade: F → B

**Performance:**
- Checkout: 2.8s → 1.1s (**-60%**)
- Admin ordini: 4.2s → 1.8s (**-57%**)
- Query catalogo: da 380ms a 180ms (**-52%**)

**ROI:**
- Tempo risparmiato: ~30 minuti di implementazione
- Storage risparmiato: 600 MB
- Costi hosting: Downgrade possibile da piano superiore

---

## Scenario 2: Blog con Yoast SEO - Autoload Pesante

### Sintomi:
- TTFB elevato (1.5s)
- Memory limit warnings
- Health Score: 62% (Voto D)

### Diagnosi:
```bash
$ wp fp-performance db health

Database Health Score
Score: 62%
Grade: D
Status: fair

Issues Found:
🟡 Autoload elevato (2.1 MB) (penalty: -5)
🟢 8 indici ad alta priorità mancanti (penalty: -10)
🟢 45 tabelle con charset obsoleto (penalty: -10)
```

### Analisi Dettagliata:
```bash
# Via Admin: Vai su "Opzioni Autoload Grandi"
```

**Top Colpevoli:**
1. `_yoast_wpseo_focuskw` - 450 KB
2. `elementor_css_...` (vari) - 380 KB
3. `wp_user_roles` - 280 KB
4. Transient vari - 200 KB

### Soluzione:

**Step 1: Disabilita Autoload Selettivo**
```bash
# Via Admin, click "Disabilita Autoload" su top 5 opzioni
```

**Step 2: Converti Charset**
```bash
# Via Admin: Sezione "Charset Obsoleti"
# Click "Converti a utf8mb4" su tutte le 45 tabelle
```

**Step 3: Crea Indici Mancanti**
```sql
-- Indici suggeriti (via phpMyAdmin o CLI)
ALTER TABLE wp_posts ADD INDEX idx_post_author (post_author);
ALTER TABLE wp_postmeta ADD INDEX idx_meta_key_value (meta_key(191), meta_value(191));
ALTER TABLE wp_options ADD INDEX idx_autoload (autoload);
```

### Risultati:

**Performance:**
- TTFB: 1.5s → 0.6s (**-60%**)
- Memory usage: -18%
- Health Score: 62% → 85% (**+23 punti**)

**Query Specifiche:**
- Author archives: 480ms → 85ms (**-82%**)
- Meta queries: 720ms → 180ms (**-75%**)

---

## Scenario 3: Sito con Elementor - Database Enorme

### Sintomi:
- Database 5.8 GB (!!)
- Backup giornaliero fallisce (timeout)
- Hosting vicinissimo a limite storage
- Health Score: 35% (Voto F)

### Diagnosi:
```bash
$ wp fp-performance db plugin-cleanup

Elementor:
  Items: 28,450
  Potential savings: ~1,420.5 MB
  → 8,500 revisioni Elementor
  → 19,950 file CSS cache
```

### Causa Root:
- Revisioni Elementor non limitate
- Ogni revisione: ~50-200 KB
- 8,500 revisioni × 150 KB avg = **1,275 MB!**

### Soluzione:

**Step 1: Pulizia Elementor**
```bash
# Via Admin: Sezione Elementor
# Seleziona entrambe opzioni:
# - Elimina revisioni Elementor
# - Rigenera cache CSS
```

**Risultato:** -1,420 MB liberati!

**Step 2: Limita Revisioni (Prevenzione)**
```php
// wp-config.php
define('WP_POST_REVISIONS', 3);
define('AUTOSAVE_INTERVAL', 300); // 5 minuti
```

**Step 3: Ottimizza Frammentazione**
```bash
wp fp-performance db optimize
```

**Risultato:** Recuperati altri 340 MB

### Risultati:

**Database:**
- Dimensione: 5.8 GB → 4.0 GB (**-31%**)
- Health Score: 35% → 78% (**+43 punti!**)

**Operazioni:**
- Backup: da timeout a 8 minuti
- Export DB: da 15 min a 6 min
- Storage: downgrade piano hosting possibile

**ROI:**
- Risparmio hosting: €15/mese (piano inferiore)
- Risparmio annuo: €180
- Tempo implementazione: 20 minuti

---

## Scenario 4: Multisite con 50 Subsites

### Sintomi:
- Crescita database incontrollata
- Site-transients che si accumulano
- Alcuni subsite molto più lenti di altri

### Analisi Trend:
```bash
$ wp fp-performance db report --format=text

Database Report
Generated: 2025-10-19 10:30:00
Database size: 12.4 GB
Overhead: 890 MB

Health Score: 58% (Grade: D)

Growth rate: 45.3 MB/day (!!)
30-day projection: 13.7 GB
90-day projection: 16.5 GB
```

**Allarme:** Crescita **45 MB/giorno** è eccessiva!

### Investigazione:
```bash
# Analizza per trovare la fonte
$ wp fp-performance db fragmentation

Fragmented Tables:
wp_12_postmeta    2,340 MB    45% fragmentation
wp_45_options      890 MB    38% fragmentation
```

**Trovata!** 
- Subsite #12: E-commerce con ACF, genera molta postmeta
- Subsite #45: Plugin logging aggressivo

### Soluzione:

**Step 1: Pulisci Subsite Problematici**
```bash
# Switch a subsite #12
wp --url=shop.example.com fp-performance db plugin-cleanup

# Cleanup WooCommerce + ACF
# Via admin di quel subsite
```

**Step 2: Disabilita Plugin Logging Inutile**
```bash
# Subsite #45
# Disattiva o configura retention logs
```

**Step 3: Ottimizza Tutti i Subsites**
```bash
# Script bash per tutti i subsites
for site in $(wp site list --field=url); do
  echo "Optimizing $site..."
  wp --url=$site fp-performance db optimize
done
```

### Risultati:

**Database:**
- Dimensione: 12.4 GB → 9.8 GB (**-21%**)
- Crescita: 45 MB/day → 8 MB/day (**-82%**)
- Proiezione 90 giorni: 16.5 GB → 10.5 GB

**Performance Subsites:**
- Subsite #12: TTFB da 2.1s a 0.9s
- Subsite #45: TTFB da 1.8s a 0.7s

---

## Scenario 5: Manutenzione Preventiva - Sito Già Buono

### Situazione Iniziale:
- Database: 890 MB
- Health Score: 82% (Voto B)
- Performance OK

### Obiettivo:
Mantenere performance eccellenti nel tempo

### Strategia:

**1. Snapshot Iniziale**
```bash
# Via Admin: Report & Trend > Crea Snapshot
# Label: "Baseline Ottobre 2025"
```

**2. Monitoring Automatico (Cron)**
```bash
# Aggiungi a crontab
0 2 * * 0 wp fp-performance db health >> /var/log/db-health.log
0 3 1 * * wp fp-performance db report --format=json > /backup/monthly/db-report-$(date +\%Y\%m).json
```

**3. Alert Personalizzato**
```bash
#!/bin/bash
# check-db-health.sh

SCORE=$(wp fp-performance db health --format=json | jq '.score')

if [ $SCORE -lt 70 ]; then
  echo "Database health score dropped to $SCORE%!" | mail -s "DB Alert" admin@example.com
fi
```

**4. Review Mensile**
- Primo lunedì del mese: Review report
- Controlla trend crescita
- Se crescita > 10 MB/giorno → Investigare

### Risultati Dopo 6 Mesi:

**Metriche:**
- Health Score: Stabile 82-88%
- Crescita media: 4.2 MB/giorno (controllata)
- Problemi: 0 (rilevati e risolti preventivamente)

**Benefici:**
- Zero emergenze database
- Performance stabile
- Costi hosting ottimizzati

---

## Automazione Completa: Script Esempio

```bash
#!/bin/bash
# db-maintenance.sh - Weekly Database Maintenance

echo "=== FP Performance Database Maintenance ==="
echo "Started: $(date)"

# 1. Health Check
echo -e "\n[1/5] Health Check..."
wp fp-performance db health

SCORE=$(wp fp-performance db health | grep "Score:" | awk '{print $2}' | tr -d '%')

# 2. Se score basso, ottimizza
if [ $SCORE -lt 80 ]; then
  echo -e "\n[2/5] Score below 80%, running optimizations..."
  wp fp-performance db optimize
else
  echo -e "\n[2/5] Score OK, skipping optimization"
fi

# 3. Plugin Cleanup
echo -e "\n[3/5] Plugin-specific cleanup..."
wp fp-performance db plugin-cleanup

# 4. Fragmentation check
echo -e "\n[4/5] Checking fragmentation..."
wp fp-performance db fragmentation

# 5. Generate report
echo -e "\n[5/5] Generating monthly report..."
wp fp-performance db report --format=json > "/backup/db-report-$(date +%Y%m%d).json"

echo -e "\nCompleted: $(date)"
echo "=== Maintenance Complete ==="
```

**Uso:**
```bash
# Aggiungi a crontab - Ogni domenica alle 3am
0 3 * * 0 /path/to/db-maintenance.sh >> /var/log/db-maintenance.log 2>&1
```

---

## Conclusioni e Best Practices

### ✅ Cose da Fare:
1. **Monitorare Health Score** settimanalmente
2. **Snapshot prima di operazioni** critiche
3. **Limitare revisioni** (max 3-5)
4. **Cleanup plugin-specific** mensile
5. **Review autoload** se TTFB alto

### ❌ Cose da NON Fare:
1. **Mai** ottimizzare durante picchi traffico
2. **Non** disabilitare autoload senza testare
3. **Non** ignorare backup prima conversioni
4. **Non** convertire tutto a InnoDB senza valutare
5. **Non** automatizzare operazioni critiche senza supervisione

### 📊 KPI Ideali:
- Health Score: > 80%
- Autoload: < 1 MB
- Crescita: < 10 MB/giorno
- Frammentazione: < 5%
- Storage Engine: 100% InnoDB
- Charset: 100% utf8mb4

---

**Buona ottimizzazione! 🚀**

