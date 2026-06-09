# REGIS — Control de asistencia (practicantes)

Aplicación PHP (sesiones + CSRF) con panel administrativo, practicantes, asistencia (manual, DNI, QR, geolocalización opcional), dashboard con gráficos y reportes.

## Requisitos

- PHP 8.1+ con extensiones: `pdo_mysql`, `mysqli`, `gd`, `mbstring`, `zip` (últimas dos importantes para PDF / Excel / QR local).
- MySQL 5.7+ / MariaDB
- Apache con `mod_rewrite` opcional, o servir la carpeta `public/`
- [Composer](https://getcomposer.org/) (recomendado) para autoload y dependencias

## Instalación

1. Clona o copia el proyecto y entra a la carpeta.
2. `composer install` (o `php composer.phar install`) — genera/actualiza `vendor/`.
3. Crea la base con tu script SQL y **después** ejecuta `database/migration_extend.sql`.
4. Copia `.env.example` a `.env` y ajusta `DB_*` y `APP_URL` (debe apuntar a `public`, p. ej. `http://localhost/REGIS/public`).
5. Credenciales iniciales: ejecuta en MySQL `database/fix_admin_password.sql`, luego entra como **usuario `admin`** / **contraseña `admin123`** y cámbiala en producción.
   Alternativa: `php scripts/hash_password.php TuClave` y `UPDATE usuarios SET password = '...' WHERE username = 'admin';`
6. Abre en el navegador: `.../public/index.php?r=login`

## Reportes y QR

- **PDF**: se intenta mPDF; si no hay `gd`/`zip` o falla la librería, se ofrece HTML descargable para imprimir a PDF.
- **Excel**: se intenta `.xlsx` (PhpSpreadsheet); si falla, se descarga **CSV** (compatible con Excel).
- **Código QR**: con `gd` y Endroid se genera PNG en el servidor; si no, se redirige al servicio configurado en `QR_IMAGE_SERVICE`.

## Estructura

- `public/index.php` — enrutador `?r=nombre_ruta`
- `routes/*.php` — pantallas y acciones
- `includes/` — bootstrap, BD, reglas de negocio, exportaciones
- `storage/tmp` — temporales mPDF (debe ser escribible)
# 3rics
