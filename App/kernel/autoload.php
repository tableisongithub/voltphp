<?php
if (version_compare(phpversion(), '8', '<')) {
    die("VoltPHP requires PHP 8 or higher.");
}
error_reporting(E_ALL);
header("Server: VoltPHP");
header("X-Powered-By: VoltPHP");
if (!file_exists(ROOT . "/.env")) {
    trigger_error("No.env file found. Creating empty... Please fill it.", E_WARNING);
    $envFile = fopen(ROOT . "/.env", "w");
    fwrite($envFile, '
DB_HOST =
DB_PART =
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
    require_once ROOT . "/resources/views/errors/503.php";
    return;
}
if (getenv('APP_ENV') === "development") {
    require_once ROOT . '/App/Providers/RouterProvider.php';
} else {
    try {
        require_once ROOT . '/App/Providers/RouterProvider.php';
    } catch (Exception $e) {
        echo $e->getMessage();
        require_once ROOT . '/resources/views/errors/500.php';
    }
}
