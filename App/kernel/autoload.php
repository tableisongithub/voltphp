<?php
if (version_compare(phpversion(), '8', '<')) {
    die("VoltPHP requires PHP 8 or higher.");
}
error_reporting(E_ALL);
header("Server: VoltPHP");
header("X-Powered-By: VoltPHP");
function runTroughtFolder($dir)
{
    $files = scandir($dir);
    foreach ($files as $file) {
        if ($file == "." || $file == "..") {
            continue;
        }
        if (is_dir($dir . "/" . $file)) {
            runTroughtFolder($dir . "/" . $file);
        } else {
            require_once $dir . "/" . $file;
        }
    }
}
if (!file_exists(ROOT . "/.env")) {
    trigger_error("No.env file found. Creating empty... Please fill it.", E_WARNING);
    $envFile = fopen(ROOT . "/.env", "w");
    fwrite($envFile, '
DB_HOST =
DB_PORT =
DB_DB =
DB_USER =
DB_PASSWORD =
DB_UNSAFE =
MAINTENANCE =
');
    fclose($envFile);
    die();
}
$env = parse_ini_file(ROOT . '/.env');
define('env', $env);
if ($env["MAINTENANCE"]) {
    // show everyerror and warning
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    ini_set('error_log', ROOT . '/storage/logs/error.log');
    ini_set('log_errors', 1);
    ini_set('error_reporting', E_ALL);
    require_once ROOT . "/resources/views/errors/503.php";
    return;
}
if (getenv('APP_ENV') === "development") {
    require_once ROOT . '/App/Providers/RouterProvider.php';
    runTroughtFolder(ROOT . '/App/Providers');
    runTroughtFolder(ROOT . '/App/Http');
    runTroughtFolder(ROOT . '/routers');
    runTroughtFolder(ROOT . '/App/Methods');
} else {
    try {
        require_once ROOT . '/App/Providers/RouterProvider.php';
        runTroughtFolder(ROOT . '/App/Providers');
        runTroughtFolder(ROOT . '/App/Http');
        runTroughtFolder(ROOT . '/routers');
        runTroughtFolder(ROOT . '/App/Methods');
    } catch (Exception $e) {
        echo $e->getMessage();
        require_once ROOT . '/resources/views/errors/500.php';
    }
}
