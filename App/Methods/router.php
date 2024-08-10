<?php
error_reporting(E_ALL & ~E_WARNING);
// for debugging
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);
class Route
{
    private $method;
    private $uri;

    public function __construct($method, $uri)
    {
        $this->method = $method;
        $this->uri = $uri;
    }
    /**
     * Add prefix to the route so that it can be accessed by the prefix in middleware function
     * 
     * @param string $prefix The prefix to be added to the route
     * @return void
     */
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

    /**
     * Adds a name to the defined function
     * @param string $name The name of the function
     * @return FunctionClass
     */
    public function name($name)
    {
        $this->name = $name;
        return $this;
    }
    /**
     * Adds a function to the defined function
     * @param string $function The function to be added
     * @return FunctionClass
     */
    public function function($function)
    {
        $this->function = $function;
        return $this;
    }
    /**
     * Registers the function to the Router
     * @return void
     */
    public function register()
    {
        Router::$functions[$this->name] = $this->function;
    }
}

class Middleware
{
    private $funcs;
    /**
     * Middleware constructor
     * @param array $funcs The middleware functions prefix to be executed
     */
    public function __construct($funcs)
    {
        $this->funcs = $funcs;
    }
    /**
     * Function for code that will be executed only if the middleware functions are successful
     * @param callable $callbackfunc The callback function that contains the routes
     * @return void
     */
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
    private static $handlingRequest = false;
    private static $fallback = null;
    /**
     * Registers a route to the Router
     * @param string $method The HTTP method of the route to be registered (GET, POST, PUT, OPTIONS, DELETE, HEAD, PATCH, CONNECT, TRACE)
     * @param string $uri The URI of the route to be registered (e.g. /home)
     * @param string $class The class that contains the function to be executed when the route is accessed
     * @param string $function The function to be executed when the route is accessed
     * @return Route
     */
    private static function registerRoute($method, $uri, $class, $function): Route
    {
        self::$routes[$method][$uri] = compact('class', 'function');
        return new Route($method, $uri);
    }
    /**
     * Registers a GET route to the Router
     * @param string $uri The URI of the route to be registered (e.g. /home)
     * @param string $class The class that contains the function to be executed when the route is accessed
     * @param string $function The function to be executed when the route is accessed
     * @return Route
     */
    public static function GET($uri, $class, $function): Route
    {
        return self::registerRoute('GET', $uri, $class, $function);
    }
    /**
     * Registers a POST route to the Router
     * @param string $uri The URI of the route to be registered (e.g. /home)
     * @param string $class The class that contains the function to be executed when the route is accessed
     * @param string $function The function to be executed when the route is accessed
     * @return Route
     */
    public static function POST($uri, $class, $function): Route
    {
        return self::registerRoute('POST', $uri, $class, $function);
    }
    /**
     * Registers a PUT route to the Router
     * @param string $uri The URI of the route to be registered (e.g. /home)
     * @param string $class The class that contains the function to be executed when the route is accessed
     * @param string $function The function to be executed when the route is accessed
     * @return Route
     */
    public static function PUT($uri, $class, $function): Route
    {
        return self::registerRoute('PUT', $uri, $class, $function);
    }
    /**
     * Registers a OPTIONS route to the Router
     * @param string $uri The URI of the route to be registered (e.g. /home)
     * @param string $class The class that contains the function to be executed when the route is accessed
     * @param string $function The function to be executed when the route is accessed
     * @return Route
     */
    public static function OPTIONS($uri, $class, $function): Route
    {
        return self::registerRoute('OPTIONS', $uri, $class, $function);
    }
    /**
     * Registers a DELETE route to the Router
     * @param string $uri The URI of the route to be registered (e.g. /home)
     * @param string $class The class that contains the function to be executed when the route is accessed
     * @param string $function The function to be executed when the route is accessed
     * @return Route
     */
    public static function DELETE($uri, $class, $function): Route
    {
        return self::registerRoute('DELETE', $uri, $class, $function);
    }
    /**
     * Registers a HEAD route to the Router
     * @param string $uri The URI of the route to be registered (e.g. /home)
     * @param string $class The class that contains the function to be executed when the route is accessed
     * @param string $function The function to be executed when the route is accessed
     * @return Route
     */
    public static function HEAD($uri, $class, $function): Route
    {
        return self::registerRoute('HEAD', $uri, $class, $function);
    }
    /**
     * Registers a PATCH route to the Router
     * @param string $uri The URI of the route to be registered (e.g. /home)
     * @param string $class The class that contains the function to be executed when the route is accessed
     * @param string $function The function to be executed when the route is accessed
     * @return Route
     */
    public static function PATCH($uri, $class, $function): Route
    {
        return self::registerRoute('PATCH', $uri, $class, $function);
    }
    /**
     * Registers a CONNECT route to the Router
     * @param string $uri The URI of the route to be registered (e.g. /home)
     * @param string $class The class that contains the function to be executed when the route is accessed
     * @param string $function The function to be executed when the route is accessed
     * @return Route
     */
    public static function CONNECT($uri, $class, $function): Route
    {
        return self::registerRoute('CONNECT', $uri, $class, $function);
    }
    /**
     * Registers a TRACE route to the Router
     * @param string $uri The URI of the route to be registered (e.g. /home)
     * @param string $class The class that contains the function to be executed when the route is accessed
     * @param string $function The function to be executed when the route is accessed
     * @return Route
     */
    public static function TRACE($uri, $class, $function): Route
    {
        return self::registerRoute('TRACE', $uri, $class, $function);
    }
    /**
     * Registers a fallback route to the Router
     * @param string $path The path of the fallback route
     * @param callable $function The function to be executed when the fallback route is accessed
     * @return void
     */

    public static function fallback($path, $function)
    {
        self::$fallback = compact('path', 'function');
    }

    /**
     * Registers a middleware to the Router
     * @param array $funcs The middleware functions prefix to be executed
     * @return Middleware
     */
    public static function middleware($funcs)
    {
        return new Middleware($funcs);
    }
    /**
     * Registers a function to the Router
     * @return FunctionClass
     */
    public static function functionRegister()
    {
        return new FunctionClass();
    }
    /**
     * Handles the request and executes the route. If the route is not found, it will execute the fallback route. If you want to disable auto handling of the request, set Router::$handleRequestOnExit to false
     * @return void
     */
    public static function handleRequest()
    {
        if (self::$handlingRequest) {
            return;
        }
        self::$handlingRequest = true;
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
    /**
     * Matches a wildcard route
     * @param string $method The HTTP method of the route to be matched
     * @param string $uri The URI of the route to be matched
     * @return array|null
     */
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
    /**
     * Executes the route
     * @param array $route The route to be executed
     * @return void
     */
    private static function executeRoute($route)
    {
        try {
            header('Content-Type: application/json');
            echo json_encode(self::invoke($route['class'], $route['function']));
        } catch (Exception $e) {
            require_once 'errors/500.php';
        }
    }
    /**
     * Invokes the function of the class
     * @param string $class The class that contains the function to be executed
     * @param string $function The function to be executed
     * @return mixed
     */
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
    /**
     * Invokes the function by prefix
     * @param string $prefix The prefix of the function to be executed
     * @return mixed
     */
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
