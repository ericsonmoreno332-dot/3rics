<?php
// ================================================================
// SCRIPT DE REUBICACIÓN - Sube todo a /htdocs/ correctamente
// ================================================================
set_time_limit(0);
ini_set('display_errors', 1);

$FTP_HOST  = 'ftpupload.net';
$FTP_USER  = 'if0_42133482';
$FTP_PASS  = '61072715';
$FTP_ROOT  = '/htdocs';          // Raíz real del hosting
$LOCAL_ROOT = __DIR__;           // c:\Users\salei\Desktop\3ricso

function ftpMkdirRecursive($ftp, $path) {
    $parts = explode('/', trim($path, '/'));
    $current = '';
    foreach ($parts as $part) {
        if (empty($part)) continue;
        $current .= '/' . $part;
        @ftp_mkdir($ftp, $current);
    }
}

function uploadFile($ftp, $localPath, $remotePath, $retries = 3) {
    $remoteDir = dirname($remotePath);
    ftpMkdirRecursive($ftp, $remoteDir);
    for ($i = $retries; $i >= 0; $i--) {
        $mode = (preg_match('/\.(png|jpg|jpeg|gif|ico|pdf|xlsx|xls|zip)$/i', $remotePath)) ? FTP_BINARY : FTP_ASCII;
        if (@ftp_put($ftp, $remotePath, $localPath, $mode)) return true;
        if ($i > 0) sleep(1);
    }
    return false;
}

function getAllFiles($dir, $base = '') {
    $result = [];
    $items = scandir($dir);
    foreach ($items as $item) {
        if ($item === '.' || $item === '..') continue;
        $fullPath = $dir . DIRECTORY_SEPARATOR . $item;
        $relPath  = $base ? $base . '/' . $item : $item;
        if (is_dir($fullPath)) {
            $result = array_merge($result, getAllFiles($fullPath, $relPath));
        } else {
            $result[] = ['local' => $fullPath, 'rel' => $relPath];
        }
    }
    return $result;
}

echo "=== SUBIDA COMPLETA A RUTA CORRECTA ===\n\n";

// Conectar FTP
$ftp = ftp_connect($FTP_HOST, 21, 30);
if (!$ftp) die("[ERROR] No se pudo conectar al FTP\n");
if (!ftp_login($ftp, $FTP_USER, $FTP_PASS)) die("[ERROR] Login fallido\n");
ftp_pasv($ftp, true);
ftp_set_option($ftp, FTP_TIMEOUT_SEC, 60);
echo "[OK] Conectado a FTP!\n\n";

// ── Crear .env de producción ─────────────────────────────────────
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

// Subir .env a raíz de htdocs
$tmpEnv = tempnam(sys_get_temp_dir(), 'env');
file_put_contents($tmpEnv, $envContent);
if (ftp_put($ftp, $FTP_ROOT . '/.env', $tmpEnv, FTP_ASCII)) {
    echo "[OK] .env → /htdocs/.env\n";
} else {
    echo "[FAIL] .env\n";
}
unlink($tmpEnv);

// ── Carpetas a subir ─────────────────────────────────────────────
$folders = ['includes', 'routes', 'views', 'public', 'database'];
// vendor solo las carpetas esenciales (no tests ni docs)
$vendorEssential = [
    'vendor/autoload.php',
    'vendor/composer',
    'vendor/mpdf/mpdf/src',
    'vendor/mpdf/mpdf/data',
    'vendor/phpoffice/phpspreadsheet/src',
    'vendor/setasign/fpdf',
    'vendor/setasign/fpdi/src',
    'vendor/symfony/polyfill-ctype',
    'vendor/symfony/polyfill-mbstring',
    'vendor/symfony/polyfill-php80',
    'vendor/vlucas/phpdotenv/src',
    'vendor/psr',
    'vendor/phpoption',
    'vendor/graham-campbell',
];

$ok = 0; $fail = 0;

// Subir carpetas principales
foreach ($folders as $folder) {
    $localDir = $LOCAL_ROOT . DIRECTORY_SEPARATOR . $folder;
    if (!is_dir($localDir)) {
        echo "[SKIP] $folder (no existe localmente)\n";
        continue;
    }
    echo "\nSubiendo $folder/...\n";
    $files = getAllFiles($localDir, $folder);
    foreach ($files as $f) {
        $remotePath = $FTP_ROOT . '/' . str_replace('\\', '/', $f['rel']);
        if (uploadFile($ftp, $f['local'], $remotePath)) {
            echo "  [OK] " . $f['rel'] . "\n";
            $ok++;
        } else {
            echo "  [FAIL] " . $f['rel'] . "\n";
            $fail++;
        }
    }
}

// Subir vendor esencial
echo "\nSubiendo vendor esencial...\n";
// autoload.php raíz
$autoloadLocal = $LOCAL_ROOT . '/vendor/autoload.php';
if (file_exists($autoloadLocal)) {
    if (uploadFile($ftp, $autoloadLocal, $FTP_ROOT . '/vendor/autoload.php')) {
        echo "  [OK] vendor/autoload.php\n"; $ok++;
    }
}

foreach ($vendorEssential as $vpath) {
    $localPath = $LOCAL_ROOT . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $vpath);
    if (is_file($localPath)) {
        $remotePath = $FTP_ROOT . '/' . $vpath;
        if (uploadFile($ftp, $localPath, $remotePath)) {
            echo "  [OK] $vpath\n"; $ok++;
        } else {
            echo "  [FAIL] $vpath\n"; $fail++;
        }
    } elseif (is_dir($localPath)) {
        $files = getAllFiles($localPath, $vpath);
        foreach ($files as $f) {
            // Saltar tests y docs
            if (preg_match('/(\/tests?\/|\/docs?\/|\/samples?\/|\.md$|\.txt$)/i', $f['rel'])) continue;
            $remotePath = $FTP_ROOT . '/' . str_replace('\\', '/', $f['rel']);
            if (uploadFile($ftp, $f['local'], $remotePath)) {
                $ok++;
            } else {
                $fail++;
                echo "  [FAIL] " . $f['rel'] . "\n";
            }
        }
        echo "  [OK] Carpeta $vpath/ subida\n";
    }
}

// Archivos raíz importantes
$rootFiles = ['composer.json'];
foreach ($rootFiles as $rf) {
    $local = $LOCAL_ROOT . DIRECTORY_SEPARATOR . $rf;
    if (file_exists($local)) {
        if (ftp_put($ftp, $FTP_ROOT . '/' . $rf, $local, FTP_ASCII)) {
            echo "[OK] $rf\n"; $ok++;
        }
    }
}

ftp_close($ftp);

echo "\n=== RESULTADO ===\n";
echo "OK:    $ok\n";
echo "FAIL:  $fail\n";
echo "\nSitio: https://3ricasistem.infinityfree.io/public/\n";
echo "Importar BD: https://3ricasistem.infinityfree.io/public/import_remote.php\n";
