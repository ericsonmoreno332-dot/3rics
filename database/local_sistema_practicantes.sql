-- MariaDB dump 10.19  Distrib 10.4.32-MariaDB, for Win64 (AMD64)
--
-- Host: localhost    Database: sistema_practicantes
-- ------------------------------------------------------
-- Server version	10.4.32-MariaDB

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
-- Temporary table structure for view `areas`
--

DROP TABLE IF EXISTS `areas`;
/*!50001 DROP VIEW IF EXISTS `areas`*/;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
/*!50001 CREATE VIEW `areas` AS SELECT
 1 AS `id`,
  1 AS `nombre`,
  1 AS `encargado`,
  1 AS `cargo`,
  1 AS `estado` */;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `asistencias`
--

DROP TABLE IF EXISTS `asistencias`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `asistencias` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `practicante_id` int(11) NOT NULL,
  `fecha` date NOT NULL,
  `hora_entrada` time DEFAULT NULL,
  `hora_salida` time DEFAULT NULL,
  `estado` enum('presente','tardanza','falta') DEFAULT 'presente',
  `metodo_entrada` enum('manual','qr','dni','geo') DEFAULT 'manual',
  `metodo_salida` enum('manual','qr','dni','geo') DEFAULT 'manual',
  `observacion` text DEFAULT NULL,
  `lat_entrada` decimal(10,8) DEFAULT NULL,
  `lng_entrada` decimal(11,8) DEFAULT NULL,
  `lat_salida` decimal(10,8) DEFAULT NULL,
  `lng_salida` decimal(11,8) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_asistencia_dia` (`practicante_id`,`fecha`),
  KEY `idx_asistencias_fecha` (`fecha`),
  KEY `idx_asistencias_pract_fecha` (`practicante_id`,`fecha`),
  CONSTRAINT `fk_asistencia_practicante` FOREIGN KEY (`practicante_id`) REFERENCES `practicantes` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `asistencias`
--

LOCK TABLES `asistencias` WRITE;
/*!40000 ALTER TABLE `asistencias` DISABLE KEYS */;
INSERT INTO `asistencias` VALUES (4,4,'2026-06-08','00:09:43','00:09:51','presente','qr','qr',NULL,NULL,NULL,NULL,NULL,'2026-06-08 05:09:43','2026-06-08 05:09:51'),(5,5,'2026-06-08','15:44:45','15:45:04','tardanza','dni','manual','no iene qr',NULL,NULL,NULL,NULL,'2026-06-08 20:44:45','2026-06-08 20:45:04');
/*!40000 ALTER TABLE `asistencias` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `carreras`
--

DROP TABLE IF EXISTS `carreras`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `carreras` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(150) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=64 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `carreras`
--

LOCK TABLES `carreras` WRITE;
/*!40000 ALTER TABLE `carreras` DISABLE KEYS */;
INSERT INTO `carreras` VALUES (1,'Ingeniería de Sistemas'),(2,'Administración'),(3,'Contabilidad'),(4,'Diseño Gráfico'),(5,'Enfermería'),(6,'Arquitectura'),(7,'Administración Industrial'),(8,'Administración Logística'),(9,'Desarrollo de Software'),(10,'Ingeniería de Software con Inteligencia Artificial'),(11,'Ingeniería de Ciberseguridad'),(12,'Ingeniería de Soporte TI'),(13,'Diseño Gráfico Digital'),(14,'Electricidad Industrial'),(15,'Mecánica Automotriz'),(16,'Mecatrónica Automotriz'),(17,'Mecánica de Mantenimiento'),(18,'Seguridad Industrial y Prevención de Riesgos'),(19,'Diseño y Desarrollo de Videojuegos'),(20,'Electricista Industrial'),(21,'Agroindustria'),(22,'Hotelería y Turismo'),(23,'Tecnologías Ambientales'),(24,'Ingeniería Civil'),(25,'Ingeniería Industrial'),(26,'Ingeniería Ambiental'),(27,'Ingeniería Empresarial'),(28,'Ingeniería Informática'),(29,'Ingeniería de Sistemas e Informática'),(30,'Ingeniería Química'),(31,'Ingeniería en Industrias Alimentarias'),(32,'Ciencia de la Computación'),(33,'Medicina Humana'),(34,'Farmacia y Bioquímica'),(35,'Psicología'),(36,'Obstetricia'),(37,'Derecho'),(38,'Administración y Finanzas'),(39,'Administración y Marketing'),(40,'Administración y Negocios Internacionales'),(41,'Administración y Negocios Digitales'),(42,'Administración y Gestión Pública'),(43,'Administración y Gestión del Talento Humano'),(44,'Contabilidad y Finanzas'),(45,'Educación'),(46,'Matemática'),(47,'Física'),(48,'Química'),(49,'Arquitectura de Interiores'),(50,'Comunicación Audiovisual'),(51,'Marketing Digital'),(52,'Gestión Empresarial'),(53,'Negocios Internacionales'),(54,'Tecnología Médica'),(55,'Nutrición y Dietética'),(56,'Biología'),(57,'Agronomía'),(58,'Zootecnia'),(59,'Turismo, Hotelería y Gastronomía'),(60,'Ingeniería Agroindustrial'),(61,'Ingeniería Pesquera'),(62,'Ingeniería Mecánica Eléctrica'),(63,'Ingeniería Electrónica');
/*!40000 ALTER TABLE `carreras` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `horarios`
--

DROP TABLE IF EXISTS `horarios`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `horarios` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `hora_entrada` time NOT NULL,
  `hora_salida` time NOT NULL,
  `tolerancia_minutos` int(11) DEFAULT 10,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `horarios`
--

LOCK TABLES `horarios` WRITE;
/*!40000 ALTER TABLE `horarios` DISABLE KEYS */;
INSERT INTO `horarios` VALUES (1,'08:00:00','16:00:00',10,'2026-05-10 05:20:53');
/*!40000 ALTER TABLE `horarios` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `instituciones`
--

DROP TABLE IF EXISTS `instituciones`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `instituciones` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(150) NOT NULL,
  `tipo` enum('universidad','instituto') NOT NULL,
  `estado` enum('activo','inactivo') DEFAULT 'activo',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `nombre` (`nombre`)
) ENGINE=InnoDB AUTO_INCREMENT=64 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `instituciones`
--

LOCK TABLES `instituciones` WRITE;
/*!40000 ALTER TABLE `instituciones` DISABLE KEYS */;
INSERT INTO `instituciones` VALUES (1,'Universidad Nacional San Luis Gonzaga','universidad','activo','2026-05-10 05:20:53',NULL),(2,'Instituto Tecnológico de Ica','instituto','activo','2026-05-10 05:20:53',NULL),(3,'Universidad Peruana Los Andes','universidad','activo','2026-05-10 05:20:53',NULL),(44,'Universidad Privada de Ica','universidad','activo','2026-05-26 17:02:00',NULL),(45,'Universidad Tecnológica del Perú','universidad','activo','2026-05-26 17:02:00',NULL),(46,'Universidad Continental','universidad','activo','2026-05-26 17:02:00',NULL),(47,'Pontificia Universidad Católica del Perú','universidad','activo','2026-05-26 17:02:00',NULL),(48,'Universidad Nacional Mayor de San Marcos','universidad','activo','2026-05-26 17:02:00',NULL),(49,'Universidad César Vallejo','universidad','activo','2026-05-26 17:02:00',NULL),(50,'Universidad Peruana de Ciencias Aplicadas','universidad','activo','2026-05-26 17:02:00',NULL),(51,'Universidad Privada del Norte','universidad','activo','2026-05-26 17:02:00',NULL),(52,'Universidad Nacional de Ingeniería','universidad','activo','2026-05-26 17:02:00',NULL),(53,'SENATI','instituto','activo','2026-05-26 17:02:00',NULL),(54,'TECSUP','instituto','activo','2026-05-26 17:02:00',NULL),(55,'IDAT','instituto','activo','2026-05-26 17:02:00',NULL),(56,'Cibertec','instituto','activo','2026-05-26 17:02:00',NULL),(57,'SISE','instituto','activo','2026-05-26 17:02:00',NULL),(58,'SENCICO','instituto','activo','2026-05-26 17:02:00',NULL),(59,'Zegel IPAE','instituto','activo','2026-05-26 17:02:00',NULL),(60,'Instituto Peruano Canadiense','instituto','activo','2026-05-26 17:02:00',NULL),(61,'Instituto Data System','instituto','activo','2026-05-26 17:02:00',NULL),(62,'Instituto Alas Peruanas','instituto','activo','2026-05-26 17:02:00',NULL);
/*!40000 ALTER TABLE `instituciones` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `personas`
--

DROP TABLE IF EXISTS `personas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `personas` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `dni` char(8) NOT NULL,
  `nombres` varchar(100) DEFAULT NULL,
  `apellido_paterno` varchar(100) DEFAULT NULL,
  `apellido_materno` varchar(100) DEFAULT NULL,
  `fecha_consulta` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `dni` (`dni`)
) ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `personas`
--

LOCK TABLES `personas` WRITE;
/*!40000 ALTER TABLE `personas` DISABLE KEYS */;
INSERT INTO `personas` VALUES (1,'22309108','ERIKA MABEL','NAVARRO','HERNANDEZ','2026-05-14 02:38:31'),(3,'46702758','ELEDITA','VASQUEZ','FLORES','2026-05-14 02:35:35'),(5,'61072715','ERICSON SALE','MORENO','NAVARRO','2026-06-08 05:20:03'),(7,'80093096','OSCAR','USCATA','IPURRE','2026-05-14 13:25:04'),(9,'60810589','JHEFERSON JOAN','ESCATE','UNZUETA','2026-05-26 21:28:48');
/*!40000 ALTER TABLE `personas` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `practicantes`
--

DROP TABLE IF EXISTS `practicantes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `practicantes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `dni` char(8) NOT NULL,
  `nombres` varchar(100) NOT NULL,
  `apellidos` varchar(100) NOT NULL,
  `carrera` varchar(120) NOT NULL,
  `correo` varchar(120) DEFAULT NULL,
  `telefono` varchar(20) DEFAULT NULL,
  `foto` varchar(255) DEFAULT NULL,
  `institucion_id` int(11) DEFAULT NULL,
  `area_id` int(11) DEFAULT NULL,
  `fecha_inicio` date NOT NULL,
  `fecha_fin` date NOT NULL,
  `estado` enum('activo','finalizado','suspendido') DEFAULT 'activo',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `dni` (`dni`),
  UNIQUE KEY `correo` (`correo`),
  KEY `idx_practicantes_area` (`area_id`),
  KEY `idx_practicantes_inst` (`institucion_id`),
  KEY `idx_practicantes_dni` (`dni`),
  CONSTRAINT `fk_practicante_institucion` FOREIGN KEY (`institucion_id`) REFERENCES `instituciones` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `practicantes`
--

LOCK TABLES `practicantes` WRITE;
/*!40000 ALTER TABLE `practicantes` DISABLE KEYS */;
INSERT INTO `practicantes` VALUES (4,'60810589','JHEFERSON JOAN','ESCATE UNZUETA','Ingeniería de Software con Inteligencia Artificial','Jhefersonescate@gmail.com','937687199',NULL,53,16,'2026-07-06','2026-11-27','activo','2026-05-26 21:30:01',NULL),(5,'61072715','ERICSON SALE','MORENO NAVARRO','Ingeniería de Software con Inteligencia Artificial',NULL,NULL,NULL,53,16,'2026-02-16','2026-12-31','activo','2026-06-08 05:20:59','2026-06-08 05:29:37');
/*!40000 ALTER TABLE `practicantes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `reportes`
--

DROP TABLE IF EXISTS `reportes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `reportes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `usuario_id` int(11) DEFAULT NULL,
  `tipo` varchar(50) NOT NULL,
  `fecha_generado` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `fk_reportes_usuario` (`usuario_id`),
  CONSTRAINT `fk_reportes_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `reportes`
--

LOCK TABLES `reportes` WRITE;
/*!40000 ALTER TABLE `reportes` DISABLE KEYS */;
/*!40000 ALTER TABLE `reportes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `usuarios`
--

DROP TABLE IF EXISTS `usuarios`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `usuarios` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `nombres` varchar(100) NOT NULL,
  `email` varchar(120) DEFAULT NULL,
  `rol` enum('admin','supervisor','practicante') NOT NULL DEFAULT 'supervisor',
  `practicante_id` int(11) DEFAULT NULL,
  `reset_token` varchar(64) DEFAULT NULL,
  `reset_expires` datetime DEFAULT NULL,
  `estado` enum('activo','inactivo') DEFAULT 'activo',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email` (`email`),
  UNIQUE KEY `practicante_id` (`practicante_id`),
  KEY `idx_usuarios_username` (`username`),
  KEY `idx_usuarios_email` (`email`),
  CONSTRAINT `fk_usuarios_practicante` FOREIGN KEY (`practicante_id`) REFERENCES `practicantes` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `usuarios`
--

LOCK TABLES `usuarios` WRITE;
/*!40000 ALTER TABLE `usuarios` DISABLE KEYS */;
INSERT INTO `usuarios` VALUES (1,'admin','$2y$10$d5xNMkLWHbTYVMix1BogrevkUrjH.WVkLPLGLzT9iKXStaRX3/mS2','Administrador General','admin@municipalidad.gob.pe','admin',NULL,NULL,NULL,'activo','2026-05-10 05:20:53','2026-06-08 20:33:17'),(4,'60810589','$2y$10$W8YfyXl5EGv/khDatYEoBuGQJTi7QWW216goFUAnJXU9upL4VzjTO','JHEFERSON JOAN ESCATE UNZUETA',NULL,'practicante',4,NULL,NULL,'activo','2026-05-26 21:30:01','2026-06-08 05:03:39'),(5,'61072715','$2y$10$zZiM5OYsZKMn0qagHHGmreMPaISKx5CKKpc2s4d2cib/pUlrI8VcK','ERICSON SALE MORENO NAVARRO',NULL,'practicante',5,NULL,NULL,'activo','2026-06-08 05:20:59','2026-06-08 05:29:37'),(6,'3ric','$2y$10$2KWBLpq9vCSe.uDCG5sH8OLBhK7Vg2EVhgo4kDw418mK5p6wZzvru','Ericson Moreno',NULL,'supervisor',NULL,NULL,NULL,'activo','2026-06-08 05:32:48',NULL);
/*!40000 ALTER TABLE `usuarios` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Final view structure for view `areas`
--

/*!50001 DROP VIEW IF EXISTS `areas`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = cp850 */;
/*!50001 SET character_set_results     = cp850 */;
/*!50001 SET collation_connection      = cp850_general_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`root`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `areas` AS select `digi`.`areas`.`IdAreas` AS `id`,`digi`.`areas`.`Nombre` AS `nombre`,`digi`.`areas`.`Encargado` AS `encargado`,`digi`.`areas`.`Cargo` AS `cargo`,`digi`.`areas`.`Estado` AS `estado` from `digi`.`areas` */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-06-08 22:07:44
