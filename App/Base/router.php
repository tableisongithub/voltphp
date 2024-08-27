<?php

namespace App\Base\router;

use Exception;

error_reporting(E_ALL & ~E_WARNING); // Suppress warnings for a cleaner output
// For debugging purposes:
// ini_set('display_errors', 1); // Enable error display
// ini_set('display_startup_errors', 1); // Enable startup errors display
// error_reporting(E_ALL); // Show all errors, including warnings

class Router
{
    /**
     * Array to hold all registered routes.
     *
     * @var array
     */
    private static array $routes = [];

    /**
     * The cache key used for storing routes in APCu.
     *
     * @var string
     */
    private static string $cacheKey = 'routes_cache';

    /**
     * Array to hold middleware functions.
     *
     * @var array
     */
    private static array $middleware = [];

    /**
     * Fallback function to be executed if no route matches.
     *
     * @var callable|null
     */
    private static $fallback = null;

    /**
     * Initializes the router by attempting to load cached routes from APCu.
     * If the routes are cached, the script will exit early.
     * If APCu is not available, it proceeds to route registration.
     *
     * @return bool Whether the cached routes were loaded and solved successfully.
     */
    public static function init(): bool
    {
        try {
            $cachedRoutes = apcu_fetch(self::$cacheKey);
            if ($cachedRoutes) {
                self::$routes = unserialize($cachedRoutes);
                self::executeRoutes();
                return true;
            }
        } catch (Exception $e) {
            // APCu is not available, proceed without caching
        }
        return false;
    }

    /**
     * Registers a GET route.
     *
     * @param string $uri The URI pattern for the route.
     * @param callable $callback The function to be executed for the route.
     * @return void
     */
    public static function GET(string $uri, callable $callback): void
    {
        self::addRoute('GET', $uri, $callback);
    }

    /**
     * Registers a POST route.
     *
     * @param string $uri The URI pattern for the route.
     * @param callable $callback The function to be executed for the route.
     * @return void
     */
    public static function POST(string $uri, callable $callback): void
    {
        self::addRoute('POST', $uri, $callback);
    }

    /**
     * Registers a PUT route.
     *
     * @param string $uri The URI pattern for the route.
     * @param callable $callback The function to be executed for the route.
     * @return void
     */
    public static function PUT(string $uri, callable $callback): void
    {
        self::addRoute('PUT', $uri, $callback);
    }

    /**
     * Registers a DELETE route.
     *
     * @param string $uri The URI pattern for the route.
     * @param callable $callback The function to be executed for the route.
     * @return void
     */
    public static function DELETE($uri, callable $callback)
    {
        self::addRoute('DELETE', $uri, $callback);
    }

    /**
     * Adds a route to the internal routes array.
     *
     * @param string $method The HTTP method for the route (GET, POST, etc.).
     * @param string $uri The URI pattern for the route.
     * @param callable $callback The function to be executed for the route.
     * @return void
     */
    private static function addRoute($method, $uri, callable $callback)
    {
        self::$routes[$method][$uri] = $callback;
    }

    /**
     * Registers a fallback function to be executed if no route matches.
     *
     * @param callable $callback The fallback function to be executed.
     * @return void
     */
    public static function fallback(callable $callback): void
    {
        self::$fallback = $callback;
    }

    /**
     * Executes the matched route based on the current request URI and method.
     * If the route is found, it is executed; otherwise, the fallback is handled.
     * After executing routes, they are cached in APCu if available.
     *
     * @return void
     */
    public static function executeRoutes(): void
    {
        $method = $_SERVER['REQUEST_METHOD'];
        $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

        foreach (self::$middleware as $middleware) {
            call_user_func($middleware);
        }

        $route = self::$routes[$method][$uri] ?? null;

        if ($route) {
            call_user_func($route);
        } elseif (self::$fallback) {
            call_user_func(self::$fallback);
        } else {
            require ROOT . "resources/views/errors/404.php";
        }

        try {
            apcu_store(self::$cacheKey, serialize(self::$routes));
        } catch (Exception $e) {
        }
    }

    /**
     * Registers middleware to be executed before any route.
     *
     * @param callable $middleware The middleware function to be executed.
     * @return void
     */
    public static function addMiddleware(callable $middleware): void
    {
        self::$middleware[] = $middleware;
    }


    /**
     * Clears the route cache stored in APCu.
     *
     * @return void
     */
    public static function clearCache(): void
    {
        try {
            apcu_delete(self::$cacheKey);
        } catch (Exception $e) {
            // Handle the case where APCu is not available
        }
    }
}
