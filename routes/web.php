<?php

use Core\Router;

/**
 * Web Routes
 *
 * Define all application routes here. Each route maps a URI pattern
 * to a Closure or a [Controller::class, 'method'] array.
 */

// Basic closure route
Router::get('/', function () {
    return 'Welcome to Mini-Laravel (ForgeMVC)!';
});

// Route with a dynamic parameter
Router::get('/hello/{name}', function (string $name) {
    $safe = htmlspecialchars($name, ENT_QUOTES, 'UTF-8');
    return "Hello, {$safe}!";
});

// Test POST route
Router::post('/submit', function () {
    return 'Form submitted successfully.';
});

// Controller-based routes
Router::get('/home', [App\Controllers\HomeController::class, 'index']);
Router::get('/home/{name}', [App\Controllers\HomeController::class, 'show']);
