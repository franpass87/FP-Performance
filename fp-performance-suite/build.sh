#!/usr/bin/env bash
set -euo pipefail

PLUGIN_SLUG="fp-performance-suite"
PROJECT_ROOT="$(cd "$(dirname "$0")" && pwd)"
BUILD_DIR="${PROJECT_ROOT}/build"
STAGING_DIR="${BUILD_DIR}/${PLUGIN_SLUG}"

SET_VERSION=""
BUMP_TYPE="patch"
ZIP_NAME=""

while [ "$#" -gt 0 ]; do
    case "$1" in
        --set-version=*)
            SET_VERSION="${1#*=}"
            ;;
        --set-version)
            shift
            if [ "$#" -eq 0 ]; then
                echo "Missing value for --set-version" >&2
                exit 1
            fi
            SET_VERSION="$1"
            ;;
        --bump=*)
            BUMP_TYPE="${1#*=}"
            ;;
        --bump)
            shift
            if [ "$#" -eq 0 ]; then
                echo "Missing value for --bump" >&2
                exit 1
            fi
            BUMP_TYPE="$1"
            ;;
        --zip-name=*)
            ZIP_NAME="${1#*=}"
            ;;
        --zip-name)
            shift
            if [ "$#" -eq 0 ]; then
                echo "Missing value for --zip-name" >&2
                exit 1
            fi
            ZIP_NAME="$1"
            ;;
        --help|-h)
            cat <<USAGE
Usage: bash build.sh [--set-version=X.Y.Z] [--bump=patch|minor|major] [--zip-name=name]
If --set-version is provided it overrides --bump.
USAGE
            exit 0
            ;;
        *)
            echo "Unknown option: $1" >&2
            exit 1
            ;;
    esac
    shift
done

case "$BUMP_TYPE" in
    patch|minor|major)
        ;;
    *)
        echo "Invalid bump type: ${BUMP_TYPE}. Allowed: patch, minor, major." >&2
        exit 1
        ;;
esac

cd "$PROJECT_ROOT"

if [ -n "$SET_VERSION" ]; then
    NEW_VERSION="$(php tools/bump-version.php --set="$SET_VERSION")"
elif [ -n "$BUMP_TYPE" ]; then
    NEW_VERSION="$(php tools/bump-version.php --bump="$BUMP_TYPE")"
else
    NEW_VERSION="$(php tools/bump-version.php --bump=patch)"
fi

rm -rf vendor
composer install --no-dev --prefer-dist --no-interaction --optimize-autoloader
composer dump-autoload -o --classmap-authoritative

rm -rf "$STAGING_DIR"
mkdir -p "$STAGING_DIR"

RSYNC_EXCLUDES=(
    "--exclude=.git"
    "--exclude=.github"
    "--exclude=tests"
    "--exclude=docs"
    "--exclude=node_modules"
    "--exclude=*.md"
    "--exclude=.idea"
    "--exclude=.vscode"
    "--exclude=build"
    "--exclude=.gitattributes"
    "--exclude=.gitignore"
    "--exclude=phpunit.xml.dist"
    "--exclude=phpcs.xml.dist"
    "--exclude=phpstan.neon.dist"
    "--exclude=composer.lock"
    "--exclude=.phpunit.result.cache"
    "--exclude=wp-content"
    "--exclude=wp-admin"
    "--exclude=wp-includes"
    "--exclude=📋*.txt"
    "--exclude=📚*.md"
    "--exclude=🎉*.md"
    "--exclude=✅*.md"
    "--exclude=examples"
    "--exclude=tools"
    "--exclude=bin"
    "--exclude=build.sh"
    "--exclude=verify-zip-structure.sh"
    "--exclude=README-BUILD.md"
    "--exclude=README-ZIP-WORDPRESS.md"
    "--exclude=.DS_Store"
    "--exclude=Thumbs.db"
)

rsync -a --delete "${RSYNC_EXCLUDES[@]}" ./ "$STAGING_DIR"/

TIMESTAMP="$(date +%Y%m%d%H%M)"
ZIP_BASENAME="$PLUGIN_SLUG-$TIMESTAMP"
if [ -n "$ZIP_NAME" ]; then
    ZIP_BASENAME="$ZIP_NAME"
fi

mkdir -p "$BUILD_DIR"

(
    cd "$BUILD_DIR"
    zip -r "${ZIP_BASENAME}.zip" "$PLUGIN_SLUG" >/dev/null
)

ZIP_PATH="${BUILD_DIR}/${ZIP_BASENAME}.zip"

FINAL_VERSION="$(php -r '$contents = file_get_contents("'"${PROJECT_ROOT}/fp-performance-suite.php"'"); if ($contents === false) { exit(1); } if (preg_match("/^\\s*\\*\\s*Version:\\s*(.+)$/mi", $contents, $m)) { echo trim($m[1]); } else { exit(1); }')"

if [ -z "$FINAL_VERSION" ]; then
    echo "Unable to determine final version." >&2
    exit 1
fi

echo "Version: ${FINAL_VERSION}"
echo "Zip: ${ZIP_PATH}"
