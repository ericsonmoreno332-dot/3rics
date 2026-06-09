<?php
declare(strict_types=1);

$db_host = 'sql300.infinityfree.com';
$db_user = 'if0_42133482';
$db_pass = '61072715';
$db_name_main = 'if0_42133482_sistema_practicantes';

try {
    $pdo = new PDO("mysql:host=$db_host;dbname=$db_name_main;charset=utf8mb4", $db_user, $db_pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
    
    echo "Conexión exitosa. Tablas en la base de datos:\n";
    $stmt = $pdo->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    print_r($tables);
    
    if (in_array('areas', $tables, true)) {
        echo "\nConteo de registros en 'areas': ";
        $count = $pdo->query("SELECT COUNT(*) FROM areas")->fetchColumn();
        echo "$count registros.\n";
        
        echo "Primeros 5 registros de 'areas':\n";
        print_r($pdo->query("SELECT id, nombre, estado FROM areas LIMIT 5")->fetchAll());
    } else {
        echo "\nLa tabla 'areas' no existe.\n";
    }
    
    if (in_array('usuarios', $tables, true)) {
        echo "\nConteo de registros en 'usuarios': ";
        $count = $pdo->query("SELECT COUNT(*) FROM usuarios")->fetchColumn();
        echo "$count registros.\n";
    }
} catch (Exception $e) {
    echo "Error de conexión o consulta: " . $e->getMessage() . "\n";
}
