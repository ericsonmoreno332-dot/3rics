<?php
set_time_limit(0);

$FTP_HOST = 'ftpupload.net';
$FTP_USER = 'if0_42133482';
$FTP_PASS = '61072715';
$FTP_ROOT = '/htdocs';

echo "Conectando FTP...\n";
$ftp = ftp_connect($FTP_HOST, 21, 30);
if (!$ftp) die("[ERROR] No se pudo conectar\n");
if (!ftp_login($ftp, $FTP_USER, $FTP_PASS)) die("[ERROR] Login fallido\n");
ftp_pasv($ftp, true);
echo "[OK] Conectado!\n\n";

// ── 1. PROXY vendor/autoload.php ────────────────────────────────
// Usa el vendor completo que ya está en /htdocs/3ricso/vendor/
$autoloadProxy = <<<'PHP'
<?php
// Proxy: usa el vendor completo que subimos originalmente
$realVendor = __DIR__ . '/../3ricso/vendor/autoload.php';
if (file_exists($realVendor)) {
    require_once $realVendor;
} else {
    // Fallback: intentar cargar localmente si existe
    $files = glob(__DIR__ . '/*/src/*.php');
}
PHP;

$tmp = tempnam(sys_get_temp_dir(), 'al');
file_put_contents($tmp, $autoloadProxy);
if (ftp_put($ftp, $FTP_ROOT . '/vendor/autoload.php', $tmp, FTP_ASCII)) {
    echo "[OK] vendor/autoload.php (proxy hacia 3ricso/vendor/)\n";
} else {
    echo "[FAIL] vendor/autoload.php\n";
}
unlink($tmp);

// ── 2. .htaccess raíz CORRECTO ──────────────────────────────────
$htaccess = <<<'HTACCESS'
Options -Indexes
RewriteEngine On

# Servir archivos/carpetas que existen directamente
RewriteCond %{REQUEST_FILENAME} -f [OR]
RewriteCond %{REQUEST_FILENAME} -d
RewriteRule ^ - [L]

# Todo lo demás va a public/index.php
RewriteRule ^(.*)$ public/index.php [L,QSA]
HTACCESS;

$tmp = tempnam(sys_get_temp_dir(), 'hta');
file_put_contents($tmp, $htaccess);
if (ftp_put($ftp, $FTP_ROOT . '/.htaccess', $tmp, FTP_ASCII)) {
    echo "[OK] .htaccess raíz corregido\n";
} else {
    echo "[FAIL] .htaccess raíz\n";
}
unlink($tmp);

// ── 3. import_remote.php en public/ ────────────────────────────
$importPhp = <<<'PHP'
<?php
set_time_limit(300);
ini_set('display_errors', 1);
error_reporting(E_ALL);

echo "<pre style='background:#0d1117;color:#3fb950;padding:20px;font-family:monospace;font-size:13px'>\n";
echo "=== IMPORTACIÓN BASE DE DATOS ===\n\n";

$host   = 'sql300.infinityfree.com';
$user   = 'if0_42133482';
$pass   = '61072715';
$dbMain = 'if0_42133482_sistema_practicantes';
$dbDigi = 'if0_42133482_digi';
$sqlDir = dirname(__DIR__) . '/database';

function importSql($host, $user, $pass, $db, $file) {
    echo "→ Importando: " . basename($file) . " en [$db]\n";
    if (!file_exists($file)) { echo "  [SKIP] Archivo no encontrado\n\n"; return; }
    try {
        $pdo = new PDO("mysql:host=$host;port=3306;dbname=$db;charset=utf8mb4", $user, $pass, [
            PDO::ATTR_TIMEOUT  => 30,
            PDO::ATTR_ERRMODE  => PDO::ERRMODE_EXCEPTION,
        ]);
        $sql = file_get_contents($file);
        // Limpiar directivas problemáticas de InfinityFree
        $sql = preg_replace('/\/\*![0-9]+ SET\s+(NAMES|character_set_client|collation_connection)[^\n]*\*\/;?/i', '', $sql);
        $sql = preg_replace('/^SET\s+(NAMES|character_set|collation_connection)[^;]*;/mi', '', $sql);

        $queries = array_filter(array_map('trim', explode(";\n", $sql)));
        $ok = $err = 0;
        foreach ($queries as $q) {
            if (strlen(trim($q)) < 3) continue;
            try { $pdo->exec($q); $ok++; }
            catch (Exception $e) {
                if (strpos($e->getMessage(), 'already exists') === false &&
                    strpos($e->getMessage(), 'Duplicate') === false) {
                    echo "  [WARN] " . substr($e->getMessage(), 0, 100) . "\n";
                }
                $err++;
            }
        }
        echo "  [OK] Ejecutadas: $ok | Advertencias: $err\n\n";
    } catch (Exception $e) {
        echo "  [ERROR] " . $e->getMessage() . "\n\n";
    }
}

importSql($host, $user, $pass, $dbMain, $sqlDir . '/local_sistema_practicantes_adjusted.sql');
importSql($host, $user, $pass, $dbDigi,  $sqlDir . '/local_digi.sql');
importSql($host, $user, $pass, $dbMain, $sqlDir . '/fix_admin_password.sql');

echo "=== IMPORTACIÓN COMPLETA ===\n\n";
echo "➜ <a href='https://3ricasistem.infinityfree.io/public/' style='color:#58a6ff'>Ir al sistema</a>\n";
echo "</pre>";
PHP;

$tmp = tempnam(sys_get_temp_dir(), 'imp');
file_put_contents($tmp, $importPhp);
if (ftp_put($ftp, $FTP_ROOT . '/public/import_db.php', $tmp, FTP_ASCII)) {
    echo "[OK] public/import_db.php subido\n";
} else {
    echo "[FAIL] public/import_db.php\n";
}
unlink($tmp);

// ── 4. info.php para verificar PHP ──────────────────────────────
$infoPhp = <<<'PHP'
<?php
echo "<pre style='background:#0d1117;color:#3fb950;padding:20px;font-family:monospace'>\n";
echo "PHP VERSION: " . PHP_VERSION . "\n";
echo "DIR: " . __DIR__ . "\n\n";

$root = dirname(__DIR__);
$checks = [
    'vendor/autoload.php',
    '3ricso/vendor/autoload.php',
    'includes/bootstrap.php',
    '.env',
];
foreach ($checks as $c) {
    echo (file_exists($root . '/' . $c) ? '[OK]' : '[NO]') . " $c\n";
}

echo "\n--- EXTENSIONES ---\n";
foreach (['pdo_mysql','mysqli','mbstring','gd','zip'] as $e) {
    echo (extension_loaded($e) ? '[OK]' : '[NO]') . " $e\n";
}

echo "\n--- CONEXION BD ---\n";
try {
    $p = new PDO('mysql:host=sql300.infinityfree.com;port=3306;dbname=if0_42133482_sistema_practicantes', 'if0_42133482', '61072715', [PDO::ATTR_TIMEOUT=>8,PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION]);
    echo "[OK] MySQL conectado!\n";
    echo "Tablas: " . implode(', ', $p->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN)) . "\n";
} catch(Exception $e) { echo "[ERROR] " . $e->getMessage() . "\n"; }
echo "</pre>";
PHP;

$tmp = tempnam(sys_get_temp_dir(), 'inf');
file_put_contents($tmp, $infoPhp);
if (ftp_put($ftp, $FTP_ROOT . '/public/info.php', $tmp, FTP_ASCII)) {
    echo "[OK] public/info.php subido\n";
} else {
    echo "[FAIL] public/info.php\n";
}
unlink($tmp);

ftp_close($ftp);

echo "\n=== LISTO ===\n";
echo "1. Info:     https://3ricasistem.infinityfree.io/public/info.php\n";
echo "2. Importar: https://3ricasistem.infinityfree.io/public/import_db.php\n";
echo "3. Sistema:  https://3ricasistem.infinityfree.io/public/\n";
