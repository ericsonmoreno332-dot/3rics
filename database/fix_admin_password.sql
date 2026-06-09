-- Base: sistema_practicantes
-- Deja usuario admin con contraseña en texto plano: admin123
-- Ejecutar en phpMyAdmin (pestaña SQL) una sola vez.

USE sistema_practicantes;

UPDATE usuarios
SET password = '$2y$10$d5xNMkLWHbTYVMix1BogrevkUrjH.WVkLPLGLzT9iKXStaRX3/mS2'
WHERE username = 'admin';
