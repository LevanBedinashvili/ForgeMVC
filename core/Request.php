<?php

declare(strict_types=1);

namespace Core;

class Request
{
    /**
     * Get the current HTTP method.
     * Includes support for Form Method Spoofing (detecting _method in POST requests).
     */
    public static function method(): string
    {
        $method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');

        // Form method spoofing
        if ($method === 'POST' && isset($_POST['_method'])) {
            $spoofedMethod = strtoupper($_POST['_method']);
            if (in_array($spoofedMethod, ['PUT', 'PATCH', 'DELETE'], true)) {
                return $spoofedMethod;
            }
        }

        return $method;
    }

    /**
     * Check if the request is a specific HTTP method.
     */
    public static function isMethod(string $method): bool
    {
        return self::method() === strtoupper($method);
    }

    /**
     * Get the current request URI without query strings.
     */
    public static function uri(): string
    {
        $uri = $_SERVER['REQUEST_URI'] ?? '/';
        $uri = parse_url($uri, PHP_URL_PATH);

        // Decode percent-encoded characters for consistent route matching
        $uri = rawurldecode($uri);

        // Strip null bytes (poison byte attack prevention)
        $uri = str_replace("\0", '', $uri);
        
        // Remove trailing slash if it's not the root
        if ($uri !== '/' && str_ends_with($uri, '/')) {
            $uri = rtrim($uri, '/');
            
            if (php_sapi_name() !== 'cli') {
                $query = $_SERVER['QUERY_STRING'] ?? '';
                $redirectUrl = $query ? $uri . '?' . $query : $uri;
                http_response_code(301);
                header('Location: ' . $redirectUrl);
                exit;
            }
        }

        return $uri;
    }

    /**
     * Get all request data (GET & POST combined).
     * POST takes precedence in this simple implementation.
     */
    public static function all(): array
    {
        $all = array_merge($_GET, $_POST);
        unset($all['_method'], $all['_csrf']);
        return $all;
    }

    /**
     * Get a specific input value from the request.
     */
    public static function input(string $key, mixed $default = null): mixed
    {
        $all = self::all();
        return $all[$key] ?? $default;
    }

    /**
     * Check if a specific input key exists in the request.
     */
    public static function has(string $key): bool
    {
        return array_key_exists($key, self::all());
    }

    /**
     * Retrieve only a specified subset of input data.
     * Extremely useful for massaging form arrays before Database insertion.
     */
    public static function only(array $keys): array
    {
        $all = self::all();
        $results = [];

        foreach ($keys as $key) {
            if (isset($all[$key])) {
                $results[$key] = $all[$key];
            }
        }

        return $results;
    }
}
