<?php

declare(strict_types=1);

namespace Core;

class Session
{
    /**
     * Start the session if it hasn't been started already.
     */
    public static function start(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_set_cookie_params([
                'lifetime' => 0,
                'path'     => '/',
                'domain'   => '',
                'secure'   => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on',
                'httponly' => true,
                'samesite' => 'Lax'
            ]);
            session_start();
            
            // Age flash messages
            $_SESSION['_flash_old'] = $_SESSION['_flash_new'] ?? [];
            $_SESSION['_flash_new'] = [];
        }
    }

    /**
     * Regenerate the session ID securely to prevent Session Fixation attacks.
     * Call this when a user logs in, logs out, or changes privilege levels.
     */
    public static function regenerate(bool $deleteOldSession = true): bool
    {
        return session_regenerate_id($deleteOldSession);
    }

    /**
     * Set a session key/value pair.
     */
    public static function set(string $key, mixed $value): void
    {
        $_SESSION[$key] = $value;
    }

    /**
     * Get a session value by key, with an optional default.
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        return $_SESSION[$key] ?? $default;
    }

    /**
     * Check if a session key exists.
     */
    public static function has(string $key): bool
    {
        return isset($_SESSION[$key]);
    }

    /**
     * Remove a specific session key.
     */
    public static function remove(string $key): void
    {
        unset($_SESSION[$key]);
    }

    /**
     * Destroy the entire session.
     */
    public static function destroy(): void
    {
        $_SESSION = [];
        session_destroy();
        
        $params = session_get_cookie_params();
        setcookie(
            session_name(),
            '',
            time() - 42000,
            $params['path'],
            $params['domain'],
            $params['secure'],
            $params['httponly']
        );
    }

    /**
     * Set a flash message (available for the next request only).
     */
    public static function flash(string $key, mixed $value): void
    {
        $_SESSION['_flash_new'][$key] = $value;
    }

    /**
     * Get a flash message (available until the end of the next request).
     */
    public static function getFlash(string $key, mixed $default = null): mixed
    {
        if (isset($_SESSION['_flash_new'][$key])) {
            return $_SESSION['_flash_new'][$key];
        }

        if (isset($_SESSION['_flash_old'][$key])) {
            return $_SESSION['_flash_old'][$key];
        }

        return $default;
    }

    /**
     * Check if a flash message exists.
     */
    public static function hasFlash(string $key): bool
    {
        return isset($_SESSION['_flash_new'][$key]) || isset($_SESSION['_flash_old'][$key]);
    }
}
