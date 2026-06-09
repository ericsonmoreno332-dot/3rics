<?php
declare(strict_types=1);

$url = 'https://3ricasistem.infinityfree.io/';
echo "Haciendo petición a: $url\n";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)');

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$effective_url = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);

if (curl_errno($ch)) {
    echo "Error cURL: " . curl_error($ch) . "\n";
} else {
    echo "Código HTTP: $http_code\n";
    echo "URL final: $effective_url\n";
    echo "\nPrimeros 500 caracteres de respuesta:\n";
    echo substr($response, 0, 500) . "\n";
}
curl_close($ch);
