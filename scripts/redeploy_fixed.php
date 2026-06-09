<?php

declare(strict_types=1);

$db_host = 'sql300.infinityfree.com';
$db_user = 'if0_42133482';
$db_pass = '61072715';
$db_name_main = 'if0_42133482_sistema_practicantes';

$ftp_host = 'ftpupload.net';
$ftp_user = 'if0_42133482';
$ftp_pass = '61072715';
$ftp_dir  = '/3ricasistem.infinityfree.io/htdocs';

echo "=== PREPARANDO PARCHE DE BASE DE DATOS Y RUTA (V7) ===\n\n";

// 1. Leer el SQL original de la base de datos principal
$sql_main_path = __DIR__ . '/../database/local_sistema_practicantes.sql';
if (!file_exists($sql_main_path)) {
    echo "[ERROR] No se encuentra database/local_sistema_practicantes.sql\n";
    exit(1);
}

$sql_main = file_get_contents($sql_main_path);

// 2. Reemplazar la vista temporal usando el Regex
$count1 = 0;
$sql_main = preg_replace(
    '/DROP TABLE IF EXISTS `areas`;\s*\/\*!50001 DROP VIEW IF EXISTS `areas`\*\/;.*?SET character_set_client = @saved_cs_client;/is', 
    '-- [VISTA TEMPORAL DE AREAS ELIMINADA]', 
    $sql_main, 
    -1, 
    $count1
);
echo "   -> Vista temporal de areas eliminada ($count1 veces).\n";

// 3. Reemplazar la vista final incluyendo los SET de restauración de variables usando el Regex V6
$count2 = 0;
$sql_main = preg_replace(
    '/\/\*!50001 DROP VIEW IF EXISTS `areas`\*\/;.*?\/\*!50001 SET collation_connection      = @saved_col_connection \*\/;/is', 
    '-- [VISTA FINAL DE AREAS ELIMINADA CON SUS VARIABLES]', 
    $sql_main, 
    -1, 
    $count2
);
echo "   -> Vista final de areas de digi y variables eliminadas ($count2 veces).\n";

// 4. Agregar la definición física de la tabla de áreas con sus datos al final del archivo
$physical_table_sql = "
--
-- Estructura de tabla para la tabla `areas`
--
DROP TABLE IF EXISTS `areas`;
CREATE TABLE `areas` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) DEFAULT NULL,
  `abreviatura` varchar(50) DEFAULT NULL,
  `estado` varchar(8) DEFAULT 'activo',
  `encargado` varchar(100) DEFAULT NULL,
  `Dni` int(11) DEFAULT NULL,
  `cargo` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `areas`
--
INSERT INTO `areas` (`id`, `nombre`, `abreviatura`, `estado`, `encargado`, `Dni`, `cargo`) VALUES
(1, 'UNIDAD DE TRÁMITE DOCUMENTARIO Y ARCHIVO', 'UTDA', 'activo', 'ANICAMA ROJAS, ROSA LUZ', 22306245, 'JEFE'),
(2, 'PROCURADURIA PÚBLICA MUNICIPAL', 'PPM', 'activo', 'HUAMAN PARRA, HUMBERTO', 22287692, 'PROCURADOR'),
(3, 'DIVISION DE DEFENSA CIVIL', 'DDC', 'activo', 'ALMEYDA ROJAS, ROBERTO RENEE', 22256952, 'ENCARGADO'),
(4, 'DIVISION PROGRAMA DE VASO DE LECHE', 'PROVAL', 'activo', 'LUJAN ROJAS, CARMEN YSABEL', 22288982, 'ENCARGADO'),
(5, 'GERENCIA MUNICIPAL', 'GM', 'activo', '', 0, ''),
(6, 'OFICINA GENERAL DE SECRETARIA', 'OGS', 'activo', 'VARGAS CERVANTES, THERE LISET', 72871741, 'ENCARGADO'),
(7, 'UNIDAD DE RR.PP E IMAGEN INSTITUCIONAL', 'URPII', 'activo', 'ENRIQUE ORMEÑO, NELIXSA YEZEBEL', 70477652, 'JEFE'),
(8, 'OFICINA GENERAL DE ADMINISTRACION Y FINANZAS', 'OGAF', 'activo', 'PALOMINO MATURANO, VARBARA MIRIAM', 48140844, 'DIRECTOR'),
(9, 'OFICINA GENERAL DE ADMINISTRACION TRIBUTARIA', 'OGAT', 'activo', 'MOLINA PANEZ, JESUS ALEXANDER', 71234571, 'JEFE'),
(10, 'OFICINA GENERAL DE ASESORIA JURIDICA', 'OGAJ', 'activo', 'ORTIZ BERNAOLA, DANIEL CANDELARIO', 22287654, 'DIRECTOR'),
(11, 'OFICINA GENERAL DE PLANIFICACION PRESUPUESTO Y RACIONALIZACION', 'OGPPR', 'activo', 'QUISPE ORTIZ, LEINNY OLINDA', 70615900, 'ESPECIALISTA'),
(12, 'UNIDAD DE PERSONAL', 'UP', 'activo', 'GALINDO MELGAR, ROSA DE LOS ANGELES', 72486402, 'ENCARGADO'),
(13, 'UNIDAD DE TESORERIA', 'UT', 'activo', 'COILA  DE LA CRUZ, ROSA ALBINA', 22259940, 'JEFE'),
(14, 'UNIDAD DE ABASTECIMIENTO Y CONTROL PATRIMONIAL', 'UACP', 'activo', 'NAVARRETE SOTELO, JUAN MANUEL', 40681665, 'JEFE'),
(15, 'UNIDAD DE CONTABILIDAD', 'UC', 'activo', 'SIFUENTES PUMA, MARIA DEL ROSARIO', 22302237, 'JEFE'),
(16, 'UNIDAD DE SISTEMAS', 'US', 'activo', 'PEÑA HUAMAN, ANDRIUWS ALEXCI', 44889344, 'ENCARGADO'),
(17, 'UNIDAD DE COBRANZAS COACTIVAS', 'UCC', 'activo', 'MOORE GUTIERREZ, HANS JHERY', 22240912, 'JEFE'),
(18, 'UNIDAD DE PLANIFICACION, RACIONALIZACION Y ESTADISTICA', 'UPRE', 'activo', 'PALOMINO MATURANO, VARBARA MIRIAM', 48140844, 'DIRECTORA'),
(19, 'GERENCIA DE SERVICIOS A LA CIUDAD, AMBIENTE Y SEGURIDAD PÚBLICA', 'GSCASP', 'activo', 'HERNANDEZ MONTOYA, FELIX FELIPE', 21402797, 'JEFE'),
(20, 'SUB GERENCIA DE DESARROLLO HUMANO Y PARTICIPACION VECINAL', 'GDH', 'activo', 'PEÑA PUMAHUALLCCA, CINTHIA', 44779733, 'ENCARGADO'),
(21, 'GERENCIA DE INVERSION PÚBLICA', 'GIP', 'activo', 'CANELA ORDOÑEZ, KATTYA LUCERO', 71653822, 'SUBGERENTE'),
(22, 'GERENCIA DE DESARROLLO SOCIAL Y ECONOMICO', 'GDSE', 'activo', 'VARGAS HUARCAYA, MARY ISABEL', 48092774, 'JEFE'),
(23, 'SUB GERENCIA DE INVERSION DE OBRAS PRIVADAS', 'SGIOPRI', 'activo', 'ORE TRILLO, JAEL JULLIANA', 41359314, 'SECRETARIA'),
(24, 'SUB GERENCIA DE REGISTRO CIVIL', 'SGRC', 'activo', 'MOREYRA PARRAVICINI, RYAN MARCELO', 46146897, 'ENCARGADO'),
(25, 'CORREDOR TURISTICO Y HUMEDALES', 'CTH', 'activo', 'CASMA ALMEIDA, PEDRO EVARISTO', 22242079, 'JEFE'),
(26, 'SERVICIOS GENERALES Y MANTENIMIENTO', 'SGM', 'activo', 'FLORES  GARCÍA, LUIS ZACARIAS', 22267687, 'ADMINISTRADOR'),
(27, 'GERENCIA DE DESARROLLO URBANO Y TRANSPORTE', 'GDUT', 'activo', 'CRISTOBAL VILLANUEVA, ALLAN RICHARD', 10637328, 'GERENTE'),
(28, 'ORGANO DE CONTROL INSTITUCIONAL', 'OCI', 'activo', '', 0, ''),
(29, 'REGULARIZACIÓN DE ESTADO CIVIL', 'REC', 'activo', 'YATACO DE LA CRUZ, WILFREDO DAVID', 22256116, 'GERENTE'),
(30, 'ALCALDIA', 'ALC', 'activo', 'GUEVARA CASTRO, FERNANDO', 22291956, 'JEFE'),
(31, 'SUB GERENCIA DE PLANTEAMIENTO URBANO Y CATASTRO', 'SGPUC', 'activo', '', 0, ''),
(32, 'DEFENSORIA DEL VECINO', 'DV', 'activo', '', 0, ''),
(33, 'INSTITUTO VIAL PROVINCIAL', 'IVP', 'activo', 'SARMIENTO MORALES, JESUS', 48067520, 'SUBGERENTE'),
(34, 'SUB GERENCIA DE ESTUDIOS Y FORMULACIÓN DE PROYECTOS', 'SGEFP', 'activo', 'CANELA ORDOÑEZ, KATTYA LUCERO', 71653822, 'SUBGERENTE'),
(35, 'SUB GERENCIA DE INVERSIÓN DE OBRAS PÚBLICAS', 'SGIOPUB', 'activo', 'SALCEDO CHUMBEZ, ANATOLIO JULIO', 22257261, 'ASISTENTE'),
(36, 'AUDITORIO', 'AUD', 'activo', '', 0, ''),
(37, 'MESA DE PARTES', 'MP', 'activo', '', 0, ''),
(38, 'SUB GERENCIA DE SEGURIDAD PÚBLICA', 'SGSP', 'activo', 'SALAZAR SANCHEZ, OSCAR ANDRES', 45558805, 'JEFE'),
(39, 'PARQUE ZONAL', 'PZ', 'activo', 'PARIONA  DE ANGULO, MERCEDES GLADYS', 22261636, 'JEFE'),
(40, 'CENTRO DE FAENAMIENTO DE ANIMALES DE ABASTO', 'CFAA', 'activo', 'HUAMANI ANGULO, MARIA BEATRIZ', 22272371, 'JEFE'),
(41, 'DIVISION SISFOH', 'SISF', 'activo', 'GUEVARA CASTRO, FERNANDO', 22291956, 'JEFE'),
(42, 'SUB GERENCIA AMBIENTAL MUNICIPAL', 'SGAM', 'activo', '', 0, ''),
(43, 'DIVISION DE LIMPIEZA PÚBLICA', 'DLP', 'activo', 'SOTIL HERNANDEZ, WILFREDO EDUARDO', 22315019, 'JEFE'),
(44, 'DIVISION DE PARQUES Y JARDINES', 'DPJ', 'activo', 'HUAMAN FLORES, JOE GIANMARCO', 73183216, 'ENCARGADO'),
(45, 'DIVISION DE SERENAZGO', 'DS', 'activo', 'AYALA ROJAS, JOHAN ROBERTO', 73795550, 'TRABAJADOR'),
(46, 'DIVISION DE POLICIA MUNICIPAL', 'DPM', 'activo', 'CHAVEZ BAUTISTA, LUIS JESUS', 70350086, 'ENCARGADO'),
(47, 'SUB GERENCIA DE TRANSPORTE Y SEGURIDAD VIAL', 'SGTSV', 'activo', 'ARRAZABA HERRERA, SANDY EVELYN', 46489520, 'ASISTENTE'),
(48, 'DIVISION DE TRANSPORTE Y TRANSITO', 'DTT', 'activo', '', 0, ''),
(49, 'DIVISION DE LICENCIAS Y CONTROL DE VEHICULOS MENORES', 'DLCVM', 'activo', '', 0, ''),
(50, 'DIVISION DE FISCALIZACION AL TRANSPORTE', 'DFT', 'activo', 'PACIFICO FUENTES, RONALDO JESÚS', 71322358, 'INSPECTOR'),
(51, 'DIVISION DEMUNA', 'DEMUNA', 'activo', 'ÑAÑEZ OLIVARES, DORIS LUZ', 6233797, 'JEFE'),
(52, 'DIVISION OMAPED', 'OMAPED', 'activo', 'CHAVEZ CONDORI, JOAQUIN ABIGAEL', 22244125, 'ASISTENTE'),
(53, 'DIVISION CIAM', 'CIAM', 'activo', 'RAMOS CAQUIAMARCA, MARIA ISABEL', 22290657, 'JEFE'),
(54, 'DIVISION DE PARTICIPACION VECINAL', 'DPV', 'activo', 'PEÑA PUMAHUALLCCA, CINTHIA', 44779733, 'ENCARGADO'),
(55, 'DIVISION DE SALUD, EDUCACION Y DEPORTE', 'DSED', 'activo', 'ORE ANICAMA, WALTER RALHP', 44860724, 'JEFE'),
(56, 'SUB GERENCIA DE DESARROLLO ECONOMICO', 'SGDE', 'activo', 'CABRERA VARGAS, GLADYS DELIA', 21457705, 'GERENTE'),
(57, 'ESTADIO MUNICIPAL', 'EM', 'activo', 'ADVINCULA ESPINO, MANUEL EDUARDO', 42255070, 'ENCARGADO'),
(58, 'CENTRO CULTURAL MUNICIPAL', 'CCM', 'activo', 'DONAYRE CASAVILCA, JORGE RICARDO', 22260649, 'ADMINISTRADOR'),
(59, 'DIVISION DE COMERCIALIZACION', 'DCOMER', 'activo', 'RENTEROS ARAUJO, IXOMIRO', 22248841, 'ENCARGADO'),
(60, 'MERCADO MUNICIPAL', 'MM', 'activo', 'MEJIA VALDERRAMA, MIGUEL ANGEL', 22289732, 'ENCARGADO'),
(61, 'DIVISION DE TURISMO', 'DT', 'activo', 'CABRERA VARGAS, GLADYS DELIA', 21457705, 'GERENTE'),
(62, 'DIVISION DE FOMENTO A LA MICRO Y PEQUEÑA EMPRESA', 'DFMPE', 'activo', 'FUENTES LEON, JUAN AGUSTIN', 22263895, 'ENCARGADO'),
(63, 'SUB GERENCIA DE PROGRAMAS ASISTENCIALES DE LUCHA CONTRA LA POBREZA', 'SGPALCP', 'activo', 'CALMET CARBAJAL, ROGER GABRIEL', 46396278, 'GERENTE'),
(64, 'DIVISION DE COMEDORES', 'DCOMED', 'activo', 'SALAZAR  SANCHEZ, VICTOR AQUILES', 44510058, 'ENCARGADO'),
(65, 'AREA DE REMUNERACION', 'AR', 'activo', 'ONTIVEROS DUEÑAS, VERONICA JULISSA', 43763735, 'ENCARGADO'),
(66, 'AREA DE ESCALAFON Y CONTROL', 'ACE', 'activo', 'LLANGE ARCE, JESUS BERNABE', 22182079, 'ENCARGADO'),
(67, 'AREA DE BIENESTAR SOCIAL', 'ASS', 'activo', 'QUINCHO ENRIQUEZ, ROSA', 22245039, 'JEFE'),
(68, 'AREA DE CONTROL PATRIMONIAL', 'ACP', 'activo', 'COILA  DE LA CRUZ, ELSA  YOLANDA', 22270797, 'JEFE'),
(69, 'AREA DE ALMACEN', 'AA', 'activo', '', 0, ''),
(70, 'UNIDAD DE REGISTRO Y RECAUDACION TRIBUTARIA', 'URRT', 'activo', 'CHECCLLO MEZA, MAGNO PLATON', 45417521, 'TECNICO'),
(71, 'UNIDAD DE FISCALIZACION', 'UFIS', 'activo', 'SOTO NAVARRETE, GUSTAVO RUBEN', 22265302, 'ASISTENTE'),
(72, 'UNIDAD DE PRESUPUESTO', 'UP', 'activo', 'QUISPE ORTIZ, LEINNY OLINDA', 70615900, 'ESPECIALISTA'),
(73, 'UNIDAD DE PROGRAMACION MULTIANUAL DE INVERSIONES', 'UPMI', 'activo', '', 0, ''),
(74, 'ADMINISTRACION PUBLICA MERCADO 2', 'APM2', 'activo', 'MEJIA VALDERRAMA, MIGUEL ANGEL', 22289732, 'ENCARGADO'),
(75, 'CENTRO DE FAENAMIENTO DE ANIMALES DE ABASTO', 'CFAA', 'activo', '', 0, ''),
(76, 'BORDE COSTERO', 'BCOS', 'activo', '', 0, '');
";

$sql_main .= "\n" . $physical_table_sql;

// Guardar archivo SQL final parchado
$adjusted_sql_path = __DIR__ . '/../database/local_sistema_practicantes_adjusted.sql';
file_put_contents($adjusted_sql_path, $sql_main);
echo "[OK] SQL Ajustado definitivo creado.\n";

// 5. Generar el script de importación definitivo (sin autodestrucción)
$import_script = <<<PHP
<?php
declare(strict_types=1);

error_reporting(E_ALL);
ini_set('display_errors', '1');
header('Content-Type: text/plain; charset=utf-8');

\$db_host = '{$db_host}';
\$db_user = '{$db_user}';
\$db_pass = '{$db_pass}';
\$db_name_main = '{$db_name_main}';

echo "=== IMPORTACIÓN REMOTA DEFINITIVA (V7 - NO AUTO-DESTRUCT) ===\\n\\n";

try {
    echo "1. Conectando a la base de datos principal...\\n";
    \$pdo = new PDO("mysql:host=\$db_host;dbname=\$db_name_main;charset=utf8mb4", \$db_user, \$db_pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
    
    \$file = __DIR__ . '/database/local_sistema_practicantes_adjusted.sql';
    if (!file_exists(\$file)) {
        throw new Exception("No existe el archivo: database/local_sistema_practicantes_adjusted.sql");
    }
    
    echo "2. Limpiando base de datos previa (si existe)...\\n";
    \$pdo->exec("SET FOREIGN_KEY_CHECKS = 0;");
    \$tables = ['usuarios', 'reportes', 'practicantes', 'personas', 'instituciones', 'horarios', 'carreras', 'asistencias', 'areas'];
    foreach (\$tables as \$table) {
        \$pdo->exec("DROP TABLE IF EXISTS `\$table` CASCADE;");
        \$pdo->exec("DROP VIEW IF EXISTS `\$table` CASCADE;");
    }
    \$pdo->exec("SET FOREIGN_KEY_CHECKS = 1;");
    
    echo "3. Importando esquema y datos con la tabla física 'areas'...\\n";
    \$sql = file_get_contents(\$file);
    \$pdo->exec(\$sql);
    echo "   [OK] Base de datos principal importada con éxito con la tabla física.\\n\\n";
    
} catch (Exception \$e) {
    echo "   [ERROR] " . \$e->getMessage() . "\\n\\n";
}

echo "=== PROCESO COMPLETADO V7 ===";
PHP;

$import_temp_file = __DIR__ . '/../temp_import.php';
file_put_contents($import_temp_file, $import_script);
echo "[OK] Script import_remote temporal creado.\n";

// 6. Subir vía FTP
echo "\nConectando al FTP...\n";
$ftp_conn = ftp_connect($ftp_host);
if (!$ftp_conn) { echo "[ERROR] No se pudo conectar al FTP.\n"; exit(1); }

$ftp_login = ftp_login($ftp_conn, $ftp_user, $ftp_pass);
if (!$ftp_login) { echo "[ERROR] Credenciales incorrectas.\n"; ftp_close($ftp_conn); exit(1); }

ftp_pasv($ftp_conn, true);
echo "[OK] Conectado.\n";

// Subir SQL parchado
echo "-> Subiendo SQL...\n";
ftp_chdir($ftp_conn, $ftp_dir . '/database');
ftp_put($ftp_conn, 'local_sistema_practicantes_adjusted.sql', $adjusted_sql_path, FTP_BINARY);

// Subir import_remote.php
echo "-> Subiendo import_remote.php...\n";
ftp_chdir($ftp_conn, $ftp_dir);
ftp_put($ftp_conn, 'import_remote.php', $import_temp_file, FTP_BINARY);

ftp_close($ftp_conn);
@unlink($import_temp_file);
@unlink($adjusted_sql_path);

echo "\n=== PROCESO COMPLETADO ===\n";
echo "Abre este enlace en tu navegador para importar:\n";
echo "👉 https://3ricasistem.infinityfree.io/import_remote.php 👈\n";
