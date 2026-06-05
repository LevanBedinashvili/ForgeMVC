<?php

declare(strict_types=1);

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use Core\Csrf;
use Core\Session;

class CsrfTest extends TestCase
{
    protected function setUp(): void
    {
        $_SESSION = [];
    }

    public function testGenerateToken()
    {
        $token = Csrf::generateToken();
        $this->assertNotEmpty($token);
        $this->assertEquals(64, strlen($token)); // hex encoded 32 bytes
        $this->assertEquals($token, Session::get('_csrf_token'));
    }

    public function testVerifyValidToken()
    {
        $token = Csrf::generateToken();
        $this->assertTrue(Csrf::verify($token));
    }

    public function testVerifyInvalidToken()
    {
        Csrf::generateToken();
        $this->assertFalse(Csrf::verify('invalid_token'));
    }

    public function testVerifyNullToken()
    {
        Csrf::generateToken();
        $this->assertFalse(Csrf::verify(null));
    }
}
