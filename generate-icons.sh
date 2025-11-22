#!/bin/bash

# Script para generar iconos PWA para UniMind
# Requiere ImageMagick instalado

# Colores del tema UniMind
BG_COLOR="#4a90e2"
TEXT_COLOR="#ffffff"

# Verificar si ImageMagick está instalado
if ! command -v convert &> /dev/null; then
    echo "❌ ImageMagick no está instalado."
    echo "Instálalo con: sudo apt install imagemagick"
    exit 1
fi

# Crear directorio si no existe
mkdir -p public/icons

echo "🎨 Generando iconos PWA para UniMind..."

# Tamaños de iconos necesarios
SIZES=(72 96 128 144 152 192 384 512)

# Generar cada tamaño
for SIZE in "${SIZES[@]}"; do
    echo "📦 Generando icono ${SIZE}x${SIZE}..."
    
    convert -size ${SIZE}x${SIZE} xc:"$BG_COLOR" \
        -gravity center \
        -pointsize $((SIZE / 3)) \
        -fill "$TEXT_COLOR" \
        -font "DejaVu-Sans-Bold" \
        -annotate +0+0 "UM" \
        -background "$BG_COLOR" \
        -flatten \
        "public/icons/icon-${SIZE}x${SIZE}.png"
done

# Crear favicon.ico (múltiples tamaños)
echo "🔖 Generando favicon.ico..."
convert public/icons/icon-72x72.png \
        public/icons/icon-96x96.png \
        public/icons/icon-128x128.png \
        public/icons/icon-144x144.png \
        public/favicon.ico

echo "✅ Iconos generados exitosamente en public/icons/"
echo ""
echo "📋 Archivos creados:"
ls -lh public/icons/
echo ""
echo "💡 Nota: Estos son iconos temporales con las iniciales 'UM'"
echo "   Para un logo profesional, reemplaza estos archivos con tu diseño."
