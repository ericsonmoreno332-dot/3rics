<?php
set_time_limit(0);

$FTP_HOST = 'ftpupload.net';
$FTP_USER = 'if0_42133482';
$FTP_PASS = '61072715';
$FTP_ROOT = '/htdocs';

// ── Script de diagnóstico ────────────────────────────────────────
$debugPhp = <<<'PHP'
<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<pre style='background:#111;color:#0f0;padding:20px;font-size:13px'>";
echo "=== DIAGNÓSTICO DEL SERVIDOR ===\n\n";

// PHP Version
echo "PHP Version: " . PHP_VERSION . "\n";
echo "Directorio actual: " . __DIR__ . "\n\n";

// Verificar extensiones
$exts = ['pdo', 'pdo_mysql', 'mysqli', 'mbstring', 'json', 'openssl', 'gd', 'zip'];
echo "--- Extensiones PHP ---\n";
foreach ($exts as $ext) {
    echo ($ext) . ": " . (extension_loaded($ext) ? '[OK]' : '[FALTA]') . "\n";
}

// Verificar .env
echo "\n--- Archivo .env ---\n";
$envPath = dirname(__DIR__) . '/.env';
echo "Ruta: $envPath\n";
echo "Existe: " . (file_exists($envPath) ? 'SI' : 'NO') . "\n";
if (file_exists($envPath)) {
    $lines = file($envPath);
    foreach ($lines as $line) {
        // Ocultar contraseña
        if (strpos($line, 'PASS') !== false) {
            echo "DB_PASS=***\n";
        } else {
            echo trim($line) . "\n";
        }
    }
}

// Verificar vendor/autoload.php
echo "\n--- Autoload ---\n";
$autoload = dirname(__DIR__) . '/vendor/autoload.php';
echo "vendor/autoload.php: " . (file_exists($autoload) ? '[OK]' : '[FALTA]') . "\n";

// Verificar conexión BD
echo "\n--- Conexión MySQL ---\n";
$host = 'sql300.infinityfree.com';
$user = 'if0_42133482';
$pass = '61072715';
$db   = 'if0_42133482_sistema_practicantes';
try {
    $pdo = new PDO("mysql:host=$host;port=3306;dbname=$db;charset=utf8mb4", $user, $pass, [
        PDO::ATTR_TIMEOUT => 10,
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ]);
    echo "Conexión MySQL: [OK]\n";
    $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    echo "Tablas encontradas: " . count($tables) . "\n";
    foreach ($tables as $t) echo "  - $t\n";
} catch (Exception $e) {
    echo "Conexión MySQL: [ERROR] " . $e->getMessage() . "\n";
}

echo "\n=== FIN DIAGNÓSTICO ===</pre>";
PHP;

// ── import_remote.php (en public/) ──────────────────────────────
$importPhp = <<<'PHP'
<?php
set_time_limit(300);
ini_set('display_errors', 1);
error_reporting(E_ALL);

echo "<pre style='background:#111;color:#0f0;padding:20px;font-size:13px'>\n";
echo "=== IMPORTACIÓN REMOTA ===\n\n";

$host = 'sql300.infinityfree.com';
$user = 'if0_42133482';
$pass = '61072715';
$dbMain = 'if0_42133482_sistema_practicantes';
$dbDigi = 'if0_42133482_digi';
$sqlDir = dirname(__DIR__) . '/database';

function runSqlFile($host, $user, $pass, $dbName, $file) {
    echo "Importando " . basename($file) . " → BD: $dbName\n";
    if (!file_exists($file)) {
        echo "   [ERROR] Archivo no encontrado: $file\n\n";
        return;
    }
    try {
        $pdo = new PDO("mysql:host=$host;port=3306;dbname=$dbName;charset=utf8mb4", $user, $pass, [
            PDO::ATTR_TIMEOUT => 30,
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        ]);
        $sql = file_get_contents($file);
        // Limpiar líneas problemáticas
        $sql = preg_replace('/\/\*![0-9]+ SET\s+(?:NAMES|character_set_client|character_set_results|collation_connection)[^\n]*\*\/;/i', '', $sql);
        $sql = preg_replace('/^SET\s+(?:NAMES|character_set|collation_connection)[^\n;]*;?\s*$/mi', '', $sql);
        $sql = preg_replace('/^START TRANSACTION;/mi', '', $sql);
        $sql = preg_replace('/^COMMIT;/mi', '', $sql);

        // Ejecutar sentencia por sentencia
        $queries = array_filter(array_map('trim', explode(";\n", $sql)));
        $ok = 0; $err = 0;
        foreach ($queries as $q) {
            if (empty($q)) continue;
            try {
                $pdo->exec($q);
                $ok++;
            } catch (Exception $e) {
                $err++;
                // Ignorar errores de tabla ya existente
                if (strpos($e->getMessage(), 'already exists') === false) {
                    echo "   [WARN] " . substr($e->getMessage(), 0, 120) . "\n";
                }
            }
        }
        echo "   [OK] Ejecutadas: $ok | Errores: $err\n\n";
    } catch (Exception $e) {
        echo "   [ERROR CONEXIÓN] " . $e->getMessage() . "\n\n";
    }
}

runSqlFile($host, $user, $pass, $dbMain, $sqlDir . '/local_sistema_practicantes_adjusted.sql');
runSqlFile($host, $user, $pass, $dbDigi,  $sqlDir . '/local_digi.sql');
runSqlFile($host, $user, $pass, $dbMain, $sqlDir . '/fix_admin_password.sql');

echo "=== IMPORTACIÓN COMPLETA ===\n";
echo "Ahora visita: <a href='https://3ricasistem.infinityfree.io/public/' style='color:cyan'>https://3ricasistem.infinityfree.io/public/</a>\n";
echo "</pre>";
PHP;

// ── Guardar temporales ───────────────────────────────────────────
$tmpDebug  = sys_get_temp_dir() . '/debug_prod.php';
$tmpImport = sys_get_temp_dir() . '/import_prod.php';
file_put_contents($tmpDebug,  $debugPhp);
file_put_contents($tmpImport, $importPhp);

// ── FTP ─────────────────────────────────────────────────────────
echo "Conectando FTP...\n";
$ftp = ftp_connect($FTP_HOST, 21, 30);
if (!$ftp) die("[ERROR] No se pudo conectar\n");
if (!ftp_login($ftp, $FTP_USER, $FTP_PASS)) die("[ERROR] Login fallido\n");
ftp_pasv($ftp, true);
echo "[OK] Conectado!\n";

// Listar contenido de htdocs para ver estructura real
echo "\nContenido de /htdocs:\n";
$list = ftp_nlist($ftp, $FTP_ROOT);
if ($list) {
    foreach ($list as $item) echo "  $item\n";
} else {
    echo "  (vacío o error)\n";
}

// Crear carpeta public si no existe
@ftp_mkdir($ftp, $FTP_ROOT . '/public');

// Subir debug.php a public/
if (ftp_put($ftp, $FTP_ROOT . '/public/debug.php', $tmpDebug, FTP_ASCII)) {
    echo "[OK] public/debug.php subido\n";
} else {
    echo "[FAIL] No se pudo subir debug.php\n";
}

// Subir import_remote.php a public/
if (ftp_put($ftp, $FTP_ROOT . '/public/import_remote.php', $tmpImport, FTP_ASCII)) {
    echo "[OK] public/import_remote.php subido\n";
} else {
    echo "[FAIL] No se pudo subir import_remote.php\n";
}

ftp_close($ftp);

echo "\n=== LISTO ===\n";
echo "1. Diagnóstico: https://3ricasistem.infinityfree.io/public/debug.php\n";
echo "2. Importar BD: https://3ricasistem.infinityfree.io/public/import_remote.php\n";
