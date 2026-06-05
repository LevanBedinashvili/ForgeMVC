<?php

declare(strict_types=1);

if (!function_exists('e')) {
    /**
     * Escape HTML special characters in a string.
     *
     * @param string|null $value
     * @return string
     */
    function e(?string $value): string
    {
        if ($value === null) {
            return '';
        }
        return htmlspecialchars($value, ENT_QUOTES, 'UTF-8', false);
    }
}
