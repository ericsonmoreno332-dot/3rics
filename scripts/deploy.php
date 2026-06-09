<?php

declare(strict_types=1);

// Credentials
$db_host = 'sql300.infinityfree.com';
$db_user = 'if0_42133482';
$db_pass = '61072715';
$db_name_digi = 'if0_42133482_digi';
$db_name_main = 'if0_42133482_sistema_practicantes';

$ftp_host = 'ftpupload.net';
$ftp_user = 'if0_42133482';
$ftp_pass = '61072715';
$ftp_dir  = '/3ricasistem.infinityfree.io/htdocs';

echo "=== INICIANDO DEPLOY AUTOMÁTICO ===\n\n";

// ==========================================
// 1. PREPARACIÓN DE SQL AJUSTADO
// ==========================================
echo "1. Preparando base de datos ajustada para producción...\n";

$sql_main_path = __DIR__ . '/../database/local_sistema_practicantes.sql';
if (!file_exists($sql_main_path)) {
    echo "[ERROR] No se encuentra database/local_sistema_practicantes.sql. Por favor expórtala primero.\n";
    exit(1);
}

$sql_main = file_get_contents($sql_main_path);
echo "   -> Ajustando referencias a 'digi.areas' para usar '{$db_name_digi}.areas'...\n";
$sql_main = str_replace('`digi`.`areas`', "`{$db_name_digi}`.`areas`", $sql_main);
$sql_main = str_replace('digi.areas', "{$db_name_digi}.areas", $sql_main);

$adjusted_sql_path = __DIR__ . '/../database/local_sistema_practicantes_adjusted.sql';
file_put_contents($adjusted_sql_path, $sql_main);
echo "   [OK] Archivo SQL ajustado creado en database/local_sistema_practicantes_adjusted.sql\n";


// ==========================================
// 2. CREACIÓN DE ARCHIVOS DE CONFIGURACIÓN
// ==========================================
echo "\n2. Preparando archivos de configuración para producción...\n";

// .env producción
$prod_env = <<<ENV
DB_HOST={$db_host}
DB_PORT=3306
DB_NAME={$db_name_main}
DB_USER={$db_user}
DB_PASS={$db_pass}

APP_URL=https://3ricasistem.infinityfree.io
TIMEZONE=America/Lima

QR_IMAGE_SERVICE=https://api.qrserver.com/v1/create-qr-code/?size=280x280&data=
TARDANZA_HORA=08:15:00
UPLOAD_MAX_MB=2

DNI_API_URL=https://app.apiinti.dev/api/v1/dni/
DNI_API_TOKEN=inti_live_1098c790d16d0a2e41c5f75ad6b0d2be
ENV;

$env_temp_file = __DIR__ . '/../temp_prod.env';
file_put_contents($env_temp_file, $prod_env);
echo "   -> Generado archivo temporal .env de producción.\n";

// .htaccess raíz para redireccionar a public
$root_htaccess = <<<HTACCESS
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteRule ^$ public/index.php [L]
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteRule ^(.*)$ public/$1 [L]
</IfModule>
HTACCESS;

$htaccess_temp_file = __DIR__ . '/../temp_root.htaccess';
file_put_contents($htaccess_temp_file, $root_htaccess);
echo "   -> Generado archivo temporal .htaccess de redirección.\n";

// import_remote.php para ejecutar base de datos en el servidor (evita bloqueo de conexión remota de MySQL)
$import_script = <<<PHP
<?php
declare(strict_types=1);

error_reporting(E_ALL);
ini_set('display_errors', '1');

header('Content-Type: text/plain; charset=utf-8');

\$db_host = '{$db_host}';
\$db_user = '{$db_user}';
\$db_pass = '{$db_pass}';
\$db_name_digi = '{$db_name_digi}';
\$db_name_main = '{$db_name_main}';

echo "=== IMPORTACIÓN REMOTA DE BASE DE DATOS ===\\n\\n";

try {
    echo "1. Conectando a la base de datos 'digi' (\$db_name_digi)...\\n";
    \$pdo_digi = new PDO("mysql:host=\$db_host;dbname=\$db_name_digi;charset=utf8mb4", \$db_user, \$db_pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
    
    \$file_digi = __DIR__ . '/database/local_digi.sql';
    if (!file_exists(\$file_digi)) {
        throw new Exception("No existe el archivo: database/local_digi.sql");
    }
    
    echo "   -> Ejecutando local_digi.sql...\\n";
    \$sql_digi = file_get_contents(\$file_digi);
    \$pdo_digi->exec(\$sql_digi);
    echo "   [OK] Base de datos 'digi' importada con éxito.\\n\\n";
    
} catch (Exception \$e) {
    echo "   [ERROR] " . \$e->getMessage() . "\\n\\n";
}

try {
    echo "2. Conectando a la base de datos principal (\$db_name_main)...\\n";
    \$pdo_main = new PDO("mysql:host=\$db_host;dbname=\$db_name_main;charset=utf8mb4", \$db_user, \$db_pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
    
    \$file_main = __DIR__ . '/database/local_sistema_practicantes_adjusted.sql';
    if (!file_exists(\$file_main)) {
        throw new Exception("No existe el archivo: database/local_sistema_practicantes_adjusted.sql");
    }
    
    echo "   -> Ejecutando local_sistema_practicantes_adjusted.sql...\\n";
    \$sql_main = file_get_contents(\$file_main);
    \$pdo_main->exec(\$sql_main);
    echo "   [OK] Base de datos principal importada con éxito.\\n\\n";
    
} catch (Exception \$e) {
    echo "   [ERROR] " . \$e->getMessage() . "\\n\\n";
}

echo "3. Limpiando archivos temporales...\\n";
@unlink(__DIR__ . '/database/local_digi.sql');
@unlink(__DIR__ . '/database/local_sistema_practicantes_adjusted.sql');
echo "   [OK] Archivos SQL temporales eliminados del servidor.\\n\\n";

echo "4. Autodestruyendo script de importación...\\n";
@unlink(__FILE__);
echo "   [OK] Script import_remote.php eliminado.\\n\\n";

echo "=== PROCESO COMPLETADO CON EXITO ===";
PHP;

$import_temp_file = __DIR__ . '/../temp_import.php';
file_put_contents($import_temp_file, $import_script);
echo "   -> Generado archivo temporal de importación remota.\n";


// ==========================================
// 3. CONEXIÓN Y SUBIDA FTP
// ==========================================
echo "\n3. Conectando al servidor FTP {$ftp_host}...\n";

$ftp_conn = ftp_connect($ftp_host);
if (!$ftp_conn) {
    echo "   [ERROR] No se pudo conectar al servidor FTP.\n";
    cleanup_temp_files();
    exit(1);
}

$ftp_login = ftp_login($ftp_conn, $ftp_user, $ftp_pass);
if (!$ftp_login) {
    echo "   [ERROR] Credenciales FTP incorrectas.\n";
    ftp_close($ftp_conn);
    cleanup_temp_files();
    exit(1);
}

ftp_pasv($ftp_conn, true);
echo "   [OK] Conectado y autenticado vía FTP con éxito.\n";

// Función para asegurar directorios FTP
function ftp_ensure_dir($ftp, $base_dir, $relative_path) {
    $parts = explode('/', trim($relative_path, '/'));
    $current = $base_dir;
    
    if (!@ftp_chdir($ftp, $current)) {
        return false;
    }
    
    foreach ($parts as $part) {
        if ($part === '') continue;
        $current .= '/' . $part;
        if (!@ftp_chdir($ftp, $current)) {
            if (!@ftp_mkdir($ftp, $current)) {
                return false;
            }
            @ftp_chdir($ftp, $current);
        }
    }
    return true;
}

// Asegurar que el directorio raíz de la app existe en el FTP
echo "   -> Comprobando y accediendo al directorio raíz de la app en FTP...\n";
if (!ftp_ensure_dir($ftp_conn, $ftp_dir, '')) {
    echo "   [ERROR] No se pudo crear o acceder al directorio raíz {$ftp_dir} en el FTP.\n";
    ftp_close($ftp_conn);
    cleanup_temp_files();
    exit(1);
}

// Función recursiva para subir archivos
function upload_directory($ftp, $local_path, $remote_path, $ftp_dir, $ignored_patterns = []) {
    $dir = opendir($local_path);
    if (!$dir) return;
    
    // Asegurar directorio remoto antes de subir contenido
    if ($remote_path !== '') {
        ftp_ensure_dir($ftp, $ftp_dir, $remote_path);
    }
    
    // Ir al directorio correspondiente
    ftp_chdir($ftp, $ftp_dir . ($remote_path === '' ? '' : '/' . $remote_path));
    
    while (($file = readdir($dir)) !== false) {
        if ($file === '.' || $file === '..') continue;
        
        $local_file = $local_path . '/' . $file;
        $remote_file = ($remote_path === '' ? '' : $remote_path . '/') . $file;
        
        // Comprobar ignorados
        $is_ignored = false;
        foreach ($ignored_patterns as $pattern) {
            if (strpos($local_file, $pattern) !== false) {
                // Excepción: queremos subir los archivos SQL temporales requeridos
                if (
                    strpos($local_file, 'local_digi.sql') !== false || 
                    strpos($local_file, 'local_sistema_practicantes_adjusted.sql') !== false
                ) {
                    continue;
                }
                $is_ignored = true;
                break;
            }
        }
        if ($is_ignored) continue;
        
        if (is_dir($local_file)) {
            upload_directory($ftp, $local_file, $remote_file, $ftp_dir, $ignored_patterns);
        } else {
            // Subir archivo
            $upload_success = false;
            $retries = 3;
            while ($retries > 0 && !$upload_success) {
                // Renombrar los archivos temporales en la subida
                $dest_name = $remote_file;
                if ($file === 'temp_prod.env') {
                    $dest_name = ($remote_path === '' ? '' : $remote_path . '/') . '.env';
                } elseif ($file === 'temp_root.htaccess') {
                    $dest_name = ($remote_path === '' ? '' : $remote_path . '/') . '.htaccess';
                } elseif ($file === 'temp_import.php') {
                    $dest_name = ($remote_path === '' ? '' : $remote_path . '/') . 'import_remote.php';
                }
                
                // Ir al directorio correcto de subida
                ftp_chdir($ftp, $ftp_dir . ($remote_path === '' ? '' : '/' . $remote_path));
                
                if (@ftp_put($ftp, basename($dest_name), $local_file, FTP_BINARY)) {
                    echo "      [UP] Subido: " . ($remote_path === '' ? '' : $remote_path . '/') . basename($dest_name) . "\n";
                    $upload_success = true;
                } else {
                    $retries--;
                    echo "      [REINTENTANDO] Error al subir {$local_file}. Reintentos: {$retries}\n";
                    sleep(1);
                }
            }
            if (!$upload_success) {
                echo "      [ERROR CRÍTICO] No se pudo subir: {$local_file}\n";
            }
        }
    }
    closedir($dir);
}

// Patrones de archivos y carpetas a ignorar
$ignored = [
    '/.git',
    '/.vscode',
    '/.claude',
    '/database/local_digi.sql',
    '/database/local_sistema_practicantes.sql',
    '/database/local_sistema_practicantes_adjusted.sql',
    '/scripts/deploy.php',
    '/composer.phar',
    '/.env',
    '/.env.example',
    '/temp_prod.env',
    '/temp_root.htaccess',
    '/temp_import.php'
];

echo "   -> Subiendo archivos base temporales (.env, .htaccess, import_remote.php)...\n";
ftp_chdir($ftp_conn, $ftp_dir);
@ftp_put($ftp_conn, '.htaccess', $htaccess_temp_file, FTP_BINARY);
@ftp_put($ftp_conn, '.env', $env_temp_file, FTP_BINARY);
@ftp_put($ftp_conn, 'import_remote.php', $import_temp_file, FTP_BINARY);

// Subir los SQL para importación remota
echo "   -> Subiendo bases de datos SQL a la carpeta remota 'database'...\n";
ftp_ensure_dir($ftp_conn, $ftp_dir, 'database');
ftp_chdir($ftp_conn, $ftp_dir . '/database');
@ftp_put($ftp_conn, 'local_digi.sql', __DIR__ . '/../database/local_digi.sql', FTP_BINARY);
@ftp_put($ftp_conn, 'local_sistema_practicantes_adjusted.sql', $adjusted_sql_path, FTP_BINARY);

echo "   -> Subiendo resto del código recursivamente (esto tomará unos minutos por la carpeta 'vendor')...\n";
upload_directory($ftp_conn, realpath(__DIR__ . '/..'), '', $ftp_dir, $ignored);

ftp_close($ftp_conn);
cleanup_temp_files();

echo "\n=== ARCHIVOS SUBIDOS CON ÉXITO ===\n";
echo "========================================================================\n";
echo "⚠️  ¡ÚLTIMO PASO OBLIGATORIO! ⚠️\n";
echo "Para importar la base de datos y activar el sistema, debes ingresar a:\n";
echo "👉 https://3ricasistem.infinityfree.io/import_remote.php 👈\n";
echo "en tu navegador de Internet.\n";
echo "========================================================================\n\n";

function cleanup_temp_files() {
    @unlink(__DIR__ . '/../temp_prod.env');
    @unlink(__DIR__ . '/../temp_root.htaccess');
    @unlink(__DIR__ . '/../temp_import.php');
    @unlink(__DIR__ . '/../database/local_sistema_practicantes_adjusted.sql');
}
