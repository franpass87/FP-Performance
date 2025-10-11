#!/usr/bin/env bash
set -euo pipefail

# Script per aggiornare il file fp-performance-suite.zip con le ultime modifiche
# Uso: bash update-zip.sh

PLUGIN_SLUG="fp-performance-suite"
BUILD_DIR="build"
ZIP_FILE="${PLUGIN_SLUG}.zip"

echo "🔧 Aggiornamento ${ZIP_FILE}..."

# Pulisci e crea directory build
rm -rf "${BUILD_DIR}/${PLUGIN_SLUG}"
mkdir -p "${BUILD_DIR}/${PLUGIN_SLUG}"

echo "📦 Copia file del plugin..."

# Usa rsync per copiare i file (più affidabile)
cd "${PLUGIN_SLUG}"

# Lista esclusioni
EXCLUDES=(
  --exclude=.git
  --exclude=.github
  --exclude=tests
  --exclude=docs
  --exclude=node_modules
  --exclude='*.md'
  --exclude=.idea
  --exclude=.vscode
  --exclude=build
  --exclude=.gitattributes
  --exclude=.gitignore
  --exclude=phpunit.xml.dist
  --exclude=phpcs.xml.dist
  --exclude=phpstan.neon.dist
  --exclude=composer.lock
  --exclude=composer.json
  --exclude=.phpunit.result.cache
  --exclude=wp-content
  --exclude=wp-admin
  --exclude=wp-includes
  --exclude='📋*.txt'
  --exclude='📚*.md'
  --exclude='🎉*.md'
  --exclude='✅*.md'
  --exclude=examples
  --exclude=tools
  --exclude=bin
  --exclude=build.sh
  --exclude=verify-zip-structure.sh
  --exclude=README-BUILD.md
  --exclude=README-ZIP-WORDPRESS.md
  --exclude=.DS_Store
  --exclude=Thumbs.db
  --exclude=vendor
)

# Copia con rsync (se disponibile) o fallback a tar
if command -v rsync &> /dev/null; then
  rsync -a --delete "${EXCLUDES[@]}" ./ "../${BUILD_DIR}/${PLUGIN_SLUG}/"
else
  # Fallback usando tar
  tar --exclude='.git*' --exclude='tests' --exclude='docs' --exclude='*.md' \
      --exclude='build' --exclude='examples' --exclude='tools' --exclude='bin' \
      --exclude='*.xml.dist' --exclude='*.neon.dist' --exclude='composer.*' \
      --exclude='node_modules' --exclude='vendor' --exclude='*.sh' \
      -cf - . | (cd "../${BUILD_DIR}/${PLUGIN_SLUG}" && tar -xf -)
fi

cd ..

echo "🗜️  Creazione ZIP..."

# Crea ZIP
cd "${BUILD_DIR}"
zip -r "${ZIP_FILE}" "${PLUGIN_SLUG}" -q

# Sposta ZIP nella root del progetto
mv "${ZIP_FILE}" "../${ZIP_FILE}"

cd ..

# Verifica contenuto
PAGESPEED_COUNT=$(unzip -p "${ZIP_FILE}" "${PLUGIN_SLUG}/src/Admin/Pages/Assets.php" 2>/dev/null | grep -c "PageSpeed" || echo "0")
FILE_COUNT=$(unzip -l "${ZIP_FILE}" | tail -1 | awk '{print $2}')

echo "✅ ZIP aggiornato con successo!"
echo "📊 Statistiche:"
echo "   - File inclusi: ${FILE_COUNT}"
echo "   - PageSpeed features: ${PAGESPEED_COUNT}"
echo "   - Dimensione: $(ls -lh "${ZIP_FILE}" | awk '{print $5}')"
echo ""
echo "💡 Il file ${ZIP_FILE} è pronto per essere committato!"
