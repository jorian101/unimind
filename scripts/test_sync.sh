#!/usr/bin/env bash
# Script de pruebas para el endpoint de sync (no lo ejecutes con sudo)
BASE_URL="http://localhost/unimind/controllers/SyncController.php"

# 1) Test simple: enviar un item
echo "== Test 1: enviar single item =="
curl -i -X POST \
  -H "Content-Type: application/json" \
  --cookie "PHPSESSID=YOUR_SESSION_ID" \
  -d '{"items":[{"client_uuid":"test-uuid-1","id_test":1,"respuestas":{"1":3,"2":1}}]}' \
  ${BASE_URL}

echo "\n== Test 2: enviar duplicate (mismo client_uuid) =="
curl -i -X POST \
  -H "Content-Type: application/json" \
  --cookie "PHPSESSID=YOUR_SESSION_ID" \
  -d '{"items":[{"client_uuid":"test-uuid-1","id_test":1,"respuestas":{"1":3,"2":1}}]}' \
  ${BASE_URL}

echo "\n== Test 3: payload demasiado grande =="
# Build large payload ~600KB
BIG=$(python3 - <<'PY'
import json
items=[{"client_uuid":"big-uuid-%d"%i,"id_test":1,"respuestas":{"1":1}} for i in range(0,5000)]
print(json.dumps({"items":items}))
PY
)
curl -i -X POST -H "Content-Type: application/json" --data-binary "${BIG}" ${BASE_URL}

echo "\n== Tests finalizados =="

# Nota: reemplaza --cookie "PHPSESSID=..." por la cookie real de tu sesión autenticada.
