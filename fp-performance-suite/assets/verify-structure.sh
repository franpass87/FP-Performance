#!/bin/bash

##
# Script di Verifica Struttura Modulare
# 
# Verifica che tutti i file della modularizzazione siano presenti
# e che la struttura sia corretta.
#
# @package FP\PerfSuite
##

echo "🔍 Verifica Struttura Modulare FP Performance Suite"
echo "=================================================="
echo ""

# Colori per output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Contatori
TOTAL_TESTS=0
PASSED_TESTS=0
FAILED_TESTS=0

# Directory base
BASE_DIR="$(dirname "$0")"

# Funzione per verificare esistenza file
check_file() {
    local file=$1
    local description=$2
    TOTAL_TESTS=$((TOTAL_TESTS + 1))
    
    if [ -f "$BASE_DIR/$file" ]; then
        echo -e "${GREEN}✓${NC} $description"
        PASSED_TESTS=$((PASSED_TESTS + 1))
        return 0
    else
        echo -e "${RED}✗${NC} $description"
        echo "   File mancante: $file"
        FAILED_TESTS=$((FAILED_TESTS + 1))
        return 1
    fi
}

# Funzione per verificare esistenza directory
check_dir() {
    local dir=$1
    local description=$2
    TOTAL_TESTS=$((TOTAL_TESTS + 1))
    
    if [ -d "$BASE_DIR/$dir" ]; then
        echo -e "${GREEN}✓${NC} $description"
        PASSED_TESTS=$((PASSED_TESTS + 1))
        return 0
    else
        echo -e "${RED}✗${NC} $description"
        echo "   Directory mancante: $dir"
        FAILED_TESTS=$((FAILED_TESTS + 1))
        return 1
    fi
}

# Verifica Directory
echo "📁 Verifica Directory"
echo "-------------------"
check_dir "css" "Directory CSS"
check_dir "css/base" "Directory CSS Base"
check_dir "css/layout" "Directory CSS Layout"
check_dir "css/components" "Directory CSS Components"
check_dir "css/utilities" "Directory CSS Utilities"
check_dir "css/themes" "Directory CSS Themes"
check_dir "js" "Directory JavaScript"
check_dir "js/utils" "Directory JS Utils"
check_dir "js/components" "Directory JS Components"
check_dir "js/features" "Directory JS Features"
check_dir "legacy" "Directory Legacy (backup)"
echo ""

# Verifica File CSS
echo "🎨 Verifica File CSS"
echo "------------------"
check_file "css/admin.css" "CSS Entry Point"
check_file "css/base/variables.css" "CSS Variables"
check_file "css/layout/wrap.css" "Layout Wrap"
check_file "css/layout/header.css" "Layout Header"
check_file "css/layout/grid.css" "Layout Grid"
check_file "css/layout/card.css" "Layout Card"
check_file "css/components/badge.css" "Component Badge"
check_file "css/components/toggle.css" "Component Toggle"
check_file "css/components/tooltip.css" "Component Tooltip"
check_file "css/components/table.css" "Component Table"
check_file "css/components/log-viewer.css" "Component Log Viewer"
check_file "css/components/actions.css" "Component Actions"
check_file "css/utilities/score.css" "Utility Score"
check_file "css/themes/dark-mode.css" "Theme Dark Mode"
check_file "css/themes/high-contrast.css" "Theme High Contrast"
check_file "css/themes/reduced-motion.css" "Theme Reduced Motion"
check_file "css/themes/print.css" "Theme Print"
echo ""

# Verifica File JavaScript
echo "⚡ Verifica File JavaScript"
echo "------------------------"
check_file "js/main.js" "JS Entry Point"
check_file "js/utils/http.js" "Utils HTTP"
check_file "js/utils/dom.js" "Utils DOM"
check_file "js/components/notice.js" "Component Notice"
check_file "js/components/progress.js" "Component Progress"
check_file "js/components/confirmation.js" "Component Confirmation"
check_file "js/features/log-viewer.js" "Feature Log Viewer"
check_file "js/features/presets.js" "Feature Presets"
check_file "js/features/bulk-actions.js" "Feature Bulk Actions"
echo ""

# Verifica File Backup
echo "💾 Verifica File Backup"
echo "---------------------"
check_file "legacy/admin.css.bak" "Backup CSS Originale"
check_file "legacy/admin.js.bak" "Backup JS Originale"
echo ""

# Verifica Documentazione
echo "📚 Verifica Documentazione"
echo "------------------------"
check_file "README.md" "README Documentazione"
check_file "VERIFICATION.md" "File Verifica"
echo ""

# Verifica Contenuto File Principali
echo "📝 Verifica Contenuto File"
echo "-------------------------"

# Conta import CSS
if [ -f "$BASE_DIR/css/admin.css" ]; then
    CSS_IMPORTS=$(grep -c "@import" "$BASE_DIR/css/admin.css")
    TOTAL_TESTS=$((TOTAL_TESTS + 1))
    if [ "$CSS_IMPORTS" -eq 16 ]; then
        echo -e "${GREEN}✓${NC} CSS admin.css contiene 16 import"
        PASSED_TESTS=$((PASSED_TESTS + 1))
    else
        echo -e "${RED}✗${NC} CSS admin.css dovrebbe contenere 16 import, trovati: $CSS_IMPORTS"
        FAILED_TESTS=$((FAILED_TESTS + 1))
    fi
fi

# Conta import JavaScript
if [ -f "$BASE_DIR/js/main.js" ]; then
    JS_IMPORTS=$(grep -c "^import" "$BASE_DIR/js/main.js")
    TOTAL_TESTS=$((TOTAL_TESTS + 1))
    if [ "$JS_IMPORTS" -eq 6 ]; then
        echo -e "${GREEN}✓${NC} JS main.js contiene 6 import"
        PASSED_TESTS=$((PASSED_TESTS + 1))
    else
        echo -e "${RED}✗${NC} JS main.js dovrebbe contenere 6 import, trovati: $JS_IMPORTS"
        FAILED_TESTS=$((FAILED_TESTS + 1))
    fi
fi

# Verifica esposizione API globale
if [ -f "$BASE_DIR/js/main.js" ]; then
    TOTAL_TESTS=$((TOTAL_TESTS + 1))
    if grep -q "window.fpPerfSuiteUtils" "$BASE_DIR/js/main.js"; then
        echo -e "${GREEN}✓${NC} API globale fpPerfSuiteUtils esposta"
        PASSED_TESTS=$((PASSED_TESTS + 1))
    else
        echo -e "${RED}✗${NC} API globale fpPerfSuiteUtils non trovata"
        FAILED_TESTS=$((FAILED_TESTS + 1))
    fi
fi

echo ""

# Statistiche Finali
echo "=================================================="
echo "📊 Risultati Verifica"
echo "=================================================="
echo ""
echo "Test Totali:    $TOTAL_TESTS"
echo -e "Test Passati:   ${GREEN}$PASSED_TESTS${NC}"

if [ $FAILED_TESTS -gt 0 ]; then
    echo -e "Test Falliti:   ${RED}$FAILED_TESTS${NC}"
else
    echo -e "Test Falliti:   ${GREEN}$FAILED_TESTS${NC}"
fi

echo ""

# Calcola percentuale successo
if [ $TOTAL_TESTS -gt 0 ]; then
    PERCENTAGE=$((PASSED_TESTS * 100 / TOTAL_TESTS))
    echo -n "Successo: "
    
    if [ $PERCENTAGE -eq 100 ]; then
        echo -e "${GREEN}${PERCENTAGE}%${NC} ✅"
        echo ""
        echo -e "${GREEN}┌────────────────────────────────────────┐${NC}"
        echo -e "${GREEN}│  ✅ MODULARIZZAZIONE COMPLETATA! ✅   │${NC}"
        echo -e "${GREEN}└────────────────────────────────────────┘${NC}"
    elif [ $PERCENTAGE -ge 90 ]; then
        echo -e "${YELLOW}${PERCENTAGE}%${NC} ⚠️"
        echo ""
        echo -e "${YELLOW}Quasi completato, verificare errori sopra${NC}"
    else
        echo -e "${RED}${PERCENTAGE}%${NC} ❌"
        echo ""
        echo -e "${RED}Modularizzazione incompleta, verificare errori${NC}"
    fi
fi

echo ""

# Exit code
if [ $FAILED_TESTS -gt 0 ]; then
    exit 1
else
    exit 0
fi