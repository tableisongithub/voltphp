<?php
header("Server: VoltPHP");
header("X-Powered-By: Voltphp");
if (!file_exists(ROOT . "/.env")) {
    echo "Please create a .env file in the root directory";
    exit();
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
