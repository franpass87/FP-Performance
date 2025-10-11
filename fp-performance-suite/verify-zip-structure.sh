#!/usr/bin/env bash
# Script per verificare che la struttura dello zip sia corretta per WordPress

set -euo pipefail

PLUGIN_SLUG="fp-performance-suite"
BUILD_DIR="./build"
STAGING_DIR="${BUILD_DIR}/${PLUGIN_SLUG}"

echo "=== Verifica della struttura ZIP per WordPress ==="
echo ""

# Verifica che esistano i file essenziali
echo "1. Verifica dei file essenziali..."
REQUIRED_FILES=(
    "${PLUGIN_SLUG}.php"
    "readme.txt"
    "LICENSE"
    "uninstall.php"
)

for file in "${REQUIRED_FILES[@]}"; do
    if [ -f "$file" ]; then
        echo "   ✓ $file trovato"
    else
        echo "   ✗ $file NON trovato"
        exit 1
    fi
done

echo ""
echo "2. Verifica delle directory essenziali..."
REQUIRED_DIRS=(
    "src"
    "assets"
    "languages"
    "views"
)

for dir in "${REQUIRED_DIRS[@]}"; do
    if [ -d "$dir" ]; then
        echo "   ✓ $dir/ trovata"
    else
        echo "   ✗ $dir/ NON trovata"
        exit 1
    fi
done

echo ""
echo "3. Verifica che i file non necessari siano esclusi..."
EXCLUDED_PATTERNS=(
    "tests"
    "docs"
    "examples"
    "tools"
    "bin"
    ".git"
    ".github"
    "phpunit.xml.dist"
    "phpcs.xml.dist"
    "phpstan.neon.dist"
    "build.sh"
)

for pattern in "${EXCLUDED_PATTERNS[@]}"; do
    if [ -e "$pattern" ]; then
        echo "   ⚠ $pattern presente (sarà escluso nel build)"
    else
        echo "   ✓ $pattern non presente"
    fi
done

echo ""
echo "4. Verifica header del plugin principale..."
if grep -q "Plugin Name:" "${PLUGIN_SLUG}.php"; then
    echo "   ✓ Header 'Plugin Name' trovato"
else
    echo "   ✗ Header 'Plugin Name' NON trovato"
    exit 1
fi

if grep -q "Version:" "${PLUGIN_SLUG}.php"; then
    VERSION=$(grep "Version:" "${PLUGIN_SLUG}.php" | head -1 | sed 's/.*Version:\s*//' | sed 's/\s*$//')
    echo "   ✓ Version trovata: $VERSION"
else
    echo "   ✗ Header 'Version' NON trovato"
    exit 1
fi

echo ""
echo "5. Verifica vendor directory (se presente)..."
if [ -d "vendor" ]; then
    echo "   ✓ vendor/ presente (dipendenze Composer installate)"
    if [ -f "vendor/autoload.php" ]; then
        echo "   ✓ vendor/autoload.php presente"
    else
        echo "   ✗ vendor/autoload.php NON trovato"
        exit 1
    fi
else
    echo "   ⚠ vendor/ non presente (eseguire 'composer install --no-dev' prima del deploy)"
fi

echo ""
echo "=== STRUTTURA ZIP CORRETTA PER WORDPRESS ==="
echo ""
echo "Lo zip deve avere questa struttura:"
echo "${PLUGIN_SLUG}.zip"
echo "└── ${PLUGIN_SLUG}/"
echo "    ├── ${PLUGIN_SLUG}.php"
echo "    ├── readme.txt"
echo "    ├── LICENSE"
echo "    ├── uninstall.php"
echo "    ├── src/"
echo "    ├── assets/"
echo "    ├── languages/"
echo "    ├── views/"
echo "    └── vendor/"
echo ""
echo "✓ Tutti i controlli superati!"
