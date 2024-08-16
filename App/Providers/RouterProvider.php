<?php
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
runTroughtFolder(ROOT . '/App/Http');
require_once ROOT . '/App/Methods/router.php';
require_once ROOT . '/routes/base.php';
require_once ROOT . '/App/Methods/assets.php';

Router::handleRequest();
