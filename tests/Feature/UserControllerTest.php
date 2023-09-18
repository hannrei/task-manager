<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class UserControllerTest extends TestCase
{
    private $userAdmin;
    private $userNotAdmin;

    public function setUp(): void
    {
        parent::setUp();
        $this->artisan('migrate:fresh --seed');

        $this->userAdmin = User::whereHas('roles', function ($query) {
            $query->where('name', 'admin');
        })->first();

        $this->userNotAdmin = User::whereDoesntHave('roles', function ($query) {
            $query->where('name', 'admin');
        })->first();
    }

    public function testIndexSuccess200()
    {
        $this->actingAs($this->userAdmin);

        $response = $this->getJson('/api/users');
        $response->assertStatus(200)->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'name',
                    'email',
                    'is_admin',
                ],
            ],
        ]);
    }

    public function testIndexUnauthenticated401()
    {
        $response = $this->getJson('/api/users');
        $response->assertStatus(401);
        $response->assertJson([
            'message' => 'Unauthenticated.',
        ]);
    }

    public function testIndexForbidden403()
    {
        $this->actingAs($this->userNotAdmin);

        $response = $this->getJson('/api/users');
        $response->assertStatus(403);
    }

    public function testShowAsAdminSuccess200()
    {
        $this->actingAs($this->userAdmin);

        $response = $this->getJson('/api/users/' . $this->userNotAdmin->id);

        $response->assertStatus(200)->assertJsonStructure([
            'data' => [
                'id',
                'name',
                'email',
                'is_admin',
            ],
        ]);

        $response->assertJson([
            'data' => [
                'id' => $this->userNotAdmin->id,
                'name' => $this->userNotAdmin->name,
                'email' => $this->userNotAdmin->email,
                'is_admin' => $this->userNotAdmin->is_admin,
            ],
        ]);
    }

    public function testShowAsNotAdminSuccess200()
    {
        $this->actingAs($this->userNotAdmin);

        $response = $this->getJson('/api/users/' . $this->userNotAdmin->id);

        $response->assertStatus(200)->assertJsonStructure([
            'data' => [
                'id',
                'name',
                'email',
                'is_admin',
            ],
        ]);

        $response->assertJson([
            'data' => [
                'id' => $this->userNotAdmin->id,
                'name' => $this->userNotAdmin->name,
                'email' => $this->userNotAdmin->email,
                'is_admin' => $this->userNotAdmin->is_admin,
            ],
        ]);
    }

    public function testShowUnauthenticated401()
    {
        $response = $this->getJson('/api/users/' . $this->userNotAdmin->id);
        $response->assertStatus(401);
        $response->assertJson([
            'message' => 'Unauthenticated.',
        ]);
    }

    public function testShowForbidden403()
    {
        $this->actingAs($this->userNotAdmin);

        $response = $this->getJson('/api/users/' . $this->userAdmin->id);
        $response->assertStatus(403);
    }

    public function testShowNotFound404()
    {
        $this->actingAs($this->userAdmin);

        $response = $this->getJson('/api/users/00000000-0000-0000-0000-000000000000');
        $response->assertStatus(404);
    }

    public function testUpdateAsAdminSuccess200()
    {
        $this->actingAs($this->userAdmin);

        $response = $this->putJson('/api/users/' . $this->userNotAdmin->id, [
            'name' => 'Jane Doe',
        ]);

        $response->assertStatus(200)->assertJson([
            'message' => 'User updated successfully.',
            'user' => [
                'id' => $this->userNotAdmin->id,
                'name' => 'Jane Doe',
                'email' => $this->userNotAdmin->email,
                'is_admin' => false,
            ],
        ]);

        $user = User::find($this->userNotAdmin->id);
        $this->assertEquals('Jane Doe', $user->name);
    }

    public function testUpdateAsNotAdminSuccess200()
    {
        $this->actingAs($this->userNotAdmin);

        $response = $this->putJson('/api/users/' . $this->userNotAdmin->id, [
            'name' => 'Jane Doe',
        ]);

        $response->assertStatus(200)->assertJson([
            'message' => 'User updated successfully.',
            'user' => [
                'id' => $this->userNotAdmin->id,
                'name' => 'Jane Doe',
                'email' => $this->userNotAdmin->email,
                'is_admin' => false,
            ],
        ]);

        $user = User::find($this->userNotAdmin->id);
        $this->assertEquals('Jane Doe', $user->name);
    }

    public function testUpdateUnauthenticated401()
    {
        $response = $this->putJson('/api/users/' . $this->userNotAdmin->id, [
            'name' => 'Jane Doe',
        ]);

        $response->assertStatus(401);
        $response->assertJson([
            'message' => 'Unauthenticated.',
        ]);
    }

    public function testUpdateForbidden403()
    {
        $this->actingAs($this->userNotAdmin);

        $response = $this->putJson('/api/users/' . $this->userAdmin->id, [
            'name' => 'Jane Doe',
        ]);

        $response->assertStatus(403);
    }

    public function testUpdateNotFound404()
    {
        $this->actingAs($this->userAdmin);

        $response = $this->putJson('/api/users/00000000-0000-0000-0000-000000000000', [
            'name' => 'Jane Doe',
        ]);

        $response->assertStatus(404);
    }

    public function testUpdateUnvalidData422()
    {
        $this->actingAs($this->userNotAdmin);

        $response = $this->putJson('/api/users/' . $this->userNotAdmin->id, [
            'password' => 'uv',
        ]);

        $response->assertStatus(422);
    }

    public function deleteAsAdminSuccess200()
    {
        $this->actingAs($this->userAdmin);

        $response = $this->deleteJson('/api/users/' . $this->userNotAdmin->id);

        $response->assertStatus(200)->assertJson([
            'message' => 'User deleted successfully.',
        ]);

        $user = User::find($this->userNotAdmin->id);
        $this->assertNull($user);
    }

    public function deleteAsNotAdminSuccess200()
    {
        $this->actingAs($this->userNotAdmin);

        $response = $this->deleteJson('/api/users/' . $this->userNotAdmin->id);

        $response->assertStatus(200)->assertJson([
            'message' => 'User deleted successfully.',
        ]);

        $user = User::find($this->userNotAdmin->id);
        $this->assertNull($user);
    }

    public function deleteUnauthenticated401()
    {
        $response = $this->deleteJson('/api/users/' . $this->userNotAdmin->id);

        $response->assertStatus(401);
        $response->assertJson([
            'message' => 'Unauthenticated.',
        ]);
    }

    public function deleteForbidden403()
    {
        $this->actingAs($this->userNotAdmin);

        $response = $this->deleteJson('/api/users/' . $this->userAdmin->id);

        $response->assertStatus(403);
    }

    public function deleteNotFound404()
    {
        $this->actingAs($this->userAdmin);

        $response = $this->deleteJson('/api/users/00000000-0000-0000-0000-000000000000');

        $response->assertStatus(404);
    }
}
