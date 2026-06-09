<?php
set_time_limit(0);

$FTP_HOST = 'ftpupload.net';
$FTP_USER = 'if0_42133482';
$FTP_PASS = '61072715';
$FTP_ROOT = '/htdocs';

// ── 1. Contenido del .env de producción ─────────────────────────
$envContent = 'DB_HOST=sql300.infinityfree.com
DB_PORT=3306
DB_NAME=if0_42133482_sistema_practicantes
DB_USER=if0_42133482
DB_PASS=61072715

APP_URL=https://3ricasistem.infinityfree.io

QR_IMAGE_SERVICE=https://api.qrserver.com/v1/create-qr-code/?size=280x280&data=
TIMEZONE=America/Lima

TARDANZA_HORA=08:00:00

UPLOAD_MAX_MB=2

DNI_API_URL=https://app.apiinti.dev/api/v1/dni/
DNI_API_TOKEN=inti_live_1098c790d16d0a2e41c5f75ad6b0d2be
';

// ── 2. Contenido del import_remote.php ──────────────────────────
$importContent = <<<'PHP'
<?php
set_time_limit(300);
ini_set('display_errors', 1);

$host = 'sql300.infinityfree.com';
$user = 'if0_42133482';
$pass = '61072715';
$dbMain = 'if0_42133482_sistema_practicantes';
$dbDigi = 'if0_42133482_digi';

echo "<pre>\n=== IMPORTACIÓN REMOTA ===\n\n";

$sqlDir = __DIR__ . '/database';

function runSql($host, $user, $pass, $dbName, $file) {
    echo "Importando $file en BD $dbName...\n";
    $conn = new mysqli($host, $user, $pass, $dbName);
    if ($conn->connect_error) {
        echo "   [ERROR] Conexión: " . $conn->connect_error . "\n";
        return false;
    }
    $conn->set_charset('utf8mb4');
    $sql = file_get_contents($file);
    // Quitar SET NAMES / SET collation lines que puedan fallar
    $sql = preg_replace('/\/\*.*?\*\/;?\s*/s', '', $sql);
    $sql = preg_replace('/^SET\s+(?:NAMES|character_set|collation_connection)[^\n;]*;?\s*/mi', '', $sql);

    if ($conn->multi_query($sql)) {
        do { if ($conn->store_result()) {} } while ($conn->more_results() && $conn->next_result());
    }
    if ($conn->error) {
        echo "   [WARN] " . $conn->error . "\n";
    } else {
        echo "   [OK]\n";
    }
    $conn->close();
    return true;
}

// Importar sistema_practicantes (ajustado)
$f1 = $sqlDir . '/local_sistema_practicantes_adjusted.sql';
if (file_exists($f1)) {
    runSql($host, $user, $pass, $dbMain, $f1);
} else {
    echo "[ERROR] No se encuentra: $f1\n";
}

// Importar digi
$f2 = $sqlDir . '/local_digi.sql';
if (file_exists($f2)) {
    runSql($host, $user, $pass, $dbDigi, $f2);
} else {
    echo "[WARN] No se encuentra: $f2\n";
}

// Fix admin password
$f3 = $sqlDir . '/fix_admin_password.sql';
if (file_exists($f3)) {
    runSql($host, $user, $pass, $dbMain, $f3);
}

echo "\n=== LISTO. Ahora visita https://3ricasistem.infinityfree.io/public/ ===\n</pre>";
PHP;

// ── 3. Guardar archivos temporales ──────────────────────────────
$tmpEnv    = sys_get_temp_dir() . '/prod_env.txt';
$tmpImport = sys_get_temp_dir() . '/import_remote_prod.php';

file_put_contents($tmpEnv, $envContent);
file_put_contents($tmpImport, $importContent);

// ── 4. Conectar FTP y subir ──────────────────────────────────────
echo "Conectando FTP...\n";
$ftp = ftp_connect($FTP_HOST, 21, 30);
if (!$ftp) die("[ERROR] No se pudo conectar\n");
if (!ftp_login($ftp, $FTP_USER, $FTP_PASS)) die("[ERROR] Login fallido\n");
ftp_pasv($ftp, true);
echo "[OK] Conectado!\n";

// Subir .env
if (ftp_put($ftp, $FTP_ROOT . '/.env', $tmpEnv, FTP_ASCII)) {
    echo "[OK] .env subido con credenciales de producción\n";
} else {
    echo "[FAIL] No se pudo subir .env\n";
}

// Subir import_remote.php
if (ftp_put($ftp, $FTP_ROOT . '/import_remote.php', $tmpImport, FTP_ASCII)) {
    echo "[OK] import_remote.php subido\n";
} else {
    echo "[FAIL] No se pudo subir import_remote.php\n";
}

ftp_close($ftp);

echo "\n=== TODO LISTO ===\n";
echo "Ahora abre: https://3ricasistem.infinityfree.io/import_remote.php\n";
