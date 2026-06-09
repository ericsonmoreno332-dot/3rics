<?php
declare(strict_types=1);

$ftp_host = 'ftpupload.net';
$ftp_user = 'if0_42133482';
$ftp_pass = '61072715';
$ftp_dir  = '/3ricasistem.infinityfree.io/htdocs';
$local_base = realpath(__DIR__ . '/..');

// Archivos/carpetas específicas que fallaron y hay que resubir
$retry_paths = [
    'vendor/vlucas/phpdotenv/src',
    'vendor/phpoption/phpoption/src',
    'vendor/setasign/fpdi/src',
    'views',
];

echo "=== REINTENTANDO ARCHIVOS FALLIDOS ===\n\n";

$ftp_conn = ftp_connect($ftp_host);
if (!$ftp_conn) { echo "[ERROR] No se pudo conectar al FTP.\n"; exit(1); }

$ftp_login = ftp_login($ftp_conn, $ftp_user, $ftp_pass);
if (!$ftp_login) { echo "[ERROR] Credenciales FTP incorrectas.\n"; ftp_close($ftp_conn); exit(1); }

ftp_pasv($ftp_conn, true);
echo "[OK] Conectado al FTP.\n\n";

function ftp_ensure_dir($ftp, $base_dir, $relative_path) {
    $parts = explode('/', trim($relative_path, '/'));
    $current = $base_dir;
    if (!@ftp_chdir($ftp, $current)) return false;
    foreach ($parts as $part) {
        if ($part === '') continue;
        $current .= '/' . $part;
        if (!@ftp_chdir($ftp, $current)) {
            if (!@ftp_mkdir($ftp, $current)) return false;
            @ftp_chdir($ftp, $current);
        }
    }
    return true;
}

function upload_path($ftp, $local_path, $remote_rel, $ftp_dir) {
    if (is_file($local_path)) {
        $dir = dirname($remote_rel);
        ftp_ensure_dir($ftp, $ftp_dir, $dir === '.' ? '' : $dir);
        ftp_chdir($ftp, $ftp_dir . ($dir === '.' ? '' : '/' . $dir));
        $retries = 5;
        $ok = false;
        while ($retries > 0 && !$ok) {
            if (@ftp_put($ftp, basename($remote_rel), $local_path, FTP_BINARY)) {
                echo "  [UP] $remote_rel\n";
                $ok = true;
            } else {
                $retries--;
                echo "  [REINTENTO] $remote_rel ($retries intentos restantes)\n";
                sleep(2);
            }
        }
        if (!$ok) echo "  [ERROR] No se pudo subir: $remote_rel\n";
        return;
    }

    if (is_dir($local_path)) {
        ftp_ensure_dir($ftp, $ftp_dir, $remote_rel);
        $items = scandir($local_path);
        foreach ($items as $item) {
            if ($item === '.' || $item === '..') continue;
            upload_path($ftp, $local_path . '/' . $item, $remote_rel . '/' . $item, $ftp_dir);
        }
    }
}

foreach ($retry_paths as $rel_path) {
    $local = $local_base . '/' . $rel_path;
    if (!file_exists($local)) {
        echo "[SKIP] No existe localmente: $rel_path\n";
        continue;
    }
    echo "-> Subiendo: $rel_path\n";
    upload_path($ftp_conn, $local, $rel_path, $ftp_dir);
}

ftp_close($ftp_conn);

echo "\n=== ¡REINTENTO COMPLETADO! ===\n";
echo "Ahora ve a tu navegador y abre:\n";
echo "👉 https://3ricasistem.infinityfree.io/import_remote.php\n";
echo "para importar las bases de datos.\n";
