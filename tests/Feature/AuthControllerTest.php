<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthControllerTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();
        $this->artisan('migrate:fresh --seed');
    }

    public function testRegisterSuccess201()
    {
        $response = $this->postJson('/api/auth/register', [
            'name' => 'John Doe',
            'email' => 'john.doe@email.com',
            'password' => 'password',
        ]);

        $response->assertStatus(201)
            ->assertJson([
                'message' => 'An email has been sent to your email address. Please verify.',
            ]);
    }

    public function testRegisterInvalidData422()
    {
        $response = $this->postJson('/api/auth/register', [
            'name' => 'John Doe',
            'email' => 'john.doe',
            'password' => 'password',
        ]);

        $response->assertStatus(422)
            ->assertJsonStructure(
                [
                    'message',
                    'errors',
                ]
            );
    }

    public function testLoginSuccess200()
    {
        $user = User::first();

        $response = $this->postJson('/api/auth/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'message',
                'user',
                'authorization' => [
                    'type',
                    'token',
                ],
            ]);
    }

    public function testLoginUnauthorized401()
    {
        $response = $this->postJson('/api/auth/login', [
            'email' => 'john.doe@email.com',
            'password' => 'wrong_password',
        ]);

        $response->assertStatus(401)
            ->assertJson([
                'message' => 'Unauthorized',
            ]);
    }

    public function testRefreshSuccess200()
    {
        $user = User::first();

        $response = $this->postJson('/api/auth/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $token = $response->json('authorization.token');

        $response = $this->postJson('/api/auth/refresh', [], [
            'Authorization' => 'Bearer ' . $token,
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'user',
                'authorization' => [
                    'type',
                    'token',
                ]
            ]);
    }

    public function testRefreshUnauthenticated401()
    {
        $response = $this->postJson('/api/auth/refresh');

        $response->assertStatus(401)
            ->assertJson([
                'message' => 'Unauthenticated.',
            ]);
    }

    public function testLogoutSuccess200()
    {
        $user = User::first();

        $response = $this->postJson('/api/auth/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $token = $response->json('authorization.token');

        $response = $this->postJson('/api/auth/logout', [], [
            'Authorization' => 'Bearer ' . $token,
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Successfully logged out',
            ]);
    }

    public function testLogoutUnauthenticated401()
    {
        $response = $this->postJson('/api/auth/logout');

        $response->assertStatus(401)
            ->assertJson([
                'message' => 'Unauthenticated.',
            ]);
    }
}
