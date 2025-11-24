-- Migration: 2025-11-24
-- Eliminar índice único sobre Tests.nombre si existe (para permitir nombres duplicados)
-- IMPORTANTE: Haz backup antes de ejecutar:
-- mysqldump -u root -p db_tests_estres_ansiedad > backup_aplicaciones_before_remove_index.sql

-- Esta migración detecta el nombre del índice en information_schema y lo elimina si existe.
SET @idx := (
  SELECT INDEX_NAME
  FROM information_schema.STATISTICS
  WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = 'Tests'
    AND COLUMN_NAME = 'nombre'
  LIMIT 1
);

SET @stmt := IF(@idx IS NULL,
  'SELECT 0;',
  CONCAT('ALTER TABLE `Tests` DROP INDEX `', @idx, '`;')
);

PREPARE s FROM @stmt;
EXECUTE s;
DEALLOCATE PREPARE s;

-- Nota: Ejecuta con:
-- mysql -u root -p db_tests_estres_ansiedad < database/migrations/20251124_remove_unique_tests_nombre.sql
