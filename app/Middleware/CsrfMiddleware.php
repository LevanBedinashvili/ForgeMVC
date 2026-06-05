<?php

namespace App\Middleware;

use Core\Csrf;
use Core\Middleware;

class CsrfMiddleware extends Middleware
{
    /**
     * Verify the CSRF token on state-changing requests.
     */
    public function handle(): void
    {
        Csrf::check();
    }
}
