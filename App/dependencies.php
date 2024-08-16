<?php
//Made by VoltPHP. Do not touch!

if (version_compare(phpversion(), '8', '<')) {
    die("VoltPHP requires PHP 8 or higher.");
}
if (!extension_loaded('mysqli')) {
    trigger_error('The MySQLi extension is not loaded. This might not be a bug if you use something else.', E_WARNING);
}
if (!extension_loaded('PDO')) {
    trigger_error('The PDO extension is not loaded. This might not be a bug if you use something else.', E_WARNING);
}
if (!extension_loaded("apcu")) {
    trigger_error('The APCu extension is not loaded. You will not be able to use CSRF!.', E_WARNING);
}
