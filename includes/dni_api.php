<?php

declare(strict_types=1);

/**
 * Consulta la API de DNI y retorna los datos procesados.
 * 
 * @param string $dni
 * @return array{ok:bool, data?:array, msg?:string}
 */
function fetch_dni_data(string $dni): array
{
    $url = env_string('DNI_API_URL', 'https://api.apisperu.com/v1/dni/');
    $token = env_string('DNI_API_TOKEN', '');

    if (empty($token) || $token === 'tu_token_aqui') {
        return ['ok' => false, 'msg' => 'API Token no configurado en .env'];
    }

    // Limpiar DNI
    $dni = preg_replace('/\D/', '', $dni);
    if (strlen($dni) !== 8) {
        return ['ok' => false, 'msg' => 'DNI inválido'];
    }

    $apiUrl = rtrim($url, '/') . '/' . $dni;
    
    $opts = [
        'http' => [
            'method' => 'GET',
            'header' => [
                "Authorization: Bearer $token",
                "Accept: application/json",
                "User-Agent: PHP-REGIS-APP"
            ],
            'ignore_errors' => true,
            'timeout' => 15
        ]
    ];

    $context = stream_context_create($opts);
    $response = @file_get_contents($apiUrl, false, $context);

    if ($response === false) {
        return ['ok' => false, 'msg' => 'Error de conexión con ApiInti (verifique su internet o el servidor)'];
    }

    $data = json_decode($response, true);
    
    // Si los datos vienen anidados en un objeto 'data', los extraemos
    $res = isset($data['data']) && is_array($data['data']) ? $data['data'] : $data;
    
    // Verificamos errores reportados por la API
    if (isset($data['error']) || isset($data['message']) && !isset($res['nombres'])) {
        return ['ok' => false, 'msg' => $data['message'] ?? $data['error'] ?? 'DNI no encontrado'];
    }

    if (!$res || !isset($res['nombres'])) {
        // Debug: Si no encontramos nombres, enviamos un mensaje indicando qué recibimos
        return ['ok' => false, 'msg' => 'La API respondió pero no se encontraron nombres. Verifique su plan o el DNI.'];
    }

    return [
        'ok' => true,
        'data' => [
            'dni' => $res['dni'] ?? $res['numero'] ?? $dni,
            'nombres' => $res['nombres'] ?? $res['nombre'] ?? '',
            'apellido_paterno' => $res['apellidoPaterno'] ?? $res['apellido_paterno'] ?? $res['paterno'] ?? '',
            'apellido_materno' => $res['apellidoMaterno'] ?? $res['apellido_materno'] ?? $res['materno'] ?? '',
        ]
    ];
}

/**
 * Guarda o actualiza un registro en la tabla personas.
 */
function save_to_personas(PDO $pdo, array $data): void
{
    $st = $pdo->prepare('
        INSERT INTO personas (dni, nombres, apellido_paterno, apellido_materno, fecha_consulta)
        VALUES (?, ?, ?, ?, CURRENT_TIMESTAMP)
        ON DUPLICATE KEY UPDATE 
            nombres = VALUES(nombres),
            apellido_paterno = VALUES(apellido_paterno),
            apellido_materno = VALUES(apellido_materno),
            fecha_consulta = CURRENT_TIMESTAMP
    ');
    $st->execute([
        $data['dni'],
        $data['nombres'],
        $data['apellido_paterno'],
        $data['apellido_materno']
    ]);
}
