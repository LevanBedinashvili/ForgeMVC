<?php

/**
 * ForgeMVC - A lightweight custom PHP framework
 * 
 * @package  ForgeMVC
 */

declare(strict_types=1);

// Prevent session-based timing/hijacking attacks (Security hardening)
ini_set('session.cookie_httponly', '1'); // Prevents JavaScript from reading the session cookie
ini_set('session.use_only_cookies', '1'); // Prevents passing Session IDs in URLs
ini_set('session.cookie_samesite', 'Lax'); // Basic CSRF protection for initial navigation
ini_set('session.use_strict_mode', '1'); // Prevents session fixation by rejecting uninitialized Session IDs array

// Error Reporting (Security: Do not leak stack traces to the browser in production)
// In a real framework, this would be toggled by an .env variable (e.g., APP_DEBUG=true)
ini_set('display_errors', '0');
ini_set('log_errors', '1');

// Define the root path of the application
define('BASE_PATH', dirname(__DIR__) . '/');

// Register the Composer Autoloader
require BASE_PATH . 'vendor/autoload.php';

// Load environment configuration
Core\Config::load(BASE_PATH . '.env');

// Boot the session
Core\Session::start();

// Generate CSRF token for this session
Core\Csrf::generateToken();

// Load route definitions
require BASE_PATH . 'routes/web.php';

// Resolve the current request
try {
    $uri = Core\Request::uri();
    $method = Core\Request::method();

    // Enforce CSRF protection globally for state-changing requests
    Core\Csrf::check();

    // Enforce Secure HTTP Headers
    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: SAMEORIGIN');
    header('X-XSS-Protection: 1; mode=block');

    $response = Core\Router::resolve($uri, $method);

    echo $response;
} catch (\RuntimeException $e) {
    $code = $e->getCode();
    if ($code === 404) {
        http_response_code(404);
        require BASE_PATH . 'views/errors/404.php';
    } elseif ($code === 403) {
        http_response_code(403);
        require BASE_PATH . 'views/errors/403.php';
    } else {
        http_response_code(500);
        require BASE_PATH . 'views/errors/500.php';
        \Core\Log::error($e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
    }
} catch (\Throwable $e) {
    http_response_code(500);
    require BASE_PATH . 'views/errors/500.php';
    \Core\Log::error($e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
}
