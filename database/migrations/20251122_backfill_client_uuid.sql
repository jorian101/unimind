-- Backfill / Cleanup for client_uuid column
-- Date: 2025-11-22
-- Objetivo: asegurarse que no existan NULLs ni valores duplicados en `Aplicaciones.client_uuid`
-- antes de crear un índice UNIQUE.

-- 1) (Opcional) Hacer backup de la tabla Aplicaciones
-- CREATE TABLE Aplicaciones_backup AS SELECT * FROM Aplicaciones;

-- 2) Rellenar client_uuid NULL con UUID() (valores únicos generados)
UPDATE `Aplicaciones`
SET client_uuid = UUID()
WHERE client_uuid IS NULL;

-- 3) Resolver duplicados: para filas que compartan el mismo client_uuid,
-- mantener la fila con menor id_aplicacion y asignar nuevos UUID() a las demás.
-- Esta consulta funciona en MySQL sin funciones de ventana.
UPDATE `Aplicaciones` AS a
SET a.client_uuid = UUID()
WHERE a.client_uuid IS NOT NULL
  AND EXISTS (
    SELECT 1 FROM `Aplicaciones` AS b
    WHERE b.client_uuid = a.client_uuid AND b.id_aplicacion < a.id_aplicacion
  );

-- 4) Comprobar si aún hay duplicados (debería devolver 0 filas)
SELECT client_uuid, COUNT(*) AS cnt
FROM `Aplicaciones`
GROUP BY client_uuid
HAVING cnt > 1;

-- 5) Si todo está OK, puedes crear el índice único (si no lo has ejecutado aún):
-- CREATE UNIQUE INDEX `idx_aplicaciones_client_uuid` ON `Aplicaciones` (`client_uuid`);

-- Nota: Ejecuta este script antes de ejecutar la migración que crea el índice único.
-- Comando ejemplo:
-- mysql -u root -p db_tests_estres_ansiedad < database/migrations/20251122_backfill_client_uuid.sql
