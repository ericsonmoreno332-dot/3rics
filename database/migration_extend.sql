-- Ejecutar después del CREATE inicial (USE sistema_practicantes;)
-- Extiende el esquema para el sistema web completo

ALTER TABLE practicantes
  ADD COLUMN foto VARCHAR(255) NULL AFTER telefono;

ALTER TABLE usuarios
  MODIFY COLUMN rol ENUM('admin','supervisor','practicante') NOT NULL DEFAULT 'supervisor',
  ADD COLUMN practicante_id INT NULL UNIQUE AFTER rol,
  ADD COLUMN email VARCHAR(120) NULL AFTER password,
  ADD COLUMN reset_token VARCHAR(64) NULL,
  ADD COLUMN reset_expires DATETIME NULL,
  ADD CONSTRAINT fk_usuarios_practicante
    FOREIGN KEY (practicante_id) REFERENCES practicantes(id) ON DELETE SET NULL;

ALTER TABLE asistencias
  ADD COLUMN lat_entrada DECIMAL(10,8) NULL AFTER observacion,
  ADD COLUMN lng_entrada DECIMAL(11,8) NULL AFTER lat_entrada,
  ADD COLUMN lat_salida DECIMAL(10,8) NULL,
  ADD COLUMN lng_salida DECIMAL(11,8) NULL,
  ADD COLUMN metodo_entrada ENUM('manual','qr','dni','geo') NULL DEFAULT 'manual' AFTER estado,
  ADD COLUMN metodo_salida ENUM('manual','qr','dni','geo') NULL DEFAULT 'manual';

-- Índices útiles para reportes y búsquedas
CREATE INDEX idx_asistencias_fecha ON asistencias(fecha);
CREATE INDEX idx_asistencias_pract_fecha ON asistencias(practicante_id, fecha);
CREATE INDEX idx_practicantes_area ON practicantes(area_id);
CREATE INDEX idx_practicantes_inst ON practicantes(institucion_id);

-- Crear vista para conectar con la tabla de áreas de la base de datos digi
CREATE OR REPLACE VIEW areas AS
SELECT 
    IdAreas AS id,
    Nombre AS nombre,
    Encargado AS encargado,
    Cargo AS cargo,
    Estado AS estado
FROM digi.areas;

