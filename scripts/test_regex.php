<?php
declare(strict_types=1);

$sql = file_get_contents(__DIR__ . '/../database/local_sistema_practicantes.sql');

echo "--- PROBANDO REGEX V6 NUEVO CON COLA DE VARIABLES ---\n";

// 1. Reemplazar la vista temporal
$count1 = 0;
$sql = preg_replace('/DROP TABLE IF EXISTS `areas`;\s*\/\*!50001 DROP VIEW IF EXISTS `areas`\*\/;.*?SET character_set_client = @saved_cs_client;/is', '-- [VISTA TEMPORAL DE AREAS ELIMINADA]', $sql, -1, $count1);
echo "Vista temporal de areas eliminada: $count1 veces.\n";

// 2. Reemplazar la vista final incluyendo los SET de restauración de variables
$count2 = 0;
$sql = preg_replace('/\/\*!50001 DROP VIEW IF EXISTS `areas`\*\/;.*?\/\*!50001 SET collation_connection      = @saved_col_connection \*\/;/is', '-- [VISTA FINAL DE AREAS ELIMINADA CON SUS VARIABLES]', $sql, -1, $count2);
echo "Vista final de areas eliminada con variables: $count2 veces.\n";

// Guardar resultado de prueba para verificar
file_put_contents(__DIR__ . '/../database/test_result.sql', $sql);
echo "Prueba escrita en database/test_result.sql\n";
