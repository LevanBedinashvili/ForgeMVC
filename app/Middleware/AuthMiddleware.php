<?php

namespace App\Middleware;

use Core\Middleware;
use Core\Session;

class AuthMiddleware extends Middleware
{
    /**
     * Ensure the user is authenticated via session.
     */
    public function handle(): void
    {
        if (!Session::has('user')) {
            throw new \RuntimeException('Unauthorized. Please log in.', 403);
        }
    }
}
