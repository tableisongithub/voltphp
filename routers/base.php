<?php
class test
{
    public function test()
    {
        return [
            'message' => 'Hello World'
        ];
    }
}

Router::get('/test', test::class, 'test');

Router::fallback('/', function ($path) {
    try {
        (new publicRender())->render($path);
    } catch (Exception $e) {
        try {
            if (!file_exists('../resources/views/layout.blade.php')) {
                throw new Exception("Error Processing Request", 1);
            }
            require_once '../resources/views/layout.blade.php';
        } catch (Exception $e) {
            require_once '../resources/views/errors/404.php';
        }
    }
});
