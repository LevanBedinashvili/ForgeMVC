<?php

namespace App;

class Greeter
{
    /**
     * Return a greeting message.
     */
    public function greet(string $name): string
    {
        return "Hello, " . htmlspecialchars($name, ENT_QUOTES, 'UTF-8') . "!";
    }
}
