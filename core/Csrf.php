<?php

declare(strict_types=1);

namespace Core;

class Csrf
{
    /**
     * Session key used to store the CSRF token.
     */
    protected const TOKEN_KEY = '_csrf_token';
    protected const TOKEN_TIME_KEY = '_csrf_token_time';
    protected const TOKEN_LIFETIME = 7200; // 2 hours

    /**
     * Generate a new CSRF token and store it in the session.
     * If a token already exists, it is reused (one token per session).
     */
    public static function generateToken(): string
    {
        $time = Session::get(self::TOKEN_TIME_KEY, 0);
        if (!Session::has(self::TOKEN_KEY) || (time() - $time > self::TOKEN_LIFETIME)) {
            $token = bin2hex(random_bytes(32));
            Session::set(self::TOKEN_KEY, $token);
            Session::set(self::TOKEN_TIME_KEY, time());
        }

        return Session::get(self::TOKEN_KEY);
    }

    /**
     * Get the current CSRF token.
     */
    public static function token(): ?string
    {
        return Session::get(self::TOKEN_KEY);
    }

    /**
     * Output a hidden HTML input field containing the CSRF token.
     * Use this inside forms: <?= Core\Csrf::field() ?>
     */
    public static function field(): string
    {
        $token = static::generateToken();
        return '<input type="hidden" name="_csrf" value="' . htmlspecialchars($token, ENT_QUOTES, 'UTF-8') . '">';
    }

    /**
     * Verify that the submitted _csrf token matches the session token.
     */
    public static function verify(?string $submittedToken): bool
    {
        $storedToken = static::token();

        if ($storedToken === null || $submittedToken === null) {
            return false;
        }

        return hash_equals($storedToken, $submittedToken);
    }

    /**
     * Check the current request for a valid CSRF token.
     * Should be called on state-changing methods (POST, PUT, PATCH, DELETE).
     *
     * Throws a RuntimeException on failure.
     */
    public static function check(): void
    {
        $method = Request::method();

        // Only verify on state-changing requests
        if (!in_array($method, ['POST', 'PUT', 'PATCH', 'DELETE'], true)) {
            return;
        }

        $submittedToken = Request::input('_csrf');

        if (!static::verify($submittedToken)) {
            throw new \RuntimeException('CSRF token validation failed.', 403);
        }
    }

    /**
     * Regenerate the CSRF token (call after successful form submission if desired).
     */
    public static function regenerate(): string
    {
        Session::remove(self::TOKEN_KEY);
        Session::remove(self::TOKEN_TIME_KEY);
        return static::generateToken();
    }
}
