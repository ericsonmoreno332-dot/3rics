<?php
declare(strict_types=1);

$ftp_host = 'ftpupload.net';
$ftp_user = 'if0_42133482';
$ftp_pass = '61072715';
$ftp_dir  = '/3ricasistem.infinityfree.io/htdocs';

$ftp_conn = ftp_connect($ftp_host);
if (!$ftp_conn) {
    echo "No se pudo conectar al FTP.\n";
    exit(1);
}

if (!ftp_login($ftp_conn, $ftp_user, $ftp_pass)) {
    echo "Credenciales incorrectas.\n";
    ftp_close($ftp_conn);
    exit(1);
}

ftp_pasv($ftp_conn, true);
ftp_chdir($ftp_conn, $ftp_dir);

echo "Eliminando import_remote.php por seguridad...\n";
if (@ftp_delete($ftp_conn, 'import_remote.php')) {
    echo "   [OK] import_remote.php eliminado con éxito de producción.\n";
} else {
    echo "   [FAIL] No se pudo eliminar import_remote.php (tal vez ya no existe).\n";
}

ftp_close($ftp_conn);
