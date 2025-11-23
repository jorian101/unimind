-- Migration: 2025-11-22
-- Crear índice único para Aplicaciones.client_uuid (ejecutar DESPUÉS del backfill)
CREATE UNIQUE INDEX `idx_aplicaciones_client_uuid` ON `Aplicaciones` (`client_uuid`);

-- Nota: Ejecuta este archivo SOLO después de:
-- 1) aplicar el ALTER TABLE para añadir la columna (20251122_add_client_uuid_split.sql)
-- 2) ejecutar el backfill/cleanup (20251122_backfill_client_uuid.sql)

-- Ejemplo:
-- mysql -u root -p db_tests_estres_ansiedad < database/migrations/20251122_add_client_uuid_index.sql
