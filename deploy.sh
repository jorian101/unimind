#!/bin/bash

# Usage: ./deploy.sh [branch] [provider]
# provider: cloudflare (default) | ngrok

usage() {
    echo "Usage: $0 [branch] [provider]"
    echo "  branch: Git branch to deploy (default: develop)"
    echo "  provider: cloudflare | ngrok (default: cloudflare)"
    exit 1
}

if [[ "$1" == "-h" || "$1" == "--help" || "$2" == "-h" || "$2" == "--help" ]]; then
    usage
fi

BRANCH="${1:-develop}"
PROVIDER="${2:-cloudflare}"

echo "Deploying branch: $BRANCH (provider: $PROVIDER)"
git fetch origin
git checkout "$BRANCH" || { echo "git checkout failed"; exit 1; }
git pull origin "$BRANCH" || echo "git pull failed or nothing to pull"

# Iniciar XAMPP
cd /opt/lampp || { echo "/opt/lampp not found"; exit 1; }
sudo ./lampp start
sleep 5  # Espera a que inicie

case "$PROVIDER" in
    cloudflare|cf)
        echo "Starting Cloudflare Tunnel..."
        cloudflared tunnel --url http://localhost:80 > /tmp/cloudflared.log 2>&1 &
        TUNNEL_PID=$!
        sleep 5  # Espera a que el túnel genere la URL

        echo "Proyecto desplegado en la rama $BRANCH. URL de Cloudflare Tunnel:"
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
            echo "⚠️  No se encontró el archivo de log: /tmp/cloudflared.log"
        fi

        echo ""
        echo "Tunnel PID: $TUNNEL_PID"
        echo "Para detener: kill $TUNNEL_PID && sudo /opt/lampp/lampp stop"
        ;;

    ngrok)
        if ! command -v ngrok &> /dev/null; then
            echo "ngrok no está instalado o no está en PATH. Instálalo e intenta de nuevo."
            exit 1
        fi
        echo "Starting ngrok..."
        ngrok http 80 > /tmp/ngrok.log 2>&1 &
        NGROK_PID=$!
        sleep 3  # Espera a que ngrok genere la URL

        echo "Proyecto desplegado en la rama $BRANCH. URL de ngrok:"
        URL=""
        if command -v jq &> /dev/null; then
            URL=$(curl -s http://localhost:4040/api/tunnels | jq -r '.tunnels[0].public_url' 2>/dev/null)
        fi
        if [ -z "$URL" ]; then
            URL=$(grep -oE 'https?://[a-z0-9.-]+\.ngrok\.io' /tmp/ngrok.log | head -1)
        fi
        if [ -n "$URL" ]; then
            echo "====================================="
            echo "El proyecto está disponible en: $URL"
            echo ""
            # Optional: Abrir en navegador (descomenta si lo deseas)
            # xdg-open "$URL/unimind" &
        else
            echo "⚠️  No se pudo obtener la URL automáticamente."
            echo "Revisa el dashboard local: http://localhost:4040 o el log: /tmp/ngrok.log"
        fi

        echo ""
        echo "Ngrok PID: $NGROK_PID"
        echo "Para detener: kill $NGROK_PID && sudo /opt/lampp/lampp stop  (o usar: pkill ngrok)"
        ;;

    *)
        echo "Proveedor desconocido: $PROVIDER"
        usage
        ;;
esac

exit 0
