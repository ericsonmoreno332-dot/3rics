<?php
declare(strict_types=1);

$ftp_host = 'ftpupload.net';
$ftp_user = 'if0_42133482';
$ftp_pass = '61072715';

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
echo "Conectado. Listando directorio raíz:\n";
$list = ftp_nlist($ftp_conn, '.');
print_r($list);

echo "\nIntentando listar carpetas de manera recursiva:\n";
foreach ($list as $item) {
    echo "--- Carpeta: $item ---\n";
    $sub = ftp_nlist($ftp_conn, $item);
    print_r($sub);
    
    if (is_array($sub)) {
        foreach ($sub as $subitem) {
            if (strpos($subitem, 'htdocs') !== false) {
                echo "      Contenido de $subitem:\n";
                print_r(ftp_nlist($ftp_conn, $subitem));
            }
        }
    }
}

ftp_close($ftp_conn);
