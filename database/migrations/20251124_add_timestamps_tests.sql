-- Migration: 2025-11-24
-- Añadir columnas created_at y updated_at a la tabla Tests si no existen
-- IMPORTANTE: Haz backup antes de ejecutar:
-- mysqldump -u root -p db_tests_estres_ansiedad > backup_before_add_timestamps.sql

-- Añadir created_at si falta
SET @has_created := (
  SELECT COUNT(*)
  FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = 'Tests'
    AND COLUMN_NAME = 'created_at'
);
SET @stmt := IF(@has_created = 0,
  'ALTER TABLE `Tests` ADD COLUMN `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP',
  'SELECT 0'
);
PREPARE s FROM @stmt;
EXECUTE s;
DEALLOCATE PREPARE s;

-- Añadir updated_at si falta
SET @has_updated := (
  SELECT COUNT(*)
  FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = 'Tests'
    AND COLUMN_NAME = 'updated_at'
);
SET @stmt2 := IF(@has_updated = 0,
  'ALTER TABLE `Tests` ADD COLUMN `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP',
  'SELECT 0'
);
PREPARE s2 FROM @stmt2;
EXECUTE s2;
DEALLOCATE PREPARE s2;

-- Nota: Ejecuta con:
-- mysql -u root -p db_tests_estres_ansiedad < database/migrations/20251124_add_timestamps_tests.sql
