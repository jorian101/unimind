-- Migration: 2025-11-22
-- Crea la tabla sync_logs para rastrear intentos de sincronización
CREATE TABLE IF NOT EXISTS `sync_logs` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `client_uuid` VARCHAR(36) NULL,
  `request_payload` LONGTEXT NULL,
  `response_payload` LONGTEXT NULL,
  `status` VARCHAR(32) NULL,
  `duration_ms` INT NULL,
  `error_message` TEXT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX (`client_uuid`)
);

-- Nota: Ejecuta este archivo manualmente con:
-- mysql -u root -p db_tests_estres_ansiedad < database/migrations/20251122_create_sync_logs.sql
