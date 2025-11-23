-- Migration Split: 2025-11-22
-- Solo añade la columna client_uuid a la tabla Aplicaciones (sin crear índice)
ALTER TABLE `Aplicaciones`
  ADD COLUMN `client_uuid` VARCHAR(36) NULL DEFAULT NULL AFTER `id_usuario`;

-- Nota: Ejecuta este archivo primero, luego ejecuta el backfill, y finalmente crea el índice con
-- database/migrations/20251122_add_client_uuid_index.sql
-- Ejemplo:
-- mysql -u root -p db_tests_estres_ansiedad < database/migrations/20251122_add_client_uuid_split.sql
