CREATE DATABASE IF NOT EXISTS `db_tests_estres_ansiedad`;
USE `db_tests_estres_ansiedad`;

-- 1. Tablas Principales (Sin dependencias)

CREATE TABLE `Escuelas` (
    `id_escuela` INT NOT NULL AUTO_INCREMENT,
    `nombre_escuela` VARCHAR(150) UNIQUE NOT NULL,
    `telefono` VARCHAR(20),
    PRIMARY KEY (`id_escuela`)
);

CREATE TABLE `Usuarios` (
    `id_usuario` INT NOT NULL AUTO_INCREMENT,
    `nombre` VARCHAR(100) NOT NULL,
    `apellido` VARCHAR(100) NOT NULL,
    `codigo_usuario` VARCHAR(10) NOT NULL UNIQUE,
    `password` VARCHAR(255) NOT NULL,
    `cargo` VARCHAR(30) NOT NULL CHECK (`cargo` IN ('Estudiante', 'Docente', 'Administrador')),
    `fecha_nacimiento` DATE,
    `genero` VARCHAR(10),
    `fecha_registro` DATETIME DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id_usuario`)
);
CREATE TABLE `Opciones_Respuesta` (
    `id_opcion` INT NOT NULL AUTO_INCREMENT,
    `texto_opcion` VARCHAR(100) NOT NULL,
    `valor_puntuacion` INT NOT NULL,
    PRIMARY KEY (`id_opcion`)
);

-- Tabla de Tipos de Escala
CREATE TABLE `Tipos_Escalas` (
    `id_tipo_escala` INT NOT NULL AUTO_INCREMENT,
    `nombre` VARCHAR(100) NOT NULL,
    `descripcion` TEXT,
    PRIMARY KEY (`id_tipo_escala`)
);

-- Tabla intermedia para vincular tipos de escala con opciones
CREATE TABLE `TiposEscala_Opciones` (
    `id_tipo_escala` INT NOT NULL,
    `id_opcion` INT NOT NULL,
    PRIMARY KEY (`id_tipo_escala`, `id_opcion`),
    FOREIGN KEY (`id_tipo_escala`) REFERENCES `Tipos_Escalas`(`id_tipo_escala`) ON DELETE CASCADE,
    FOREIGN KEY (`id_opcion`) REFERENCES `Opciones_Respuesta`(`id_opcion`) ON DELETE CASCADE
);

-- Tabla de Tests (moved below Tipos_Escalas to satisfy foreign keys)
CREATE TABLE `Tests` (
    `id_test` INT NOT NULL AUTO_INCREMENT,
    `nombre` VARCHAR(100) NOT NULL,
    `descripcion` TEXT,
    `num_items` INT NOT NULL,
    `tipo_test` ENUM('estres','ansiedad') NOT NULL DEFAULT 'estres',
    `estado_test` ENUM('activo','inactivo') NOT NULL DEFAULT 'activo',
    `id_tipo_escala` INT NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id_test`),
    FOREIGN KEY (`id_tipo_escala`) REFERENCES `Tipos_Escalas`(`id_tipo_escala`) ON DELETE SET NULL
);

-- 2. Tablas Dependientes (Con Foreign Keys)

CREATE TABLE `Cursos` (
    `id_curso` INT NOT NULL AUTO_INCREMENT,
    `nombre_curso` VARCHAR(150) NOT NULL,
    `id_escuela` INT NOT NULL,
    `id_profesor` INT NOT NULL, -- FK a Usuarios
    PRIMARY KEY (`id_curso`),
    FOREIGN KEY (`id_escuela`) REFERENCES `Escuelas`(`id_escuela`)
        ON DELETE CASCADE,
    FOREIGN KEY (`id_profesor`) REFERENCES `Usuarios`(`id_usuario`)
        ON DELETE RESTRICT
);

CREATE TABLE `Usuario_Curso` (
    `id_usuario_curso` INT NOT NULL AUTO_INCREMENT,
    `id_usuario` INT NOT NULL, -- FK al estudiante
    `id_curso` INT NOT NULL,   -- FK al curso
    `fecha_inscripcion` DATETIME DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id_usuario_curso`),
    UNIQUE KEY `uk_usuario_curso` (`id_usuario`, `id_curso`),
    FOREIGN KEY (`id_usuario`) REFERENCES `Usuarios`(`id_usuario`)
        ON DELETE CASCADE,
    FOREIGN KEY (`id_curso`) REFERENCES `Cursos`(`id_curso`)
        ON DELETE CASCADE
);

CREATE TABLE `Usuario_Escuela` (
    `id_usuario_escuela` INT NOT NULL AUTO_INCREMENT,
    `id_usuario` INT NOT NULL,
    `id_escuela` INT NOT NULL,
    `fecha_vinculo` DATETIME DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id_usuario_escuela`),
    UNIQUE KEY `uk_usuario_escuela` (`id_usuario`, `id_escuela`),
    FOREIGN KEY (`id_usuario`) REFERENCES `Usuarios`(`id_usuario`)
        ON DELETE CASCADE,
    FOREIGN KEY (`id_escuela`) REFERENCES `Escuelas`(`id_escuela`)
        ON DELETE CASCADE
);

CREATE TABLE `Items` (
    `id_item` INT NOT NULL AUTO_INCREMENT,
    `id_test` INT NOT NULL,
    `texto_item` TEXT NOT NULL,
    `subescala` VARCHAR(50),
    `orden` INT NOT NULL,
    PRIMARY KEY (`id_item`),
    FOREIGN KEY (`id_test`) REFERENCES `Tests`(`id_test`)
        ON DELETE CASCADE
);

CREATE TABLE `Aplicaciones` (
    `id_aplicacion` INT NOT NULL AUTO_INCREMENT,
    `id_usuario` INT NOT NULL,
    `id_test` INT NOT NULL,
    `client_uuid` VARCHAR(36) NULL DEFAULT NULL,
    `fecha_aplicacion` DATETIME DEFAULT CURRENT_TIMESTAMP,
    
    -- Campos originales (mantener compatibilidad)
    `puntuacion_total` INT,
    `resultado_nivel` VARCHAR(50) COMMENT 'Texto libre para compatibilidad',
    
    -- Campos de cálculo dinámico
    `puntuacion_maxima` INT NULL COMMENT 'Puntuación máxima posible: num_items × max_valor_escala',
    `porcentaje_score` DECIMAL(5,2) NULL COMMENT 'Porcentaje para cálculo de nivel (0.00-100.00)',
    `nivel_calculado` ENUM('normal','leve','moderado','alto','severo') NULL COMMENT 'Nivel según baremo',
    
    -- Estadísticas comparativas
    `z_score` DECIMAL(10,4) NULL COMMENT 'Puntuación estandarizada (puede ser NULL si SD < 0.01)',
    `percentil` DECIMAL(5,2) NULL COMMENT 'Posición en distribución poblacional (0.00-100.00)',
    
    -- Métricas de cambio
    `cambio_pct` DECIMAL(6,2) NULL COMMENT 'Diferencia de porcentajes vs aplicación anterior del MISMO tipo',
    `cambio_absoluto` INT NULL COMMENT 'Diferencia en puntos brutos vs aplicación anterior',
    
    -- Flags de validación
    `completo` BOOLEAN DEFAULT TRUE COMMENT 'Siempre TRUE (formulario valida 100% respuestas)',
    `es_primera_aplicacion` BOOLEAN DEFAULT FALSE COMMENT 'TRUE si es la primera del tipo_test',
    
    -- Metadatos de origen
    `origen` ENUM('estudiante_voluntario','profesor_sugerencia','sistema_automatico') DEFAULT 'estudiante_voluntario' COMMENT 'Origen de la aplicación del test',
    `fecha_finalizacion` DATETIME NULL COMMENT 'Fecha de finalización del test',
    `notas_calculo` TEXT NULL COMMENT 'Log breve de cálculos: baremo usado, z-score, flags especiales',
    
    PRIMARY KEY (`id_aplicacion`),
    UNIQUE KEY `idx_aplicaciones_client_uuid` (`client_uuid`),
    FOREIGN KEY (`id_usuario`) REFERENCES `Usuarios`(`id_usuario`)
        ON DELETE CASCADE,
    FOREIGN KEY (`id_test`) REFERENCES `Tests`(`id_test`)
        ON DELETE RESTRICT,
    
    -- Índices para reportes rápidos
    INDEX idx_usuario_test_fecha (id_usuario, id_test, fecha_finalizacion),
    INDEX idx_test_porcentaje (id_test, porcentaje_score),
    INDEX idx_fecha (fecha_finalizacion),
    INDEX idx_nivel (nivel_calculado),
    INDEX idx_tipo_completo (id_test, completo, fecha_finalizacion)
) COMMENT='Aplicaciones de tests con métricas psicométricas completas';

CREATE TABLE `Respuestas_Aplicacion` (
    `id_respuesta` INT NOT NULL AUTO_INCREMENT,
    `id_aplicacion` INT NOT NULL,
    `id_item` INT NOT NULL,
    `id_opcion_seleccionada` INT NOT NULL,
    `puntuacion_obtenida` INT NOT NULL,
    PRIMARY KEY (`id_respuesta`),
    FOREIGN KEY (`id_aplicacion`) REFERENCES `Aplicaciones`(`id_aplicacion`)
        ON DELETE CASCADE,
    FOREIGN KEY (`id_item`) REFERENCES `Items`(`id_item`)
        ON DELETE RESTRICT,
    FOREIGN KEY (`id_opcion_seleccionada`) REFERENCES `Opciones_Respuesta`(`id_opcion`)
        ON DELETE RESTRICT,
    UNIQUE KEY `uk_aplicacion_item` (`id_aplicacion`, `id_item`)
);


-- Tabla para registrar sugerencias de tests por el profesor
-- Cambio de diseño: cada sugerencia es por estudiante, no por curso
-- Se rastrean múltiples profesores/cursos mediante campos agregados
CREATE TABLE IF NOT EXISTS `Sugerencias` (
    `id_sugerencia` INT AUTO_INCREMENT PRIMARY KEY,
    `id_estudiante` INT NOT NULL,
    `id_test` INT NOT NULL,
    `profesores_ids` TEXT NULL COMMENT 'JSON array de IDs de profesores que sugirieron',
    `cursos_ids` TEXT NULL COMMENT 'JSON array de IDs de cursos desde donde se sugirió',
    `fecha_sugerencia` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `fecha_ultima_sugerencia` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `estado` ENUM('pendiente', 'visto') DEFAULT 'pendiente',
    FOREIGN KEY (`id_estudiante`) REFERENCES `Usuarios`(`id_usuario`) ON DELETE CASCADE,
    FOREIGN KEY (`id_test`) REFERENCES `Tests`(`id_test`) ON DELETE CASCADE,
    UNIQUE KEY `uk_estudiante_test` (`id_estudiante`, `id_test`)
);

-- Tabla para controlar las sugerencias a nivel de curso (restricción de 1 mes)
-- Registra cada vez que un profesor sugiere un test a un curso completo
CREATE TABLE IF NOT EXISTS `Sugerencias_Curso` (
    `id_sugerencia_curso` INT AUTO_INCREMENT PRIMARY KEY,
    `id_curso` INT NOT NULL,
    `id_test` INT NOT NULL,
    `id_profesor` INT NOT NULL,
    `fecha_sugerencia` DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`id_curso`) REFERENCES `Cursos`(`id_curso`) ON DELETE CASCADE,
    FOREIGN KEY (`id_test`) REFERENCES `Tests`(`id_test`) ON DELETE CASCADE,
    FOREIGN KEY (`id_profesor`) REFERENCES `Usuarios`(`id_usuario`) ON DELETE CASCADE,
    INDEX `idx_curso_test_fecha` (`id_curso`, `id_test`, `fecha_sugerencia`)
);

-- Tabla para registrar intentos de sincronización desde PWA
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

CREATE TABLE IF NOT EXISTS `Citas` (
    `id_cita` INT NOT NULL AUTO_INCREMENT,
    `id_alumno` INT NOT NULL,
    `fecha_cita` DATETIME NOT NULL,
    `motivo` VARCHAR(255),
    `estado` ENUM('pendiente', 'confirmada', 'cancelada') DEFAULT 'pendiente',
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id_cita`),
    FOREIGN KEY (`id_alumno`) REFERENCES `Usuarios`(`id_usuario`) ON DELETE CASCADE
);

-- Tabla para notificaciones de usuario
CREATE TABLE `Notificaciones` (
    `id_notificacion` INT NOT NULL AUTO_INCREMENT,
    `id_usuario` INT NOT NULL, -- Usuario destinatario
    `titulo` VARCHAR(255) NOT NULL,
    `mensaje` TEXT NOT NULL,
    `tipo` VARCHAR(50) DEFAULT 'info', -- info, warning, error, success
    `estado` ENUM('nueva', 'leida', 'eliminada') DEFAULT 'nueva',
    `fecha_creacion` DATETIME DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id_notificacion`),
    FOREIGN KEY (`id_usuario`) REFERENCES `Usuarios`(`id_usuario`) ON DELETE CASCADE
);

-- ============================================
-- TABLAS PARA SISTEMA DE MÉTRICAS PSICOMÉTRICAS
-- ============================================

-- Tabla de Baremos: Define rangos clínicos basados en porcentaje de puntuación
-- Funciona con CUALQUIER test sin importar escala o número de ítems
CREATE TABLE IF NOT EXISTS `Baremos` (
    `id_baremo` INT AUTO_INCREMENT PRIMARY KEY,
    `tipo_test` ENUM('estres','ansiedad') NOT NULL,
    `nivel` ENUM('normal','leve','moderado','alto','severo') NOT NULL,
    `pct_min` DECIMAL(5,2) NOT NULL COMMENT 'Porcentaje mínimo (0.00-100.00) - INCLUSIVO',
    `pct_max` DECIMAL(5,2) NOT NULL COMMENT 'Porcentaje máximo (0.00-100.01) - EXCLUSIVO excepto último',
    `descripcion` TEXT NULL,
    `color_hex` VARCHAR(7) NULL COMMENT 'Color para visualización #RRGGBB',
    `orden` INT NOT NULL COMMENT 'Orden de severidad: 1=normal, 5=severo',
    `activo` BOOLEAN DEFAULT TRUE,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_tipo_porcentaje (tipo_test, pct_min, pct_max),
    UNIQUE KEY uk_tipo_nivel_activo (tipo_test, nivel, activo)
) COMMENT='Baremos por porcentaje para cálculo dinámico de niveles clínicos';

-- Tabla de Estadísticas Poblacionales: Para calcular z-scores y percentiles
-- Se actualiza automáticamente cada semana/mes
CREATE TABLE IF NOT EXISTS `Estadisticas_Poblacionales` (
    `id_estadistica` INT AUTO_INCREMENT PRIMARY KEY,
    `tipo_test` ENUM('estres','ansiedad') NOT NULL,
    `id_escuela` INT NULL COMMENT 'NULL = global, específico = por escuela',
    `media` DECIMAL(10,2) NOT NULL,
    `desviacion` DECIMAL(10,2) NOT NULL,
    `n_muestral` INT NOT NULL COMMENT 'Tamaño de muestra (mínimo 30 para validez)',
    `fecha_calculo` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `activo` BOOLEAN DEFAULT TRUE COMMENT 'Solo una estadística activa por tipo_test',
    FOREIGN KEY (`id_escuela`) REFERENCES `Escuelas`(`id_escuela`) ON DELETE CASCADE,
    INDEX idx_tipo_activo (tipo_test, activo),
    UNIQUE KEY uk_tipo_escuela_activo (tipo_test, id_escuela, activo)
) COMMENT='Estadísticas poblacionales para z-scores. Solo calcular si n >= 30';

-- Tabla de Agregaciones: Pre-cálculo de métricas grupales para reportes rápidos
-- Se actualiza mediante trigger o cron job
CREATE TABLE IF NOT EXISTS `Agregaciones` (
    `id_agregacion` INT AUTO_INCREMENT PRIMARY KEY,
    `tipo_grupo` ENUM('curso','escuela') NOT NULL,
    `id_grupo` INT NOT NULL COMMENT 'id_curso o id_escuela',
    `tipo_test` ENUM('estres','ansiedad') NOT NULL,
    `periodo` ENUM('semanal','mensual','trimestral','anual') NOT NULL,
    `fecha_inicio` DATE NOT NULL,
    `fecha_fin` DATE NOT NULL,
    
    -- Estadísticas básicas
    `promedio` DECIMAL(10,2) NOT NULL,
    `promedio_porcentaje` DECIMAL(5,2) NOT NULL,
    `desviacion` DECIMAL(10,2) NOT NULL,
    `mediana` DECIMAL(10,2) NULL,
    
    -- Conteos
    `num_aplicaciones` INT NOT NULL,
    `num_estudiantes` INT NOT NULL,
    `num_estudiantes_riesgo` INT DEFAULT 0 COMMENT 'Con nivel alto o severo',
    `num_riesgo_emergente` INT DEFAULT 0 COMMENT 'Subieron 2+ niveles en <14 días',
    
    -- Distribución de niveles
    `nivel_predominante` ENUM('normal','leve','moderado','alto','severo') NOT NULL,
    `dist_normal` INT DEFAULT 0,
    `dist_leve` INT DEFAULT 0,
    `dist_moderado` INT DEFAULT 0,
    `dist_alto` INT DEFAULT 0,
    `dist_severo` INT DEFAULT 0,
    
    -- Cambios
    `cambio_vs_periodo_anterior` DECIMAL(6,2) NULL COMMENT 'Cambio porcentual de promedio',
    
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_grupo_tipo_periodo (tipo_grupo, id_grupo, tipo_test, periodo, fecha_inicio)
) COMMENT='Agregaciones pre-calculadas para reportes de curso y escuela';