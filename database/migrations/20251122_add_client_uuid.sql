-- Migration: 2025-11-22
-- Añade columna client_uuid a la tabla Aplicaciones y crea índice único
ALTER TABLE `Aplicaciones`
  ADD COLUMN `client_uuid` VARCHAR(36) NULL DEFAULT NULL AFTER `id_usuario`;

-- Crear índice único para idempotencia (si existen duplicados previos, la creación fallará hasta que se limpien)
CREATE UNIQUE INDEX `idx_aplicaciones_client_uuid` ON `Aplicaciones` (`client_uuid`);

-- Nota: Ejecuta este archivo manualmente con:
-- mysql -u root -p db_tests_estres_ansiedad < database/migrations/20251122_add_client_uuid.sql
