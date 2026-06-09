<?php
// =============================================
// SCRIPT DE RE-SUBIDA DE ARCHIVOS FALLIDOS
// =============================================
set_time_limit(0);
ini_set('display_errors', 1);

$FTP_HOST = 'ftpupload.net';
$FTP_USER = 'if0_42133482';
$FTP_PASS = '61072715';
$FTP_ROOT = '/htdocs';
$LOCAL_ROOT = __DIR__;

// Lista de archivos que fallaron
$failedFiles = [
    'views/layout.php',
    'vendor/symfony/polyfill-ctype/bootstrap.php',
    'vendor/symfony/polyfill-ctype/bootstrap80.php',
    'vendor/symfony/polyfill-ctype/composer.json',
    'vendor/symfony/polyfill-ctype/Ctype.php',
    'vendor/symfony/polyfill-ctype/LICENSE',
    'vendor/symfony/polyfill-ctype/README.md',
    'vendor/symfony/polyfill-mbstring/bootstrap.php',
    'vendor/symfony/polyfill-mbstring/bootstrap72.php',
    'vendor/symfony/polyfill-mbstring/bootstrap80.php',
    'vendor/symfony/polyfill-mbstring/composer.json',
    'vendor/symfony/polyfill-mbstring/LICENSE',
    'vendor/symfony/polyfill-mbstring/Mbstring.php',
    'vendor/symfony/polyfill-mbstring/README.md',
    'vendor/symfony/polyfill-mbstring/Resources/unidata/caseFolding.php',
    'vendor/symfony/polyfill-mbstring/Resources/unidata/lowerCase.php',
    'vendor/symfony/polyfill-mbstring/Resources/unidata/titleCaseRegexp.php',
    'vendor/symfony/polyfill-mbstring/Resources/unidata/upperCase.php',
    'vendor/symfony/polyfill-php80/bootstrap.php',
    'vendor/symfony/polyfill-php80/composer.json',
    'vendor/symfony/polyfill-php80/LICENSE',
    'vendor/symfony/polyfill-php80/Php80.php',
    'vendor/symfony/polyfill-php80/PhpToken.php',
    'vendor/symfony/polyfill-php80/README.md',
    'vendor/symfony/polyfill-php80/Resources/stubs/Attribute.php',
    'vendor/symfony/polyfill-php80/Resources/stubs/PhpToken.php',
    'vendor/symfony/polyfill-php80/Resources/stubs/Stringable.php',
    'vendor/symfony/polyfill-php80/Resources/stubs/UnhandledMatchError.php',
    'vendor/symfony/polyfill-php80/Resources/stubs/ValueError.php',
    'vendor/vlucas/phpdotenv/.editorconfig',
    'vendor/vlucas/phpdotenv/composer.json',
    'vendor/vlucas/phpdotenv/LICENSE',
    'vendor/vlucas/phpdotenv/Makefile',
    'vendor/vlucas/phpdotenv/phpstan-baseline.neon',
    'vendor/vlucas/phpdotenv/phpstan.neon.dist',
    'vendor/vlucas/phpdotenv/phpunit.xml.dist',
    'vendor/vlucas/phpdotenv/README.md',
    'vendor/vlucas/phpdotenv/src/Dotenv.php',
    'vendor/vlucas/phpdotenv/src/Exception/ExceptionInterface.php',
    'vendor/vlucas/phpdotenv/src/Exception/InvalidEncodingException.php',
    'vendor/vlucas/phpdotenv/src/Exception/InvalidFileException.php',
    'vendor/vlucas/phpdotenv/src/Exception/InvalidPathException.php',
    'vendor/vlucas/phpdotenv/src/Exception/ValidationException.php',
    'vendor/vlucas/phpdotenv/src/Loader/Loader.php',
    'vendor/vlucas/phpdotenv/src/Loader/LoaderInterface.php',
    'vendor/vlucas/phpdotenv/src/Loader/Resolver.php',
    'vendor/vlucas/phpdotenv/src/Parser/Entry.php',
    'vendor/vlucas/phpdotenv/src/Parser/EntryParser.php',
    'vendor/vlucas/phpdotenv/src/Parser/Lexer.php',
    'vendor/vlucas/phpdotenv/src/Parser/Lines.php',
    'vendor/vlucas/phpdotenv/src/Parser/Parser.php',
    'vendor/vlucas/phpdotenv/src/Parser/ParserInterface.php',
    'vendor/vlucas/phpdotenv/src/Parser/Value.php',
    'vendor/vlucas/phpdotenv/src/Repository/Adapter/AdapterInterface.php',
    'vendor/vlucas/phpdotenv/src/Repository/Adapter/ApacheAdapter.php',
    'vendor/vlucas/phpdotenv/src/Repository/Adapter/ArrayAdapter.php',
    'vendor/vlucas/phpdotenv/src/Repository/Adapter/EnvConstAdapter.php',
    'vendor/vlucas/phpdotenv/src/Repository/Adapter/GuardedWriter.php',
    'vendor/vlucas/phpdotenv/src/Repository/Adapter/ImmutableWriter.php',
    'vendor/vlucas/phpdotenv/src/Repository/Adapter/MultiReader.php',
    'vendor/vlucas/phpdotenv/src/Repository/Adapter/MultiWriter.php',
    'vendor/vlucas/phpdotenv/src/Repository/Adapter/PutenvAdapter.php',
    'vendor/vlucas/phpdotenv/src/Repository/Adapter/ReaderInterface.php',
    'vendor/vlucas/phpdotenv/src/Repository/Adapter/ReplacingWriter.php',
    'vendor/vlucas/phpdotenv/src/Repository/Adapter/ServerConstAdapter.php',
    'vendor/vlucas/phpdotenv/src/Repository/Adapter/WriterInterface.php',
    'vendor/vlucas/phpdotenv/src/Repository/AdapterRepository.php',
    'vendor/vlucas/phpdotenv/src/Repository/RepositoryBuilder.php',
    'vendor/vlucas/phpdotenv/src/Repository/RepositoryInterface.php',
    'vendor/vlucas/phpdotenv/src/Store/File/Paths.php',
    'vendor/vlucas/phpdotenv/src/Store/File/Reader.php',
    'vendor/vlucas/phpdotenv/src/Store/FileStore.php',
    'vendor/vlucas/phpdotenv/src/Store/StoreBuilder.php',
    'vendor/vlucas/phpdotenv/src/Store/StoreInterface.php',
    'vendor/vlucas/phpdotenv/src/Store/StringStore.php',
    'vendor/vlucas/phpdotenv/src/Util/Regex.php',
    'vendor/vlucas/phpdotenv/src/Util/Str.php',
    'vendor/vlucas/phpdotenv/src/Validator.php',
    'vendor/vlucas/phpdotenv/UPGRADING.md',
    'vendor/vlucas/phpdotenv/vendor-bin/phpstan/composer.json',
];

function ftpMkdirRecursive($ftp, $path) {
    $parts = explode('/', trim($path, '/'));
    $current = '';
    foreach ($parts as $part) {
        $current .= '/' . $part;
        if (@ftp_chdir($ftp, $current)) {
            ftp_chdir($ftp, '/');
            continue;
        }
        @ftp_mkdir($ftp, $current);
    }
}

function uploadFile($ftp, $localPath, $remotePath, $retries = 3) {
    for ($i = $retries; $i >= 0; $i--) {
        if (ftp_put($ftp, $remotePath, $localPath, FTP_BINARY)) {
            return true;
        }
        if ($i > 0) sleep(2);
    }
    return false;
}

echo "=== SUBIENDO ARCHIVOS FALLIDOS ===\n\n";
echo "Conectando a $FTP_HOST...\n";

$ftp = ftp_connect($FTP_HOST, 21, 30);
if (!$ftp) die("[ERROR] No se pudo conectar al FTP\n");

if (!ftp_login($ftp, $FTP_USER, $FTP_PASS)) die("[ERROR] Login fallido\n");

ftp_pasv($ftp, true);
echo "[OK] Conectado!\n\n";

$ok = 0;
$fail = 0;
$failedList = [];

foreach ($failedFiles as $relPath) {
    $localFile  = $LOCAL_ROOT . '/' . str_replace('/', DIRECTORY_SEPARATOR, $relPath);
    $remoteFile = $FTP_ROOT . '/' . $relPath;
    $remoteDir  = dirname($remoteFile);

    if (!file_exists($localFile)) {
        echo "[SKIP] No existe localmente: $relPath\n";
        continue;
    }

    // Crear directorio remoto si no existe
    ftpMkdirRecursive($ftp, $remoteDir);

    if (uploadFile($ftp, $localFile, $remoteFile)) {
        echo "[OK] $relPath\n";
        $ok++;
    } else {
        echo "[FAIL] $relPath\n";
        $fail++;
        $failedList[] = $relPath;
    }
}

ftp_close($ftp);

echo "\n=== RESULTADO ===\n";
echo "Subidos OK: $ok\n";
echo "Fallidos:   $fail\n";

if (!empty($failedList)) {
    echo "\nArchivos que aun fallan:\n";
    foreach ($failedList as $f) echo "  - $f\n";
}

echo "\n=== LISTO ===\n";
