<?php
//Made by VoltPHP. Do not touch!
if (!defined(ROOT)) {
    die("You have to define the ROOT constant in your public/index.php file. Use it like this: define('ROOT', __DIR__ . '/../');");
}
if (empty($env)) {
    die("Please make sure to require App/kernel/autoload.php before this file.");
}
if ($env["DB_HOST"] == "" || $env["DB_PORT"] == "" || $env["DB_PASSWORD"] == "" || $env["DB_USER"] == "" || $env["DB_NAME"] == "") {
    die("Please make sure to fill in your database credentials in your .env file.");
}
require_once ROOT . "/App/Methods/db.php";

if (DB_EXTENSION === "mysqli") {
    $conn = new MysqliInstance(file_get_contents(ROOT . "/App/Schemas/security.sql"), [$env["DB_HOST"] . ":" . $env["DB_PORT"], $env["DB_USER"], $env["DB_PASS"], $env["DB_NAME"]]);
} elseif (DB_EXTENSION === "PDO") {
    $conn = new PDOInstance(file_get_contents(ROOT . "/App/Schemas/security.sql"), [$env["DB_HOST"] . ":" . $env["DB_PORT"], $env["DB_USER"], $env["DB_PASS"], $env["DB_NAME"]]);
}

