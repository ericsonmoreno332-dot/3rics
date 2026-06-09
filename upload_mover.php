<?php
set_time_limit(0);

$FTP_HOST = 'ftpupload.net';
$FTP_USER = 'if0_42133482';
$FTP_PASS = '61072715';
$FTP_ROOT = '/htdocs';

// Script PHP que se ejecutará en el servidor para copiar vendor
$moverVendorPhp = <<<'PHP'
<?php
set_time_limit(300);
ini_set('display_errors', 1);

echo "<pre style='background:#111;color:#0f0;padding:20px;font-size:13px'>\n";
echo "=== MOVIENDO VENDOR Y ARCHIVOS ===\n\n";

$htdocs = __DIR__;  // /htdocs/public
$root   = dirname(__DIR__); // /htdocs

// Origen: donde estaba todo antes
$origen = $root . '/3ricso';

if (!is_dir($origen)) {
    echo "[INFO] Carpeta /htdocs/3ricso no existe. El vendor puede estar ya en su lugar.\n";
    
    // Verificar si vendor ya está correcto
    if (is_dir($root . '/vendor')) {
        echo "[OK] /htdocs/vendor/ existe!\n";
        $files = scandir($root . '/vendor');
        echo "Contenido vendor: " . implode(', ', array_filter($files, fn($f) => $f !== '.' && $f !== '..')) . "\n";
    } else {
        echo "[WARN] /htdocs/vendor/ NO existe\n";
    }
    echo "</pre>";
    exit;
}

echo "Carpeta origen: $origen\n\n";

// Listar qué tiene /htdocs/3ricso/
$items = array_filter(scandir($origen), fn($f) => $f !== '.' && $f !== '..');
echo "Contenido de /htdocs/3ricso/:\n";
foreach ($items as $item) echo "  - $item\n";
echo "\n";

// Función para copiar recursivamente
function copyDir($src, $dst) {
    if (!is_dir($dst)) mkdir($dst, 0755, true);
    $files = scandir($src);
    $ok = 0; $fail = 0;
    foreach ($files as $file) {
        if ($file === '.' || $file === '..') continue;
        $srcPath = $src . '/' . $file;
        $dstPath = $dst . '/' . $file;
        if (is_dir($srcPath)) {
            [$o, $f] = copyDir($srcPath, $dstPath);
            $ok += $o; $fail += $f;
        } else {
            if (!file_exists($dstPath)) {
                if (copy($srcPath, $dstPath)) $ok++;
                else $fail++;
            } else {
                $ok++; // ya existe
            }
        }
    }
    return [$ok, $fail];
}

// Copiar vendor
$vendorSrc = $origen . '/vendor';
$vendorDst = $root . '/vendor';
if (is_dir($vendorSrc)) {
    echo "Copiando vendor/ ...\n";
    [$ok, $fail] = copyDir($vendorSrc, $vendorDst);
    echo "  [OK] Copiados: $ok | Fallidos: $fail\n\n";
} else {
    echo "[WARN] No existe /htdocs/3ricso/vendor/\n\n";
}

// Verificar estructura final
echo "=== ESTRUCTURA FINAL EN /htdocs/ ===\n";
$check = ['vendor', 'includes', 'routes', 'views', 'public', 'database', '.env'];
foreach ($check as $item) {
    $path = $root . '/' . $item;
    $exists = file_exists($path) || is_dir($path);
    echo ($exists ? '[OK]' : '[FALTA]') . " $item\n";
}

// Verificar autoload
echo "\nvendor/autoload.php: " . (file_exists($root . '/vendor/autoload.php') ? '[OK]' : '[FALTA]') . "\n";

// Probar conexión BD
echo "\n=== PRUEBA BD ===\n";
try {
    $pdo = new PDO(
        'mysql:host=sql300.infinityfree.com;port=3306;dbname=if0_42133482_sistema_practicantes;charset=utf8mb4',
        'if0_42133482', '61072715',
        [PDO::ATTR_TIMEOUT => 10, PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    echo "[OK] Conexión MySQL exitosa!\n";
    echo "Tablas: " . count($tables) . "\n";
    foreach ($tables as $t) echo "  - $t\n";
} catch (Exception $e) {
    echo "[ERROR] " . $e->getMessage() . "\n";
}

echo "\n=== LISTO ===\n";
echo "Visita: <a href='https://3ricasistem.infinityfree.io/public/' style='color:cyan'>https://3ricasistem.infinityfree.io/public/</a>\n";
echo "</pre>";
PHP;

$tmpScript = tempnam(sys_get_temp_dir(), 'mover');
file_put_contents($tmpScript, $moverVendorPhp);

echo "Conectando FTP...\n";
$ftp = ftp_connect($FTP_HOST, 21, 30);
if (!$ftp) die("[ERROR] No se pudo conectar\n");
if (!ftp_login($ftp, $FTP_USER, $FTP_PASS)) die("[ERROR] Login fallido\n");
ftp_pasv($ftp, true);
echo "[OK] Conectado!\n";

// Subir el script a public/
@ftp_mkdir($ftp, $FTP_ROOT . '/public');
if (ftp_put($ftp, $FTP_ROOT . '/public/mover_vendor.php', $tmpScript, FTP_ASCII)) {
    echo "[OK] mover_vendor.php subido a public/\n";
} else {
    echo "[FAIL] No se pudo subir mover_vendor.php\n";
}

ftp_close($ftp);
unlink($tmpScript);

echo "\n=== LISTO ===\n";
echo "Abre: https://3ricasistem.infinityfree.io/public/mover_vendor.php\n";
