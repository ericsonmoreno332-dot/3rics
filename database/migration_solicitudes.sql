-- Migration: Create solicitudes_salida table
-- Stores exit time requests from practicantes for admin approval

CREATE TABLE IF NOT EXISTS `solicitudes_salida` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `asistencia_id` int(11) NOT NULL,
  `practicante_id` int(11) NOT NULL,
  `hora_propuesta` time NOT NULL,
  `estado` enum('pendiente','aceptada','rechazada') DEFAULT 'pendiente',
  `mensaje_rechazo` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_solicitudes_practicante` (`practicante_id`),
  KEY `idx_solicitudes_asistencia` (`asistencia_id`),
  KEY `idx_solicitudes_estado` (`estado`),
  CONSTRAINT `fk_solicitud_asistencia` FOREIGN KEY (`asistencia_id`) REFERENCES `asistencias` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_solicitud_practicante` FOREIGN KEY (`practicante_id`) REFERENCES `practicantes` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
