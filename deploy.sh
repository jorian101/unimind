#!/bin/bash

# Cambiar a la rama develop
git checkout develop
git pull origin develop  # Actualiza si es necesario

# Iniciar XAMPP
cd /opt/lampp
sudo ./lampp start
sleep 5  # Espera a que inicie

# Iniciar ngrok en segundo plano
ngrok http 80 &
sleep 3  # Espera a que ngrok genere la URL

# Mostrar la URL de ngrok
echo "Proyecto desplegado en la rama develop. URL de ngrok:"

# Try to get URL via API (requires jq)
if command -v jq &> /dev/null; then
    URL=$(curl -s http://localhost:4040/api/tunnels | jq -r '.tunnels[0].public_url' 2>/dev/null)
    if [ -n "$URL" ]; then
        echo "Project deployed on develop branch. Ngrok URL: $URL"
        # Optional: Open in browser (uncomment if desired)
        # xdg-open "$URL" &
    else
        echo "Failed to get URL. Check http://localhost:4040 manually."
    fi
else
    echo "jq not installed. Install it and rerun, or check http://localhost:4040 for the URL."
fi

# To stop: pkill ngrok && sudo ./lampp stop
