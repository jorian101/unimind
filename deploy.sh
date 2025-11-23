#!/bin/bash

# Allow selecting a branch to deploy (default: develop)
BRANCH="${1:-develop}"
echo "Deploying branch: $BRANCH"
git fetch origin
git checkout "$BRANCH"
git pull origin "$BRANCH"  # Actualiza si es necesario

# Iniciar XAMPP
cd /opt/lampp
sudo ./lampp start
sleep 5  # Espera a que inicie

# Iniciar Cloudflare Tunnel en segundo plano
echo "Starting Cloudflare Tunnel..."
cloudflared tunnel --url http://localhost:80 > /tmp/cloudflared.log 2>&1 &
TUNNEL_PID=$!
sleep 5  # Espera a que el túnel genere la URL

# Obtener la URL del túnel desde los logs
echo "Proyecto desplegado en la rama $BRANCH. URL de Cloudflare Tunnel:"

# Intentar obtener la URL del log
if [ -f /tmp/cloudflared.log ]; then
    URL=$(grep -oP 'https://[a-zA-Z0-9-]+\.trycloudflare\.com' /tmp/cloudflared.log | head -1)
    if [ -n "$URL" ]; then
        echo "====================================="
        echo "El proyecto está disponible en: $URL"
        echo ""
        # Optional: Abrir en navegador (descomenta si lo deseas)
        # xdg-open "$URL/unimind" &
    else
        echo "⚠️  No se pudo obtener la URL automáticamente."
        echo "Verifica el log manualmente: cat /tmp/cloudflared.log"
    fi
else
    echo "⚠️  No se encontró el archivo de log."
fi

echo ""
echo "Tunnel PID: $TUNNEL_PID"
echo "Para detener: kill $TUNNEL_PID && sudo /opt/lampp/lampp stop"
