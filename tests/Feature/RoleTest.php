<?php

namespace Tests\Feature;

use App\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class RoleTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();
        $this->artisan('migrate:fresh --seed');
    }

    public function testSeeder()
    {
        $this->assertDatabaseCount('roles', 2);
        $this->assertDatabaseHas('roles', [
            'name' => 'admin',
        ]);
        $this->assertDatabaseHas('roles', [
            'name' => 'user',
        ]);
    }

    public function testRelationships()
    {
        $role = Role::first();

        $this->assertInstanceOf('Illuminate\Database\Eloquent\Collection', $role->users);
    }
}
