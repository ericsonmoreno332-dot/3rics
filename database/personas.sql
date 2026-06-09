CREATE TABLE personas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    dni CHAR(8) UNIQUE NOT NULL,
    nombres VARCHAR(100),
    apellido_paterno VARCHAR(100),
    apellido_materno VARCHAR(100),
    fecha_consulta TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
