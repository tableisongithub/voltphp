<?php
try {
    require_once ROOT . '/App/Providers/RouterProvider.php';
} catch (Exception $e) {
    require_once 'errors/500.php';
}
