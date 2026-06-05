<?php

declare(strict_types=1);

namespace Core;

abstract class Middleware
{
    /**
     * Handle the middleware logic.
     *
     * Should throw a RuntimeException (or similar) to halt the request
     * if the check fails. Otherwise, return void to continue.
     */
    abstract public function handle(): void;
}
