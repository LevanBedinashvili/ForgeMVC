<?php

declare(strict_types=1);

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use Core\Router;

class RouterTest extends TestCase
{
    public function testStaticRouteResolution()
    {
        Router::get('/phpunit-static-route', function () {
            return 'static-success';
        });

        $response = Router::resolve('/phpunit-static-route', 'GET');
        $this->assertEquals('static-success', $response);
    }

    public function testDynamicRouteResolution()
    {
        Router::get('/user/{id}', function ($id) {
            return "User {$id}";
        });

        $response = Router::resolve('/user/42', 'GET');
        $this->assertEquals('User 42', $response);
    }

    public function testRouteNotFoundThrowsException()
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionCode(404);

        Router::resolve('/non-existent-route', 'GET');
    }
}
