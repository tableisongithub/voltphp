<?php
if (version_compare(phpversion(), '8.1.0', '<')) {
    die("VoltPHP requires PHP 8.1 or higher.");
}
if (!defined('ROOT')) {
    define('ROOT', __DIR__ . '/../../');
}
error_reporting(E_ALL);
header("Server: VoltPHP");
header("X-Powered-By: VoltPHP");
spl_autoload_register(function ($class) {

    // and prepend the base directory
    $file = ROOT . str_replace('\\', '/', $class) . '.php';

    // If the file exists, require it
    if (file_exists($file)) {
        require_once $file;
    }
});
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
    runTroughtFolder(ROOT . '/App/Methods');
    runTroughtFolder(ROOT . '/App/Providers');
    runTroughtFolder(ROOT . '/App/Http');
    runTroughtFolder(ROOT . '/routers');
} else {
    try {
        runTroughtFolder(ROOT . '/App/Methods');
        runTroughtFolder(ROOT . '/App/Providers');
        runTroughtFolder(ROOT . '/App/Http');
        runTroughtFolder(ROOT . '/routers');
    } catch (Exception $e) {
        echo $e->getMessage();
        require_once ROOT . '/resources/views/errors/500.php';
    }
}
