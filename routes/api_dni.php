<?php

declare(strict_types=1);

// Solo permitir usuarios autenticados (admin o supervisor)
$user = require_roles(['admin', 'supervisor']);

header('Content-Type: application/json');

$dni = input('dni');

if (!$dni || strlen($dni) !== 8) {
    echo json_encode(['ok' => false, 'msg' => 'DNI inválido (debe tener 8 dígitos)']);
    exit;
}

$pdo = db();

try {
    $result = fetch_dni_data($dni);

    if ($result['ok']) {
        // Guardar en la tabla personas
        save_to_personas($pdo, $result['data']);
        
        // Retornar los datos para el formulario
        echo json_encode([
            'ok' => true,
            'data' => $result['data']
        ]);
    } else {
        echo json_encode(['ok' => false, 'msg' => $result['msg']]);
    }
} catch (Exception $e) {
    echo json_encode(['ok' => false, 'msg' => 'Error interno: ' . $e->getMessage()]);
}
