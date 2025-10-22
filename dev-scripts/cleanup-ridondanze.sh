#!/bin/bash

# Script per eliminare pagine ridondanti dal plugin FP Performance Suite
# Data: 21 Ottobre 2025
# Autore: AI Assistant

echo "🧹 FP Performance Suite - Cleanup Ridondanze"
echo "=============================================="
echo ""

# Colori per output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Funzione per conferma
confirm() {
    read -p "$1 (y/n) " -n 1 -r
    echo
    if [[ $REPLY =~ ^[Yy]$ ]]; then
        return 0
    else
        return 1
    fi
}

echo -e "${YELLOW}FASE 1: Eliminazione File Ridondanti al 100%${NC}"
echo "================================================"
echo ""
echo "I seguenti file sono COMPLETAMENTE ridondanti (funzionalità migrate):"
echo ""
echo "  1. Compression.php        → Funzionalità in InfrastructureCdn.php"
echo "  2. Tools.php              → Funzionalità in Settings.php"
echo "  3. ScheduledReports.php   → Funzionalità in MonitoringReports.php"
echo "  4. AIConfigAdvanced.php   → Duplicato di AIConfig.php"
echo "  5. _Presets_OLD.php       → File backup obsoleto"
echo ""

if confirm "Vuoi procedere con l'eliminazione dei 5 file ridondanti?"; then
    echo ""
    echo "Eliminazione in corso..."
    
    FILES=(
        "src/Admin/Pages/Compression.php"
        "src/Admin/Pages/Tools.php"
        "src/Admin/Pages/ScheduledReports.php"
        "src/Admin/Pages/AIConfigAdvanced.php"
        "src/Admin/Pages/_Presets_OLD.php"
    )
    
    for file in "${FILES[@]}"; do
        if [ -f "$file" ]; then
            rm "$file"
            echo -e "  ${GREEN}✓${NC} Eliminato: $file"
        else
            echo -e "  ${YELLOW}⚠${NC} File non trovato: $file"
        fi
    done
    
    echo ""
    echo -e "${GREEN}✓ Eliminazione completata!${NC}"
    echo ""
else
    echo ""
    echo -e "${YELLOW}⚠ Eliminazione annullata${NC}"
    echo ""
fi

echo ""
echo -e "${YELLOW}FASE 2: File da Valutare${NC}"
echo "=========================="
echo ""
echo "I seguenti file richiedono una decisione:"
echo ""
echo "  • UnusedCSS.php                  → Funzionalità UNICA (130 KiB saving)"
echo "  • LighthouseFontOptimization.php → Funzionalità parzialmente coperta"
echo "  • Presets.php                    → UI per preset (service già esiste)"
echo ""
echo "Consulta REPORT_RIDONDANZE_MENU_AGGIORNATO.md per dettagli"
echo ""

if confirm "Vuoi eliminare anche Presets.php (consigliato)?"; then
    if [ -f "src/Admin/Pages/Presets.php" ]; then
        rm "src/Admin/Pages/Presets.php"
        echo -e "  ${GREEN}✓${NC} Eliminato: Presets.php"
    fi
fi

echo ""
echo -e "${YELLOW}FASE 3: Git Commit${NC}"
echo "==================="
echo ""

if confirm "Vuoi creare un commit git con le modifiche?"; then
    git add -A
    git commit -m "chore: remove redundant admin pages

- Removed Compression.php (migrated to InfrastructureCdn.php)
- Removed Tools.php (migrated to Settings.php)
- Removed ScheduledReports.php (migrated to MonitoringReports.php)
- Removed AIConfigAdvanced.php (duplicate of AIConfig.php)
- Removed _Presets_OLD.php (obsolete backup)

All functionality preserved in active pages.
See REPORT_RIDONDANZE_MENU_AGGIORNATO.md for details."
    
    echo ""
    echo -e "${GREEN}✓ Commit creato con successo!${NC}"
else
    echo ""
    echo -e "${YELLOW}⚠ Commit annullato. Ricordati di committare manualmente.${NC}"
fi

echo ""
echo "=============================================="
echo -e "${GREEN}🎉 Cleanup completato!${NC}"
echo ""
echo "Prossimi passi:"
echo "  1. Rivedi REPORT_RIDONDANZE_MENU_AGGIORNATO.md"
echo "  2. Decidi su UnusedCSS.php (integra o elimina)"
echo "  3. Decidi su LighthouseFontOptimization.php"
echo "  4. Testa il plugin per verificare tutto funzioni"
echo ""

