#!/bin/bash

echo "==================================="
echo "Test del Sistema de Recomendaciones"
echo "==================================="
echo ""

# Colors
GREEN='\033[0;32m'
RED='\033[0;31m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Test 1: Verificar tabla en DB
echo -e "${YELLOW}Test 1: Verificando tabla Recomendaciones...${NC}"
RESULT=$(/opt/lampp/bin/mysql -u root -e "USE db_tests_estres_ansiedad; SELECT COUNT(*) FROM Recomendaciones;" -s -N)
if [ "$RESULT" -gt 0 ]; then
    echo -e "${GREEN}✓ Tabla existe con $RESULT recomendaciones${NC}"
else
    echo -e "${RED}✗ No se encontraron recomendaciones${NC}"
fi
echo ""

# Test 2: Verificar archivos creados
echo -e "${YELLOW}Test 2: Verificando archivos del sistema...${NC}"
FILES=(
    "/opt/lampp/htdocs/unimind/models/administrador/RecomendacionesModel.php"
    "/opt/lampp/htdocs/unimind/controllers/RecomendacionesController.php"
    "/opt/lampp/htdocs/unimind/api/recomendaciones.php"
    "/opt/lampp/htdocs/unimind/views/administrador/recomendaciones.php"
    "/opt/lampp/htdocs/unimind/views/administrador/recomendaciones.css"
    "/opt/lampp/htdocs/unimind/views/estudiante/recomendaciones.php"
)

ALL_OK=true
for FILE in "${FILES[@]}"; do
    if [ -f "$FILE" ]; then
        echo -e "${GREEN}✓ $(basename $FILE)${NC}"
    else
        echo -e "${RED}✗ $(basename $FILE) no encontrado${NC}"
        ALL_OK=false
    fi
done
echo ""

# Test 3: Verificar sintaxis PHP
echo -e "${YELLOW}Test 3: Verificando sintaxis PHP...${NC}"
for FILE in "${FILES[@]}"; do
    if [[ $FILE == *.php ]]; then
        /opt/lampp/bin/php -l "$FILE" > /dev/null 2>&1
        if [ $? -eq 0 ]; then
            echo -e "${GREEN}✓ $(basename $FILE) - Sintaxis correcta${NC}"
        else
            echo -e "${RED}✗ $(basename $FILE) - Error de sintaxis${NC}"
        fi
    fi
done
echo ""

# Test 4: Estadísticas de recomendaciones
echo -e "${YELLOW}Test 4: Estadísticas de recomendaciones...${NC}"
echo "Por categoría:"
/opt/lampp/bin/mysql -u root -e "USE db_tests_estres_ansiedad; SELECT categoria, COUNT(*) as cantidad FROM Recomendaciones WHERE activa = 1 GROUP BY categoria;" -t

echo ""
echo "Por prioridad:"
/opt/lampp/bin/mysql -u root -e "USE db_tests_estres_ansiedad; SELECT prioridad, COUNT(*) as cantidad FROM Recomendaciones WHERE activa = 1 GROUP BY prioridad ORDER BY prioridad DESC;" -t

echo ""
echo "Por tipo de test:"
/opt/lampp/bin/mysql -u root -e "USE db_tests_estres_ansiedad; SELECT tipo_test, COUNT(*) as cantidad FROM Recomendaciones WHERE activa = 1 GROUP BY tipo_test;" -t

echo ""
echo -e "${GREEN}==================================="
echo "Tests completados"
echo -e "===================================${NC}"
