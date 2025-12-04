#!/bin/bash

# ============================================================================
# Script de Versionado Automático para Service Worker
# ============================================================================
# Este script incrementa automáticamente la versión del Service Worker
# Útil para desarrollo local y despliegues en producción
#
# Uso:
#   ./scripts/bump-version.sh          # Incrementa versión patch (1.0.5 → 1.0.6)
#   ./scripts/bump-version.sh minor    # Incrementa versión minor (1.0.5 → 1.1.0)
#   ./scripts/bump-version.sh major    # Incrementa versión major (1.0.5 → 2.0.0)
#   ./scripts/bump-version.sh 1.2.3    # Establece versión específica
#
# ============================================================================

set -e  # Salir si hay errores

# Colores para output
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m' # No Color

# Archivo del Service Worker
SW_FILE="sw.js"

# Verificar que el archivo existe
if [ ! -f "$SW_FILE" ]; then
    echo -e "${RED}Error: No se encontró el archivo $SW_FILE${NC}"
    exit 1
fi

# Extraer versión actual
CURRENT_VERSION=$(grep -oP 'const CACHE_NAME = "unimind-v\K[0-9]+\.[0-9]+\.[0-9]+' "$SW_FILE" | head -1)

if [ -z "$CURRENT_VERSION" ]; then
    echo -e "${RED}Error: No se pudo encontrar la versión actual en $SW_FILE${NC}"
    exit 1
fi

echo -e "${YELLOW}Versión actual: $CURRENT_VERSION${NC}"

# Determinar nueva versión
if [ $# -eq 0 ] || [ "$1" == "patch" ]; then
    # Incrementar patch (por defecto)
    IFS='.' read -r -a version_parts <<< "$CURRENT_VERSION"
    MAJOR="${version_parts[0]}"
    MINOR="${version_parts[1]}"
    PATCH="${version_parts[2]}"
    NEW_PATCH=$((PATCH + 1))
    NEW_VERSION="$MAJOR.$MINOR.$NEW_PATCH"
    
elif [ "$1" == "minor" ]; then
    # Incrementar minor
    IFS='.' read -r -a version_parts <<< "$CURRENT_VERSION"
    MAJOR="${version_parts[0]}"
    MINOR="${version_parts[1]}"
    NEW_MINOR=$((MINOR + 1))
    NEW_VERSION="$MAJOR.$NEW_MINOR.0"
    
elif [ "$1" == "major" ]; then
    # Incrementar major
    IFS='.' read -r -a version_parts <<< "$CURRENT_VERSION"
    MAJOR="${version_parts[0]}"
    NEW_MAJOR=$((MAJOR + 1))
    NEW_VERSION="$NEW_MAJOR.0.0"
    
elif [[ "$1" =~ ^[0-9]+\.[0-9]+\.[0-9]+$ ]]; then
    # Versión específica proporcionada
    NEW_VERSION="$1"
    
else
    echo -e "${RED}Error: Argumento inválido '$1'${NC}"
    echo "Uso: $0 [patch|minor|major|X.Y.Z]"
    exit 1
fi

echo -e "${GREEN}Nueva versión: $NEW_VERSION${NC}"

# Crear backup
BACKUP_FILE="${SW_FILE}.backup"
cp "$SW_FILE" "$BACKUP_FILE"
echo -e "${YELLOW}Backup creado: $BACKUP_FILE${NC}"

# Actualizar versión en sw.js
# Reemplazar CACHE_NAME
sed -i.tmp "s/const CACHE_NAME = \"unimind-v[0-9]\+\.[0-9]\+\.[0-9]\+\"/const CACHE_NAME = \"unimind-v${NEW_VERSION}\"/" "$SW_FILE"

# Reemplazar RUNTIME_CACHE
sed -i.tmp "s/const RUNTIME_CACHE = \"unimind-runtime-v[0-9]\+\.[0-9]\+\.[0-9]\+\"/const RUNTIME_CACHE = \"unimind-runtime-v${NEW_VERSION}\"/" "$SW_FILE"

# Eliminar archivos temporales de sed
rm -f "${SW_FILE}.tmp"

# Verificar que el cambio se aplicó
NEW_VERSION_CHECK=$(grep -oP 'const CACHE_NAME = "unimind-v\K[0-9]+\.[0-9]+\.[0-9]+' "$SW_FILE" | head -1)

if [ "$NEW_VERSION_CHECK" == "$NEW_VERSION" ]; then
    echo -e "${GREEN}✓ Versión actualizada exitosamente en $SW_FILE${NC}"
    echo ""
    echo -e "${YELLOW}Cambios aplicados:${NC}"
    echo "  CACHE_NAME: unimind-v${NEW_VERSION}"
    echo "  RUNTIME_CACHE: unimind-runtime-v${NEW_VERSION}"
    echo ""
    echo -e "${YELLOW}Siguiente paso:${NC}"
    echo "  1. Revisar los cambios: git diff $SW_FILE"
    echo "  2. Probar en el navegador"
    echo "  3. Hacer commit: git add $SW_FILE && git commit -m 'chore: bump SW version to v${NEW_VERSION}'"
    echo ""
    echo -e "${YELLOW}Para revertir:${NC}"
    echo "  mv $BACKUP_FILE $SW_FILE"
else
    echo -e "${RED}Error: La versión no se actualizó correctamente${NC}"
    echo "Restaurando backup..."
    mv "$BACKUP_FILE" "$SW_FILE"
    exit 1
fi
