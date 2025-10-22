# ISTRUZIONI INSTALLAZIONE DA ZIP

## IMPORTANTE: Elimina Prima la Vecchia Installazione

### Step 1: Elimina Via FTP/SSH

**Connettiti al server e:**

```bash
cd /homepages/20/d4299220163/htdocs/clickandbuilds/FPDevelopmentEnvironment/wp-content/plugins/

# Elimina completamente la directory
rm -rf FP-Performance/
```

**Via FTP:**
1. Vai su `/wp-content/plugins/`
2. Elimina la cartella `FP-Performance` (se esiste)

### Step 2: Carica il Nuovo ZIP

**File da usare:** `fp-performance-suite-wordpress.zip` (NON il vecchio)

**Via WordPress Admin:**
1. Vai su Plugin -> Aggiungi nuovo
2. Clicca "Carica plugin"
3. Seleziona: `fp-performance-suite-wordpress.zip`
4. Clicca "Installa ora"
5. Attiva il plugin

### Step 3: Verifica

- Menu "FP Performance" dovrebbe apparire
- Tutte le sezioni accessibili
- Nessun errore

---

## SE ANCORA DA ERRORE "File non esiste"

### Soluzione: Installazione Manuale via FTP

1. Estrai `fp-performance-suite-wordpress.zip` sul tuo computer
2. Dovresti vedere una cartella `fp-performance-suite`
3. Carica questa cartella via FTP in:
   ```
   /wp-content/plugins/fp-performance-suite/
   ```
4. Vai su WordPress -> Plugin -> Plugin Installati
5. Dovresti vedere "FP Performance Suite"
6. Attiva

---

## STRUTTURA CORRETTA

Dopo l'installazione, sul server dovrebbe esserci:

```
/wp-content/plugins/fp-performance-suite/
├── fp-performance-suite.php         (FILE PRINCIPALE)
├── uninstall.php
├── LICENSE
├── readme.txt
└── fp-performance-suite/
    ├── src/
    │   ├── Plugin.php
    │   ├── ServiceContainer.php
    │   └── Services/
    │       ├── Cache/
    │       │   └── PageCache.php    (QUESTO DEVE ESSERCI!)
    │       └── ...
    ├── assets/
    ├── languages/
    └── views/
```

---

## FILE ZIP DISPONIBILI

- `fp-performance-suite-wordpress.zip` (NUOVO - USA QUESTO)
- `fp-performance-suite.zip` (vecchio)

---

Carica fp-performance-suite-wordpress.zip!

