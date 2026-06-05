<?php

declare(strict_types=1);

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use Core\Model;

class DummyModel extends Model
{
    protected array $fillable = ['name', 'email'];
}

class ModelTest extends TestCase
{
    public function testFillableAttributes()
    {
        $model = new DummyModel();
        $model->fill([
            'name' => 'John',
            'email' => 'john@example.com',
            'is_admin' => 1 // Should be ignored because it is not fillable
        ]);

        $this->assertEquals('John', $model->name);
        $this->assertEquals('john@example.com', $model->email);
        $this->assertNull($model->is_admin);
    }

    public function testToArray()
    {
        $model = new DummyModel();
        $model->name = 'Jane';
        $model->email = 'jane@example.com';

        $array = $model->toArray();

        $this->assertArrayHasKey('name', $array);
        $this->assertEquals('Jane', $array['name']);
        $this->assertArrayHasKey('email', $array);
        $this->assertEquals('jane@example.com', $array['email']);
    }
}
