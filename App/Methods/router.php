<?php
error_reporting(E_ALL & ~E_WARNING);
class Route
{
    private $method;
    private $uri;

    public function __construct($method, $uri)
    {
        $this->method = $method;
        $this->uri = $uri;
    }

    public function name($prefix)
    {
        Router::$prefixedRoutes[$prefix] = [
            'method' => $this->method,
            'uri' => $this->uri
        ];
    }
}

class FunctionClass
{
    private $name;
    private $function;

    public function name($name)
    {
        $this->name = $name;
        return $this;
    }

    public function function($function)
    {
        $this->function = $function;
        return $this;
    }

    public function register()
    {
        Router::$functions[$this->name] = $this->function;
    }
}

class Middleware
{
    private $funcs;

    public function __construct($funcs)
    {
        $this->funcs = $funcs;
    }

    public function group($callbackfunc)
    {
        foreach ($this->funcs as $func) {
            try {
                Router::invokeByPrefix($func);
            } catch (Exception $e) {
                return;
            }
        }
        call_user_func($callbackfunc);
    }
}

class Router
{
    private static $routes = [];
    public static $prefixedRoutes = [];
    public static $middleware = [];
    public static $functions = [];
    private static $fallback = null;

    private static function registerRoute($method, $uri, $class, $function): Route
    {
        self::$routes[$method][$uri] = compact('class', 'function');
        return new Route($method, $uri);
    }

    public static function GET($uri, $class, $function): Route
    {
        return self::registerRoute('GET', $uri, $class, $function);
    }
    public static function POST($uri, $class, $function): Route
    {
        return self::registerRoute('POST', $uri, $class, $function);
    }
    public static function PUT($uri, $class, $function): Route
    {
        return self::registerRoute('PUT', $uri, $class, $function);
    }
    public static function OPTIONS($uri, $class, $function): Route
    {
        return self::registerRoute('OPTIONS', $uri, $class, $function);
    }
    public static function DELETE($uri, $class, $function): Route
    {
        return self::registerRoute('DELETE', $uri, $class, $function);
    }
    public static function HEAD($uri, $class, $function): Route
    {
        return self::registerRoute('HEAD', $uri, $class, $function);
    }
    public static function PATCH($uri, $class, $function): Route
    {
        return self::registerRoute('PATCH', $uri, $class, $function);
    }
    public static function CONNECT($uri, $class, $function): Route
    {
        return self::registerRoute('CONNECT', $uri, $class, $function);
    }
    public static function TRACE($uri, $class, $function): Route
    {
        return self::registerRoute('TRACE', $uri, $class, $function);
    }

    public static function fallback($path, $function)
    {
        self::$fallback = compact('path', 'function');
    }

    public static function middleware($funcs)
    {
        return new Middleware($funcs);
    }

    public static function functionRegister()
    {
        return new FunctionClass();
    }

    public static function handleRequest()
    {
        $method = $_SERVER['REQUEST_METHOD'];
        $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $route = self::$routes[$method][$uri] ?? self::matchWildcardRoute($method, $uri);

        if ($route) {
            self::executeRoute($route);
        } elseif (self::$fallback) {
            call_user_func(self::$fallback['function'], self::$fallback['path']);
        } else {
            require_once 'errors/404.php';
        }
    }

    private static function matchWildcardRoute($method, $uri)
    {
        foreach (self::$routes[$method] as $routeUri => $route) {
            if (strpos($routeUri, '*') !== false) {
                $pattern = str_replace('*', '.*', $routeUri);
                if (preg_match("#^$pattern$#", $uri)) {
                    return $route;
                }
            }
        }
        return null;
    }

    private static function executeRoute($route)
    {
        try {
            header('Content-Type: application/json');
            echo json_encode(self::invoke($route['class'], $route['function']));
        } catch (Exception $e) {
            require_once 'errors/500.php';
        }
    }

    private static function invoke($class, $function)
    {
        if (class_exists($class)) {
            $obj = new $class();
            if (method_exists($obj, $function)) {
                return $obj->$function();
            }
        }
        require_once 'errors/500.php';
    }

    public static function invokeByPrefix($prefix)
    {
        if (isset(self::$functions[$prefix])) {
            return call_user_func(self::$functions[$prefix]);
        }

        $prefixedRoute = self::$prefixedRoutes[$prefix] ?? null;
        if ($prefixedRoute) {
            $method = $prefixedRoute['method'];
            $uri = $prefixedRoute['uri'];
            $route = self::$routes[$method][$uri] ?? self::matchWildcardRoute($method, $uri);

            if ($route) {
                return self::invoke($route['class'], $route['function']);
            }
        }

        require_once $prefixedRoute ? 'errors/500.php' : 'errors/404.php';
    }
}
