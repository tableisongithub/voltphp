<?php

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
     * Assigns a prefix to the route, allowing middleware to identify and use it.
     *
     * @param string $prefix The prefix to be assigned to the route
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
     * Sets the name for the function to be registered.
     *
     * @param string $name The name to be assigned to the function
     * @return FunctionClass
     */
    public function name($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * Sets the callable function to be registered.
     *
     * @param callable $function The function to be registered
     * @return FunctionClass
     */
    public function function ($function)
    {
        $this->function = $function;
        return $this;
    }

    /**
     * Registers the function with the Router using the defined name.
     *
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
     * Middleware constructor to initialize the middleware functions.
     *
     * @param array $funcs An array of function prefixes that will be executed as middleware
     */
    public function __construct($funcs)
    {
        $this->funcs = $funcs;
    }

    /**
     * Executes the middleware functions in sequence and then runs the provided callback function.
     *
     * @param callable $callbackfunc The function to be executed if all middleware functions succeed
     * @return void
     */
    public function group($callbackfunc)
    {
        foreach ($this->funcs as $func) {
            try {
                Router::invokeByPrefix($func);
            } catch (Exception $e) {
                // If any middleware function fails, stop processing and return
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
     * Registers a route with the specified HTTP method, URI, class, and function.
     *
     * @param string $method The HTTP method for the route (e.g., GET, POST)
     * @param string $uri The URI pattern for the route
     * @param string $class The class containing the function to be executed
     * @param string $function The function to be executed
     * @return Route
     */
    private static function registerRoute($method, $uri, $class, $function): Route
    {
        self::$routes[$method][$uri] = compact('class', 'function');
        return new Route($method, $uri);
    }

    /**
     * Registers a GET route.
     *
     * @param string $uri The URI pattern for the route
     * @param string $class The class containing the function to be executed
     * @param string $function The function to be executed
     * @return Route
     */
    public static function GET($uri, $class, $function): Route
    {
        return self::registerRoute('GET', $uri, $class, $function);
    }

    /**
     * Registers a POST route.
     *
     * @param string $uri The URI pattern for the route
     * @param string $class The class containing the function to be executed
     * @param string $function The function to be executed
     * @return Route
     */
    public static function POST($uri, $class, $function): Route
    {
        return self::registerRoute('POST', $uri, $class, $function);
    }

    /**
     * Registers a PUT route.
     *
     * @param string $uri The URI pattern for the route
     * @param string $class The class containing the function to be executed
     * @param string $function The function to be executed
     * @return Route
     */
    public static function PUT($uri, $class, $function): Route
    {
        return self::registerRoute('PUT', $uri, $class, $function);
    }

    /**
     * Registers an OPTIONS route.
     *
     * @param string $uri The URI pattern for the route
     * @param string $class The class containing the function to be executed
     * @param string $function The function to be executed
     * @return Route
     */
    public static function OPTIONS($uri, $class, $function): Route
    {
        return self::registerRoute('OPTIONS', $uri, $class, $function);
    }

    /**
     * Registers a DELETE route.
     *
     * @param string $uri The URI pattern for the route
     * @param string $class The class containing the function to be executed
     * @param string $function The function to be executed
     * @return Route
     */
    public static function DELETE($uri, $class, $function): Route
    {
        return self::registerRoute('DELETE', $uri, $class, $function);
    }

    /**
     * Registers a HEAD route.
     *
     * @param string $uri The URI pattern for the route
     * @param string $class The class containing the function to be executed
     * @param string $function The function to be executed
     * @return Route
     */
    public static function HEAD($uri, $class, $function): Route
    {
        return self::registerRoute('HEAD', $uri, $class, $function);
    }

    /**
     * Registers a PATCH route.
     *
     * @param string $uri The URI pattern for the route
     * @param string $class The class containing the function to be executed
     * @param string $function The function to be executed
     * @return Route
     */
    public static function PATCH($uri, $class, $function): Route
    {
        return self::registerRoute('PATCH', $uri, $class, $function);
    }

    /**
     * Registers a CONNECT route.
     *
     * @param string $uri The URI pattern for the route
     * @param string $class The class containing the function to be executed
     * @param string $function The function to be executed
     * @return Route
     */
    public static function CONNECT($uri, $class, $function): Route
    {
        return self::registerRoute('CONNECT', $uri, $class, $function);
    }

    /**
     * Registers a TRACE route.
     *
     * @param string $uri The URI pattern for the route
     * @param string $class The class containing the function to be executed
     * @param string $function The function to be executed
     * @return Route
     */
    public static function TRACE($uri, $class, $function): Route
    {
        return self::registerRoute('TRACE', $uri, $class, $function);
    }

    /**
     * Sets a fallback route to be executed when no other routes match.
     *
     * @param string $path The path for the fallback route
     * @param callable $function The function to be executed for the fallback route
     * @return void
     */
    public static function fallback($path, $function)
    {
        self::$fallback = compact('path', 'function');
    }

    /**
     * Creates a Middleware instance with the given function prefixes.
     *
     * @param array $funcs An array of function prefixes to be used as middleware
     * @return Middleware
     */
    public static function middleware($funcs)
    {
        return new Middleware($funcs);
    }

    /**
     * Creates a FunctionClass instance for registering functions with the Router.
     *
     * @return FunctionClass
     */
    public static function functionRegister()
    {
        return new FunctionClass();
    }

    /**
     * Handles the incoming request, executes the matching route, or falls back to the fallback route if no match is found.
     * To disable automatic request handling, set Router::$handleRequestOnExit to false.
     *
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
            require_once 'errors/404.php'; // Load a 404 error page if no route or fallback is found
        }
    }

    /**
     * Matches a route with wildcard patterns to the incoming URI.
     *
     * @param string $method The HTTP method of the route
     * @param string $uri The URI of the incoming request
     * @return array|null The matched route or null if no match is found
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
     * Executes the route by calling the specified class and function.
     *
     * @param array $route The route containing class and function to be executed
     * @return void
     */
    private static function executeRoute($route)
    {
        try {
            header('Content-Type: application/json');
            echo json_encode(self::invoke($route['class'], $route['function']));
        } catch (Exception $e) {
            require_once 'errors/500.php'; // Load a 500 error page if an exception occurs
        }
    }

    /**
     * Instantiates the class and invokes the specified function.
     *
     * @param string $class The class containing the function to be executed
     * @param string $function The function to be invoked
     * @return mixed The result of the function execution
     */
    private static function invoke($class, $function)
    {
        if (class_exists($class)) {
            $obj = new $class();
            if (method_exists($obj, $function)) {
                return $obj->$function();
            }
        }
        require_once 'errors/500.php'; // Load a 500 error page if class or method does not exist
    }

    /**
     * Invokes a function based on its prefix, checking for prefixed routes if needed.
     *
     * @param string $prefix The prefix of the function to be executed
     * @return mixed The result of the function execution
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

        require_once $prefixedRoute ? 'errors/500.php' : 'errors/404.php'; // Load error pages as appropriate
    }
}
