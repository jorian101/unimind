-- MariaDB dump 10.19  Distrib 10.4.28-MariaDB, for Linux (x86_64)
--
-- Host: localhost    Database: db_tests_estres_ansiedad
-- ------------------------------------------------------
-- Server version	10.4.28-MariaDB

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `Aplicaciones`
--

DROP TABLE IF EXISTS `Aplicaciones`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Aplicaciones` (
  `id_aplicacion` int(11) NOT NULL AUTO_INCREMENT,
  `id_usuario` int(11) NOT NULL,
  `id_test` int(11) NOT NULL,
  `fecha_aplicacion` datetime DEFAULT current_timestamp(),
  `puntuacion_total` int(11) DEFAULT NULL,
  `resultado_nivel` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`id_aplicacion`),
  KEY `id_usuario` (`id_usuario`),
  KEY `id_test` (`id_test`),
  CONSTRAINT `Aplicaciones_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `Usuarios` (`id_usuario`) ON DELETE CASCADE,
  CONSTRAINT `Aplicaciones_ibfk_2` FOREIGN KEY (`id_test`) REFERENCES `Tests` (`id_test`)
) ENGINE=InnoDB AUTO_INCREMENT=31 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `Aplicaciones`
--

LOCK TABLES `Aplicaciones` WRITE;
/*!40000 ALTER TABLE `Aplicaciones` DISABLE KEYS */;
INSERT INTO `Aplicaciones` VALUES (1,1,1,'2025-10-30 09:00:00',35,'Ansiedad Moderada'),(2,2,2,'2025-10-30 10:00:00',18,'Estrés Bajo'),(3,3,3,'2025-10-30 11:00:00',25,'Depresión Leve'),(4,4,1,'2025-10-30 12:00:00',40,'Ansiedad Alta'),(5,5,2,'2025-10-31 08:30:00',12,'Estrés Mínimo'),(6,6,3,'2025-10-31 09:30:00',35,'Depresión Moderada'),(7,7,1,'2025-10-31 10:30:00',30,'Ansiedad Media'),(8,8,2,'2025-10-31 11:30:00',25,'Estrés Medio'),(9,9,3,'2025-10-31 12:30:00',45,'Depresión Severa'),(10,10,1,'2025-11-01 14:00:00',20,'Ansiedad Baja'),(11,11,2,'2025-11-01 15:00:00',30,'Estrés Alto'),(12,12,3,'2025-11-01 16:00:00',15,'Depresión Mínima'),(13,13,1,'2025-11-02 09:00:00',45,'Ansiedad Severa'),(14,14,2,'2025-11-02 10:00:00',16,'Estrés Bajo'),(15,15,3,'2025-11-02 11:00:00',28,'Depresión Leve'),(16,16,1,'2025-11-03 13:00:00',38,'Ansiedad Moderada'),(17,17,2,'2025-11-03 14:00:00',22,'Estrés Medio'),(18,18,3,'2025-11-03 15:00:00',30,'Depresión Moderada'),(19,19,1,'2025-11-04 09:00:00',25,'Ansiedad Media'),(20,20,2,'2025-11-04 10:00:00',10,'Estrés Mínimo'),(21,21,3,'2025-11-04 11:00:00',40,'Depresión Alta'),(22,22,1,'2025-11-05 14:00:00',32,'Ansiedad Media'),(23,23,2,'2025-11-05 15:00:00',28,'Estrés Alto'),(24,24,3,'2025-11-05 16:00:00',20,'Depresión Leve'),(25,25,1,'2025-11-06 08:30:00',42,'Ansiedad Alta'),(26,26,2,'2025-11-06 09:30:00',15,'Estrés Bajo'),(27,27,3,'2025-11-06 10:30:00',33,'Depresión Moderada'),(28,28,1,'2025-11-07 11:00:00',28,'Ansiedad Media'),(29,29,2,'2025-11-07 12:00:00',20,'Estrés Medio'),(30,30,3,'2025-11-07 13:00:00',48,'Depresión Severa');
/*!40000 ALTER TABLE `Aplicaciones` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `Cursos`
--

DROP TABLE IF EXISTS `Cursos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Cursos` (
  `id_curso` int(11) NOT NULL AUTO_INCREMENT,
  `nombre_curso` varchar(150) NOT NULL,
  `id_escuela` int(11) NOT NULL,
  `id_profesor` int(11) NOT NULL,
  PRIMARY KEY (`id_curso`),
  KEY `id_escuela` (`id_escuela`),
  KEY `id_profesor` (`id_profesor`),
  CONSTRAINT `Cursos_ibfk_1` FOREIGN KEY (`id_escuela`) REFERENCES `Escuelas` (`id_escuela`) ON DELETE CASCADE,
  CONSTRAINT `Cursos_ibfk_2` FOREIGN KEY (`id_profesor`) REFERENCES `Usuarios` (`id_usuario`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `Cursos`
--

LOCK TABLES `Cursos` WRITE;
/*!40000 ALTER TABLE `Cursos` DISABLE KEYS */;
/*!40000 ALTER TABLE `Cursos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `Escuelas`
--

DROP TABLE IF EXISTS `Escuelas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Escuelas` (
  `id_escuela` int(11) NOT NULL AUTO_INCREMENT,
  `nombre_escuela` varchar(150) NOT NULL,
  `telefono` varchar(20) DEFAULT NULL,
  PRIMARY KEY (`id_escuela`),
  UNIQUE KEY `nombre_escuela` (`nombre_escuela`)
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `Escuelas`
--

LOCK TABLES `Escuelas` WRITE;
/*!40000 ALTER TABLE `Escuelas` DISABLE KEYS */;
INSERT INTO `Escuelas` VALUES (1,'Civil','555-1001'),(2,'Educacion','555-1002'),(3,'Artes','555-1003'),(4,'Ambiental','555-1004'),(5,'Medicina','555-1005'),(6,'Biologia','555-1006'),(7,'Veterinaria','555-1007'),(8,'Alimentaria','555-1008'),(9,'Sistemas','555-1009'),(10,'Administracion','555-1010'),(11,'Psicologia','555-1011'),(12,'Obs','555-1012'),(13,'Conta','555-1013');
/*!40000 ALTER TABLE `Escuelas` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `Items`
--

DROP TABLE IF EXISTS `Items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Items` (
  `id_item` int(11) NOT NULL AUTO_INCREMENT,
  `id_test` int(11) NOT NULL,
  `texto_item` text NOT NULL,
  `subescala` varchar(50) DEFAULT NULL,
  `orden` int(11) NOT NULL,
  PRIMARY KEY (`id_item`),
  KEY `id_test` (`id_test`),
  CONSTRAINT `Items_ibfk_1` FOREIGN KEY (`id_test`) REFERENCES `Tests` (`id_test`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=31 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `Items`
--

LOCK TABLES `Items` WRITE;
/*!40000 ALTER TABLE `Items` DISABLE KEYS */;
INSERT INTO `Items` VALUES (1,1,'Me he sentido más nervioso/a o ansioso/a de lo habitual.','Ansiedad Emocional',1),(2,1,'He tenido problemas para relajarme.','Ansiedad Tensión',2),(3,1,'He estado preocupado/a por demasiadas cosas.','Preocupación',3),(4,1,'He sentido que mi corazón latía muy rápido o con fuerza.','Síntomas Físicos',4),(5,1,'He tenido dificultad para conciliar el sueño.','Ansiedad Tensión',5),(6,1,'Me he asustado fácilmente.','Ansiedad Emocional',6),(7,1,'He tenido dificultad para concentrarme.','Preocupación',7),(8,1,'Me he sentido inquieto/a o incapaz de quedarme quieto/a.','Ansiedad Tensión',8),(9,1,'Me he sentido mareado/a o con la cabeza ligera.','Síntomas Físicos',9),(10,1,'He tenido la sensación de que algo terrible iba a pasar.','Preocupación',10),(11,2,'La cantidad de tareas me resulta abrumadora.','Carga Laboral',1),(12,2,'Siento presión por obtener buenas calificaciones.','Presión Externa',2),(13,2,'He tenido dificultad para organizar mi tiempo de estudio.','Autogestión',3),(14,2,'Siento que el tiempo no me alcanza para estudiar todo.','Carga Laboral',4),(15,2,'Me preocupo por el resultado de los exámenes.','Presión Externa',5),(16,2,'He tenido dolores de cabeza o estómago por el estudio.','Síntomas Físicos',6),(17,2,'He pospuesto tareas importantes.','Autogestión',7),(18,2,'Me cuesta relajarme después de un día de clases/estudio.','Carga Laboral',8),(19,3,'Tristeza (estado de ánimo).','Afectivo',1),(20,3,'Sentimientos de culpa.','Afectivo',2),(21,3,'Suicidio.','Afectivo',3),(22,3,'Insomnio precoz (dificultad para conciliar el sueño).','Somático',4),(23,3,'Insomnio medio (despertar en la noche).','Somático',5),(24,3,'Insomnio tardío (despertar temprano).','Somático',6),(25,3,'Trabajo y actividades.','Conductual',7),(26,3,'Inhibición / Retraso.','Conductual',8),(27,3,'Agitación.','Conductual',9),(28,3,'Ansiedad psíquica (tensión, miedos).','Afectivo',10),(29,3,'Ansiedad somática (síntomas físicos).','Somático',11),(30,3,'Síntomas somáticos generales (peso, boca seca).','Somático',12);
/*!40000 ALTER TABLE `Items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `Opciones_Respuesta`
--

DROP TABLE IF EXISTS `Opciones_Respuesta`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Opciones_Respuesta` (
  `id_opcion` int(11) NOT NULL AUTO_INCREMENT,
  `texto_opcion` varchar(100) NOT NULL,
  `valor_puntuacion` int(11) NOT NULL,
  PRIMARY KEY (`id_opcion`)
) ENGINE=InnoDB AUTO_INCREMENT=35 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `Opciones_Respuesta`
--

LOCK TABLES `Opciones_Respuesta` WRITE;
/*!40000 ALTER TABLE `Opciones_Respuesta` DISABLE KEYS */;
INSERT INTO `Opciones_Respuesta` VALUES (1,'Totalmente en desacuerdo',1),(2,'En desacuerdo',2),(3,'De acuerdo',3),(4,'Totalmente de acuerdo',4),(5,'Nunca',0),(6,'Casi nunca',1),(7,'A veces',2),(8,'A menudo',3),(9,'Siempre',4),(10,'Nada en absoluto',0),(11,'Un poco',1),(12,'Bastante',2),(13,'Mucho',3),(14,'No',0),(15,'Sí',1),(16,'0 - Nunca',0),(17,'1 - Pocas veces al año',1),(18,'2 - Una vez al mes',2),(19,'3 - Pocas veces al mes',3),(20,'4 - Una vez a la semana',4),(21,'Muy bueno',0),(22,'Bueno',1),(23,'Regular',2),(24,'Malo',3),(25,'Muy malo',4),(26,'No me siento así',0),(27,'Raramente',1),(28,'A veces',2),(29,'Casi siempre',3),(30,'Muy insatisfecho',1),(31,'Insatisfecho',2),(32,'Neutral',3),(33,'Satisfecho',4),(34,'Muy Satisfecho',5);
/*!40000 ALTER TABLE `Opciones_Respuesta` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `Respuestas_Aplicacion`
--

DROP TABLE IF EXISTS `Respuestas_Aplicacion`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Respuestas_Aplicacion` (
  `id_respuesta` int(11) NOT NULL AUTO_INCREMENT,
  `id_aplicacion` int(11) NOT NULL,
  `id_item` int(11) NOT NULL,
  `id_opcion_seleccionada` int(11) NOT NULL,
  `puntuacion_obtenida` int(11) NOT NULL,
  PRIMARY KEY (`id_respuesta`),
  UNIQUE KEY `uk_aplicacion_item` (`id_aplicacion`,`id_item`),
  KEY `id_item` (`id_item`),
  KEY `id_opcion_seleccionada` (`id_opcion_seleccionada`),
  CONSTRAINT `Respuestas_Aplicacion_ibfk_1` FOREIGN KEY (`id_aplicacion`) REFERENCES `Aplicaciones` (`id_aplicacion`) ON DELETE CASCADE,
  CONSTRAINT `Respuestas_Aplicacion_ibfk_2` FOREIGN KEY (`id_item`) REFERENCES `Items` (`id_item`),
  CONSTRAINT `Respuestas_Aplicacion_ibfk_3` FOREIGN KEY (`id_opcion_seleccionada`) REFERENCES `Opciones_Respuesta` (`id_opcion`)
) ENGINE=InnoDB AUTO_INCREMENT=31 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `Respuestas_Aplicacion`
--

LOCK TABLES `Respuestas_Aplicacion` WRITE;
/*!40000 ALTER TABLE `Respuestas_Aplicacion` DISABLE KEYS */;
INSERT INTO `Respuestas_Aplicacion` VALUES (1,1,1,4,4),(2,1,2,3,3),(3,1,3,4,4),(4,1,4,3,3),(5,2,11,2,2),(6,2,12,1,1),(7,3,13,3,3),(8,3,14,2,2),(9,3,15,3,3),(10,3,16,2,2),(11,4,1,4,4),(12,4,2,4,4),(13,4,3,4,4),(14,4,4,4,4),(15,5,11,1,1),(16,5,12,1,1),(17,6,13,4,4),(18,6,14,3,3),(19,6,15,4,4),(20,6,16,3,3),(21,7,5,3,3),(22,7,6,3,3),(23,7,7,3,3),(24,7,8,3,3),(25,8,11,3,3),(26,8,12,3,3),(27,9,13,4,4),(28,9,14,4,4),(29,9,15,4,4),(30,9,16,4,4);
/*!40000 ALTER TABLE `Respuestas_Aplicacion` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `Tests`
--

DROP TABLE IF EXISTS `Tests`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Tests` (
  `id_test` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `num_items` int(11) NOT NULL,
  PRIMARY KEY (`id_test`),
  UNIQUE KEY `nombre` (`nombre`)
) ENGINE=InnoDB AUTO_INCREMENT=31 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `Tests`
--

LOCK TABLES `Tests` WRITE;
/*!40000 ALTER TABLE `Tests` DISABLE KEYS */;
INSERT INTO `Tests` VALUES (1,'Test Ansiedad Generalizada 1','Inventario para medir la ansiedad general.',15),(2,'Test Estrés Académico Corto','Mide el nivel de estrés asociado a tareas estudiantiles.',8),(3,'Escala de Depresión Hamilton','Para evaluar la severidad de la depresión.',10),(4,'Inventario de Burnout de Maslach','Mide el agotamiento emocional, despersonalización y logros personales.',22),(5,'Escala de Resiliencia Connor-Davidson','Mide la capacidad de afrontar la adversidad.',10),(6,'Cuestionario de Miedos Específicos','Evalúa la presencia de fobias específicas.',12),(7,'Test de Calidad de Sueño PSQI','Evalúa la calidad del sueño a lo largo de un mes.',7),(8,'Inventario de Ansiedad Social','Mide la ansiedad en situaciones sociales.',15),(9,'Escala de Estrés Laboral ELS','Específico para el entorno de trabajo.',10),(10,'Cuestionario de Autoestima Rosenberg','Mide la autoestima global.',10),(11,'Test Ansiedad Generalizada 2','Versión alternativa para ansiedad.',15),(12,'Test Estrés Académico Largo','Versión detallada para estrés estudiantil.',15),(13,'Escala de Estrés Parental','Mide el estrés derivado de la crianza.',10),(14,'Cuestionario de Afrontamiento al Estrés (COPE)','Evalúa estrategias de afrontamiento.',60),(15,'Test de Clima Laboral','Evalúa la percepción del ambiente de trabajo.',10),(16,'Inventario de Síntomas Somáticos','Mide síntomas físicos relacionados con el estrés.',10),(17,'Escala de Satisfacción con la Vida','Evalúa el bienestar subjetivo.',5),(18,'Cuestionario de Perfeccionismo','Mide tendencias perfeccionistas.',10),(19,'Test de Preocupación Crónica','Evalúa la tendencia a la preocupación excesiva.',10),(20,'Escala de Riesgo Suicida (BHS)','Evalúa el riesgo de autolesión.',20),(21,'Test Ansiedad de Examen','Específico para situaciones de evaluación.',10),(22,'Escala de Estrés Familiar','Mide el estrés en el contexto familiar.',8),(23,'Inventario de Ira (STAXI)','Evalúa la experiencia y expresión de la ira.',10),(24,'Cuestionario de Detección de Trauma (PCL)','Evalúa síntomas de estrés postraumático.',20),(25,'Escala de Apego en Adultos','Evalúa patrones de apego.',10),(26,'Test de Habilidades Sociales','Mide la competencia social.',10),(27,'Inventario de Estrategias de Regulación Emocional','Evalúa cómo se regulan las emociones.',10),(28,'Escala de Soledad UCLA','Mide la sensación de soledad.',20),(29,'Cuestionario de Estilos de Vida Saludable','Evalúa hábitos de salud.',15),(30,'Test de Motivación Intrínseca','Mide el grado de motivación interna.',10);
/*!40000 ALTER TABLE `Tests` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `Usuario_Curso`
--

DROP TABLE IF EXISTS `Usuario_Curso`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Usuario_Curso` (
  `id_usuario_curso` int(11) NOT NULL AUTO_INCREMENT,
  `id_usuario` int(11) NOT NULL,
  `id_curso` int(11) NOT NULL,
  `fecha_inscripcion` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id_usuario_curso`),
  UNIQUE KEY `uk_usuario_curso` (`id_usuario`,`id_curso`),
  KEY `id_curso` (`id_curso`),
  CONSTRAINT `Usuario_Curso_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `Usuarios` (`id_usuario`) ON DELETE CASCADE,
  CONSTRAINT `Usuario_Curso_ibfk_2` FOREIGN KEY (`id_curso`) REFERENCES `Cursos` (`id_curso`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `Usuario_Curso`
--

LOCK TABLES `Usuario_Curso` WRITE;
/*!40000 ALTER TABLE `Usuario_Curso` DISABLE KEYS */;
/*!40000 ALTER TABLE `Usuario_Curso` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `Usuario_Escuela`
--

DROP TABLE IF EXISTS `Usuario_Escuela`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Usuario_Escuela` (
  `id_usuario_escuela` int(11) NOT NULL AUTO_INCREMENT,
  `id_usuario` int(11) NOT NULL,
  `id_escuela` int(11) NOT NULL,
  `fecha_vinculo` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id_usuario_escuela`),
  UNIQUE KEY `uk_usuario_escuela` (`id_usuario`,`id_escuela`),
  KEY `id_escuela` (`id_escuela`),
  CONSTRAINT `Usuario_Escuela_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `Usuarios` (`id_usuario`) ON DELETE CASCADE,
  CONSTRAINT `Usuario_Escuela_ibfk_2` FOREIGN KEY (`id_escuela`) REFERENCES `Escuelas` (`id_escuela`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=31 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `Usuario_Escuela`
--

LOCK TABLES `Usuario_Escuela` WRITE;
/*!40000 ALTER TABLE `Usuario_Escuela` DISABLE KEYS */;
INSERT INTO `Usuario_Escuela` VALUES (1,1,1,'2025-11-22 19:17:04'),(2,2,2,'2025-11-22 19:17:04'),(3,3,3,'2025-11-22 19:17:04'),(4,4,4,'2025-11-22 19:17:04'),(5,5,5,'2025-11-22 19:17:04'),(6,6,6,'2025-11-22 19:17:04'),(7,7,7,'2025-11-22 19:17:04'),(8,8,8,'2025-11-22 19:17:04'),(9,9,9,'2025-11-22 19:17:04'),(10,10,10,'2025-11-22 19:17:04'),(11,11,11,'2025-11-22 19:17:04'),(12,12,12,'2025-11-22 19:17:04'),(13,13,13,'2025-11-22 19:17:04'),(14,14,13,'2025-11-22 19:17:04'),(15,15,12,'2025-11-22 19:17:04'),(16,16,12,'2025-11-22 19:17:04'),(17,17,9,'2025-11-22 19:17:04'),(18,18,2,'2025-11-22 19:17:04'),(19,19,7,'2025-11-22 19:17:04'),(20,20,3,'2025-11-22 19:17:04'),(21,21,8,'2025-11-22 19:17:04'),(22,22,7,'2025-11-22 19:17:04'),(23,23,5,'2025-11-22 19:17:04'),(24,24,4,'2025-11-22 19:17:04'),(25,25,10,'2025-11-22 19:17:04'),(26,26,2,'2025-11-22 19:17:04'),(27,27,1,'2025-11-22 19:17:04'),(28,28,4,'2025-11-22 19:17:04'),(29,29,9,'2025-11-22 19:17:04'),(30,30,6,'2025-11-22 19:17:04');
/*!40000 ALTER TABLE `Usuario_Escuela` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `Usuarios`
--

DROP TABLE IF EXISTS `Usuarios`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Usuarios` (
  `id_usuario` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) NOT NULL,
  `apellido` varchar(100) NOT NULL,
  `codigo_usuario` varchar(10) NOT NULL,
  `password` varchar(255) NOT NULL,
  `cargo` varchar(30) NOT NULL,
  `fecha_nacimiento` date DEFAULT NULL,
  `genero` varchar(10) DEFAULT NULL,
  `fecha_registro` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id_usuario`),
  UNIQUE KEY `codigo_usuario` (`codigo_usuario`)
) ENGINE=InnoDB AUTO_INCREMENT=31 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `Usuarios`
--

LOCK TABLES `Usuarios` WRITE;
/*!40000 ALTER TABLE `Usuarios` DISABLE KEYS */;
INSERT INTO `Usuarios` VALUES (1,'Andrea','Vargas','USR0001','password123','Estudiante','2004-03-10','Femenino','2025-11-22 19:17:02'),(2,'Javier','Ramos','USR0002','password123','Docente','1985-07-22','Masculino','2025-11-22 19:17:02'),(3,'Marta','Herrera','USR0003','password123','Administrador','1979-01-15','Femenino','2025-11-22 19:17:02'),(4,'Pedro','Guzmán','USR0004','password123','Administrador','1990-11-28','Masculino','2025-11-22 19:17:02'),(5,'Laura','Díaz','USR0005','password123','Estudiante','2003-05-01','Femenino','2025-11-22 19:17:02'),(6,'Ricardo','Flores','USR0006','password123','Docente','1970-12-05','Masculino','2025-11-22 19:17:02'),(7,'Elena','Soto','USR0007','password123','Estudiante','2006-09-19','Femenino','2025-11-22 19:17:02'),(8,'Miguel','López','USR0008','password123','Administrador','1982-04-14','Masculino','2025-11-22 19:17:02'),(9,'Fernanda','Castro','USR0009','password123','Estudiante','2002-02-28','Femenino','2025-11-22 19:17:02'),(10,'Daniel','Ruiz','USR0010','password123','Docente','1965-08-30','Masculino','2025-11-22 19:17:02'),(11,'Carla','Mendoza','USR0011','password123','Estudiante','2005-10-12','Femenino','2025-11-22 19:17:02'),(12,'Jorge','Paredes','USR0012','password123','Administrador','1998-06-03','Masculino','2025-11-22 19:17:02'),(13,'Silvia','Quispe','USR0013','password123','Administrador','1977-03-07','Femenino','2025-11-22 19:17:02'),(14,'Roberto','Torres','USR0014','password123','Estudiante','2007-01-25','Masculino','2025-11-22 19:17:02'),(15,'Valeria','Núñez','USR0015','password123','Docente','1989-11-11','Femenino','2025-11-22 19:17:02'),(16,'Alejandro','Vela','USR0016','password123','Estudiante','2004-04-04','Masculino','2025-11-22 19:17:02'),(17,'Cecilia','Baca','USR0017','password123','Administrador','1993-02-09','Femenino','2025-11-22 19:17:02'),(18,'Felipe','Cáceres','USR0018','password123','Administrador','1980-10-21','Masculino','2025-11-22 19:17:02'),(19,'Gabriela','Linares','USR0019','password123','Estudiante','2006-12-31','Femenino','2025-11-22 19:17:02'),(20,'Héctor','Salas','USR0020','password123','Docente','1972-01-01','Masculino','2025-11-22 19:17:02'),(21,'Irene','Zapata','USR0021','password123','Estudiante','2003-08-17','Femenino','2025-11-22 19:17:02'),(22,'Juan','Alonso','USR0022','password123','Administrador','1995-05-18','Masculino','2025-11-22 19:17:02'),(23,'Kelly','Bravo','USR0023','password123','Administrador','1974-09-02','Femenino','2025-11-22 19:17:02'),(24,'Omar','Molina','USR0024','password123','Estudiante','2005-06-20','Masculino','2025-11-22 19:17:02'),(25,'Patricia','Ríos','USR0025','password123','Docente','1987-04-26','Femenino','2025-11-22 19:17:02'),(26,'Quentin','Luna','USR0026','password123','Estudiante','2002-11-03','Masculino','2025-11-22 19:17:02'),(27,'Rosa','Vega','USR0027','password123','Administrador','1991-07-13','Femenino','2025-11-22 19:17:02'),(28,'Samuel','Yáñez','USR0028','password123','Administrador','1976-03-29','Masculino','2025-11-22 19:17:02'),(29,'Teresa','Zúñiga','USR0029','password123','Estudiante','2004-01-08','Femenino','2025-11-22 19:17:02'),(30,'Víctor','Acuña','USR0030','password123','Docente','1968-05-16','Masculino','2025-11-22 19:17:02');
/*!40000 ALTER TABLE `Usuarios` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2025-11-23  0:27:31
