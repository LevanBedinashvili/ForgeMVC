<?php

declare(strict_types=1);

namespace Core;

class Router
{
    /**
     * Registered static route definitions.
     * Structure: ['GET' => ['/uri' => ['action' => ..., 'middleware' => ...]], ...]
     */
    protected static array $staticRoutes = [];

    /**
     * Registered dynamic route definitions.
     * Structure: ['GET' => [['uri' => '...', 'action' => ..., 'middleware' => [...]], ...]]
     */
    protected static array $dynamicRoutes = [];

    /**
     * Register a GET route.
     */
    public static function get(string $uri, mixed $action, array $middleware = []): void
    {
        static::addRoute('GET', $uri, $action, $middleware);
    }

    /**
     * Register a POST route.
     */
    public static function post(string $uri, mixed $action, array $middleware = []): void
    {
        static::addRoute('POST', $uri, $action, $middleware);
    }

    /**
     * Register a PUT route.
     */
    public static function put(string $uri, mixed $action, array $middleware = []): void
    {
        static::addRoute('PUT', $uri, $action, $middleware);
    }

    /**
     * Register a DELETE route.
     */
    public static function delete(string $uri, mixed $action, array $middleware = []): void
    {
        static::addRoute('DELETE', $uri, $action, $middleware);
    }

    /**
     * Store a route definition.
     */
    protected static function addRoute(string $method, string $uri, mixed $action, array $middleware): void
    {
        if (!str_contains($uri, '{')) {
            static::$staticRoutes[$method][$uri] = [
                'action'     => $action,
                'middleware' => $middleware,
            ];
            return;
        }

        static::$dynamicRoutes[$method][] = [
            'uri'        => $uri,
            'action'     => $action,
            'middleware' => $middleware,
        ];
    }

    /**
     * Resolve the current request against registered routes.
     */
    public static function resolve(string $uri, string $method): mixed
    {
        // 1. Fast static route matching
        if (isset(static::$staticRoutes[$method][$uri])) {
            $route = static::$staticRoutes[$method][$uri];
            return static::dispatch($route['action'], $route['middleware'], []);
        }

        // 2. Fallback to dynamic route regex matching
        $dynamicRoutes = static::$dynamicRoutes[$method] ?? [];
        foreach ($dynamicRoutes as $route) {
            $pattern = preg_replace_callback('#\{([a-zA-Z_]+)(?::([^}]+))?\}#', function ($matches) {
                $name = $matches[1];
                $regex = $matches[2] ?? '[^/]+';
                return '(?P<' . $name . '>' . $regex . ')';
            }, $route['uri']);
            $pattern = '#^' . $pattern . '$#';

            if (preg_match($pattern, $uri, $matches)) {
                $params = array_filter($matches, fn($key) => !is_int($key), ARRAY_FILTER_USE_KEY);
                return static::dispatch($route['action'], $route['middleware'], $params);
            }
        }

        throw new \RuntimeException('404 Not Found', 404);
    }

    /**
     * Dispatch a matched route.
     */
    protected static function dispatch(mixed $action, array $middleware, array $params): mixed
    {
        // Run middleware before dispatching
        foreach ($middleware as $middlewareClass) {
            $middlewareInstance = new $middlewareClass();
            $middlewareInstance->handle();
        }

        if ($action instanceof \Closure) {
            return call_user_func_array($action, array_values($params));
        }

        if (is_array($action) && count($action) === 2) {
            [$controllerClass, $methodName] = $action;
            $controller = Container::resolve($controllerClass);
            return call_user_func_array([$controller, $methodName], array_values($params));
        }

        throw new \RuntimeException('Invalid route action.', 500);
    }

    /**
     * Get all registered routes (useful for debugging).
     */
    public static function routes(): array
    {
        return [
            'static' => static::$staticRoutes,
            'dynamic' => static::$dynamicRoutes,
        ];
    }
}
