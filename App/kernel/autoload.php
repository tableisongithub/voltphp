<?php
//Made by VoltPHP. Do not touch!
if (!defined(ROOT)) {
    die("You have to define the ROOT constant in your index.php file. Use it like this: define('ROOT', __DIR__ . '/../');");
}
require_once ROOT . '/App/dependencies.php';
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
if ($env["APP_KEY"] == "" || $env["APP_KEY"] == null) {
    $env["APP_KEY"] = bin2hex(random_bytes(32));
    $envFile = fopen(ROOT . "/.env", "w");
    $rebuildedEnv = "";
    foreach ($env as $key => $value) {
        $rebuildedEnv .= $key . " = " . $value . "\n";
    }
    fwrite($envFile, $rebuildedEnv);
    fclose($envFile);
}
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
