-- Migration: add 'sugerido_por' and 'origen' to Aplicaciones and create Notificaciones table
-- Fecha: 2025-11-24

-- Use IF NOT EXISTS when supported, otherwise tolerate duplicate column error
ALTER TABLE `Aplicaciones`
  ADD COLUMN IF NOT EXISTS `sugerido_por` INT(11) NULL AFTER `resultado_nivel`,
  ADD COLUMN IF NOT EXISTS `origen` VARCHAR(60) NULL AFTER `sugerido_por`;

-- Tabla para notificaciones internas (in-app)
CREATE TABLE IF NOT EXISTS `Notificaciones` (
  `id_notificacion` INT(11) NOT NULL AUTO_INCREMENT,
  `id_usuario_destino` INT(11) NOT NULL,
  `mensaje` VARCHAR(255) NOT NULL,
  `metadata` JSON DEFAULT NULL,
  `leido` TINYINT(1) NOT NULL DEFAULT 0,
  `creado_en` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP(),
  PRIMARY KEY (`id_notificacion`),
  KEY `id_usuario_destino` (`id_usuario_destino`),
  CONSTRAINT `Notificaciones_ibfk_1` FOREIGN KEY (`id_usuario_destino`) REFERENCES `Usuarios` (`id_usuario`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Nota: ejecutar este SQL con el usuario con permisos de alter y create
