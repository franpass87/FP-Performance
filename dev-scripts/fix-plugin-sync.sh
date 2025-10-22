#!/bin/bash

###############################################################################
# FP Performance Suite - Fix Plugin Sync
# 
# Questo script risolve il WSOD causato da file plugin corrotti o vecchi sul server
# 
# CAUSA DEL PROBLEMA:
# - FontOptimizer.php sul server ha codice mescolato/vecchio
# - L'errore "unexpected variable $lighthouseFonts" indica versione corrotta
# 
# SOLUZIONE:
# 1. Backup del plugin attuale
# 2. Rimozione plugin corrotto
# 3. Upload plugin aggiornato e corretto
# 4. Clear cache OPcache
###############################################################################

# Colori per output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

echo -e "${BLUE}╔════════════════════════════════════════════════════════════╗${NC}"
echo -e "${BLUE}║  FP Performance Suite - Fix Plugin Sync                   ║${NC}"
echo -e "${BLUE}╚════════════════════════════════════════════════════════════╝${NC}"
echo ""

# Verifica che siamo nella directory corretta
if [ ! -f "fp-performance-suite.php" ]; then
    echo -e "${RED}❌ ERRORE: Esegui questo script dalla root del progetto FP-Performance${NC}"
    exit 1
fi

# Crea directory di backup se non esiste
BACKUP_DIR="backup-plugin-$(date +%Y%m%d-%H%M%S)"
mkdir -p "$BACKUP_DIR"

echo -e "${YELLOW}📦 Step 1: Creazione backup del plugin corrente...${NC}"
if [ -d "fp-performance-suite" ]; then
    cp -r fp-performance-suite "$BACKUP_DIR/"
    echo -e "${GREEN}✅ Backup creato in: $BACKUP_DIR${NC}"
else
    echo -e "${YELLOW}⚠️  Directory fp-performance-suite non trovata${NC}"
fi

# Verifica git status
echo -e "\n${YELLOW}🔍 Step 2: Verifica stato Git...${NC}"
git status --short

# Verifica se ci sono modifiche non committate
if [ -n "$(git status --porcelain)" ]; then
    echo -e "${YELLOW}⚠️  Ci sono modifiche non committate${NC}"
    read -p "Vuoi fare commit delle modifiche? (y/n) " -n 1 -r
    echo
    if [[ $REPLY =~ ^[Yy]$ ]]; then
        git add .
        git commit -m "Fix: Sync plugin files before WSOD fix"
        echo -e "${GREEN}✅ Commit effettuato${NC}"
    fi
fi

# Build del plugin aggiornato
echo -e "\n${YELLOW}🔨 Step 3: Build del plugin aggiornato...${NC}"

# Rimuovi zip vecchio se esiste
if [ -f "fp-performance-suite.zip" ]; then
    rm fp-performance-suite.zip
    echo -e "${GREEN}✅ Rimosso zip vecchio${NC}"
fi

# Crea nuovo zip del plugin
echo -e "${BLUE}Creazione nuovo zip del plugin...${NC}"
if [ -f "build-plugin.ps1" ]; then
    powershell -ExecutionPolicy Bypass -File build-plugin.ps1
else
    # Build manuale se lo script non esiste
    zip -r fp-performance-suite.zip \
        fp-performance-suite.php \
        uninstall.php \
        fp-performance-suite/ \
        languages/ \
        assets/ \
        -x "*.git*" "*.md" "*node_modules*" "*tests*" "*test-*"
fi

if [ -f "fp-performance-suite.zip" ]; then
    echo -e "${GREEN}✅ Plugin buildato: fp-performance-suite.zip${NC}"
    echo -e "${BLUE}   Dimensione: $(du -h fp-performance-suite.zip | cut -f1)${NC}"
else
    echo -e "${RED}❌ ERRORE: Build del plugin fallita${NC}"
    exit 1
fi

# Istruzioni per l'upload
echo -e "\n${GREEN}╔════════════════════════════════════════════════════════════╗${NC}"
echo -e "${GREEN}║  ✅ PLUGIN PRONTO PER L'UPLOAD                             ║${NC}"
echo -e "${GREEN}╚════════════════════════════════════════════════════════════╝${NC}"
echo ""
echo -e "${YELLOW}📋 ISTRUZIONI PER RISOLVERE IL WSOD:${NC}"
echo ""
echo -e "${BLUE}1. Connettiti al server via FTP o SSH${NC}"
echo ""
echo -e "${BLUE}2. Vai nella directory:${NC}"
echo "   /wp-content/plugins/"
echo ""
echo -e "${BLUE}3. RINOMINA la cartella corrotta:${NC}"
echo "   FP-Performance → FP-Performance-OLD"
echo ""
echo -e "${BLUE}4. Carica il nuovo file:${NC}"
echo "   fp-performance-suite.zip"
echo ""
echo -e "${BLUE}5. Estrai il file ZIP sul server${NC}"
echo ""
echo -e "${BLUE}6. IMPORTANTE - Pulisci la cache OPcache:${NC}"
echo "   - Via cPanel: PHP OPcache → Reset"
echo "   - Via SSH: touch /tmp/opcache-reset.txt"
echo "   - O visita: https://tuosito.com/fix-wsod-emergency.php?action=clear_cache"
echo ""
echo -e "${BLUE}7. Prova ad accedere al sito${NC}"
echo ""
echo -e "${GREEN}═══════════════════════════════════════════════════════════${NC}"
echo ""
echo -e "${YELLOW}💡 NOTA: Il backup è stato salvato in:${NC}"
echo "   $BACKUP_DIR"
echo ""
echo -e "${YELLOW}📂 File da caricare:${NC}"
echo "   fp-performance-suite.zip"
echo ""

# Opzione per copiare su clipboard (Windows)
if command -v clip.exe &> /dev/null; then
    echo -e "${BLUE}📋 Vuoi copiare il path completo del zip negli appunti? (y/n)${NC}"
    read -p "" -n 1 -r
    echo
    if [[ $REPLY =~ ^[Yy]$ ]]; then
        realpath fp-performance-suite.zip | clip.exe
        echo -e "${GREEN}✅ Path copiato negli appunti!${NC}"
    fi
fi

echo -e "\n${GREEN}✅ Script completato!${NC}"

