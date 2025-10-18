# Guida alla Creazione dello ZIP per WordPress

Questo documento spiega come creare correttamente un pacchetto ZIP del plugin per l'installazione su WordPress.

## Struttura Corretta dello ZIP

Per essere installato correttamente su WordPress, lo ZIP deve avere questa struttura:

```
fp-performance-suite.zip
└── fp-performance-suite/
    ├── fp-performance-suite.php  (file principale del plugin)
    ├── readme.txt               (file readme WordPress)
    ├── LICENSE                  (licenza)
    ├── uninstall.php           (script di disinstallazione)
    ├── src/                    (codice sorgente PHP)
    ├── assets/                 (CSS, JS, immagini)
    ├── languages/              (file di traduzione)
    ├── views/                  (template PHP)
    └── vendor/                 (dipendenze Composer)
```

**IMPORTANTE**: Lo ZIP deve contenere una cartella con il nome del plugin, non i file direttamente nella root dello ZIP.

## Metodo 1: Script Build Locale (build.sh)

### Prerequisiti
- Bash
- PHP CLI
- Composer
- Comando `rsync`
- Comando `zip`

### Utilizzo

```bash
cd fp-performance-suite

# Build con versione patch (incrementa 1.1.0 -> 1.1.1)
bash build.sh

# Build con versione minor (incrementa 1.1.0 -> 1.2.0)
bash build.sh --bump=minor

# Build con versione major (incrementa 1.1.0 -> 2.0.0)
bash build.sh --bump=major

# Build con versione specifica
bash build.sh --set-version=1.5.0

# Build con nome ZIP personalizzato
bash build.sh --zip-name=mio-plugin
```

Lo script:
1. Installa le dipendenze Composer (solo produzione)
2. Ottimizza l'autoloader
3. Copia i file necessari in una directory staging
4. Esclude file di sviluppo e test
5. Crea lo ZIP con la struttura corretta

Il file ZIP sarà in: `fp-performance-suite/build/`

## Metodo 2: GitHub Actions

### Workflow Automatici

#### Build su Tag (Releases)
File: `.github/workflows/build-plugin-zip.yml`

Quando viene creato un tag:
```bash
git tag v1.2.0
git push origin v1.2.0
```

Il workflow crea automaticamente lo ZIP e lo carica come artifact.

#### Build su Push/Pull Request
File: `.github/workflows/build.yml`

Il workflow viene eseguito automaticamente su:
- Push al branch `main`
- Apertura di Pull Request

Lo ZIP è disponibile come artifact nella sezione Actions di GitHub.

## Verifica della Struttura

Prima di installare il plugin, verifica la struttura:

```bash
cd fp-performance-suite
bash verify-zip-structure.sh
```

Lo script controlla:
- ✓ Presenza di tutti i file essenziali
- ✓ Presenza di tutte le directory necessarie
- ✓ Header del plugin corretti
- ✓ Versione del plugin
- ⚠ File che saranno esclusi nel build

## File Esclusi dal ZIP

I seguenti file/directory NON sono inclusi nello ZIP di produzione:

### Directory di sviluppo
- `.git/`, `.github/` - File Git
- `tests/` - Test PHPUnit
- `docs/` - Documentazione sviluppatore
- `examples/` - Esempi di codice
- `tools/`, `bin/` - Script di utilità
- `node_modules/` - Dipendenze Node.js
- `build/` - Directory di build

### File di configurazione
- `.gitignore`, `.gitattributes`
- `phpunit.xml.dist`
- `phpcs.xml.dist`
- `phpstan.neon.dist`
- `composer.lock`
- `.phpunit.result.cache`
- File `.md` (Markdown)
- `build.sh`
- `README-BUILD.md`

### File di sistema
- `.DS_Store` (macOS)
- `Thumbs.db` (Windows)
- `.idea/`, `.vscode/` (IDE)

### Directory WordPress extra
- `wp-content/`, `wp-admin/`, `wp-includes/`

## File Inclusi nel ZIP

### File Essenziali
- ✓ `fp-performance-suite.php` - File principale
- ✓ `readme.txt` - README WordPress
- ✓ `LICENSE` - Licenza GPL
- ✓ `uninstall.php` - Script disinstallazione

### Directory Essenziali
- ✓ `src/` - Codice sorgente PHP
- ✓ `assets/` - CSS, JavaScript, immagini
- ✓ `languages/` - File traduzione (.pot)
- ✓ `views/` - Template PHP
- ✓ `vendor/` - Dipendenze Composer (autoloader)

## Installazione su WordPress

### Via Dashboard WordPress
1. Vai su **Plugin → Aggiungi nuovo**
2. Clicca su **Carica plugin**
3. Seleziona il file `fp-performance-suite.zip`
4. Clicca su **Installa ora**
5. Clicca su **Attiva**

### Via FTP/SSH
1. Estrai lo ZIP localmente
2. Carica la cartella `fp-performance-suite/` in `wp-content/plugins/`
3. Vai su **Plugin** nel dashboard
4. Attiva **FP Performance Suite**

### Via WP-CLI
```bash
wp plugin install fp-performance-suite.zip --activate
```

## Risoluzione Problemi

### Errore: "Il pacchetto non contiene un plugin valido"
**Causa**: La struttura dello ZIP non è corretta.

**Soluzione**: Assicurati che lo ZIP contenga una cartella con il nome del plugin, non i file direttamente nella root.

### Errore: "Plugin manca dipendenze"
**Causa**: La directory `vendor/` non è presente nello ZIP.

**Soluzione**: Esegui `composer install --no-dev` prima di creare lo ZIP.

### Errore: "Fatal error: Class not found"
**Causa**: L'autoloader Composer non è ottimizzato.

**Soluzione**: Esegui `composer dump-autoload -o --classmap-authoritative` prima del build.

## Checklist Pre-Installazione

Prima di creare lo ZIP per la distribuzione:

- [ ] Versione aggiornata in `fp-performance-suite.php`
- [ ] Versione aggiornata in `readme.txt`
- [ ] `composer install --no-dev` eseguito
- [ ] `composer dump-autoload -o --classmap-authoritative` eseguito
- [ ] Test PHPUnit passati
- [ ] PHPCS e PHPStan senza errori
- [ ] `verify-zip-structure.sh` eseguito con successo
- [ ] Testato l'installazione su WordPress pulito

## Supporto

Per problemi o domande:
- Documentazione: `/docs`
- Repository: https://github.com/[tuo-username]/fp-performance-suite
- Website: https://francescopasseri.com
