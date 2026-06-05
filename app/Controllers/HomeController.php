<?php

namespace App\Controllers;

use App\Greeter;
use Core\Controller;

class HomeController extends Controller
{
    protected Greeter $greeter;

    public function __construct(Greeter $greeter)
    {
        $this->greeter = $greeter;
    }

    /**
     * Display the home page.
     */
    public function index(): string
    {
        return $this->render('home/index', [
            'title' => 'Home - Mini-Laravel',
            'name'  => 'World',
        ]);
    }

    /**
     * Display a greeting page with a dynamic name.
     * Uses the auto-wired Greeter service.
     */
    public function show(string $name): string
    {
        $greeting = $this->greeter->greet($name);

        return $this->render('home/index', [
            'title' => 'Hello - Mini-Laravel',
            'name'  => $name,
            'greeting' => $greeting,
        ]);
    }
}
