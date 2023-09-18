<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class UserTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();
        $this->artisan('migrate:fresh --seed');
    }

    public function testUserMassAssignment()
    {
        $user = User::create([
            'name' => 'John Doe',
            'email' => 'john.doe@email.com',
            'password' => Hash::make('password'),
            ]);

        $userFromDb = User::find($user->id);

        $this->assertEquals($user->name, $userFromDb->name);
        $this->assertEquals($user->email, $userFromDb->email);
        $this->assertEquals($user->password, $userFromDb->password);
    }

    public function testHiddenFields()
    {
        $user = User::first();

        $this->assertArrayHasKey('id', $user->toArray());
        $this->assertArrayNotHasKey('password', $user->toArray());
        $this->assertArrayNotHasKey('remember_token', $user->toArray());
    }

    public function testUserRelationships()
    {
        $user = User::first();

        $this->assertInstanceOf('Illuminate\Database\Eloquent\Collection', $user->roles);
        $this->assertInstanceOf('App\Models\Role', $user->roles->first());
        $this->assertInstanceOf('Illuminate\Database\Eloquent\Collection', $user->tasks);
        $this->assertInstanceOf('App\Models\Task', $user->tasks->first());
        $this->assertInstanceOf('Illuminate\Database\Eloquent\Collection', $user->tasksCreated);
        $this->assertInstanceOf('App\Models\Task', $user->tasksCreated->first());
    }

    public function testHasRole()
    {
        $adminRole = Role::where('name', 'admin')->first();
        $usersRole = Role::where('name', 'user')->first();

        $userWithAdminRole = User::whereHas('roles', function ($query) use ($adminRole) {
            $query->where('role_id', $adminRole->id);
        })->first();

        $userWithUsersRoleNotAdminRole = User::whereHas('roles', function ($query) use ($usersRole) {
            $query->where('role_id', $usersRole->id);
        })->whereDoesntHave('roles', function ($query) use ($adminRole) {
            $query->where('role_id', $adminRole->id);
        })->first();

        $this->assertTrue($userWithAdminRole->hasRole('user'));
        $this->assertTrue($userWithAdminRole->hasRole('admin'));

        $this->assertTrue($userWithUsersRoleNotAdminRole->hasRole('user'));
        $this->assertFalse($userWithUsersRoleNotAdminRole->hasRole('admin'));
    }

    public function testIsAdmin()
    {
        $adminRole = Role::where('name', 'admin')->first();
        $usersRole = Role::where('name', 'user')->first();

        $userWithAdminRole = User::whereHas('roles', function ($query) use ($adminRole) {
            $query->where('role_id', $adminRole->id);
        })->first();

        $userWithUsersRoleNotAdminRole = User::whereHas('roles', function ($query) use ($usersRole) {
            $query->where('role_id', $usersRole->id);
        })->whereDoesntHave('roles', function ($query) use ($adminRole) {
            $query->where('role_id', $adminRole->id);
        })->first();

        $this->assertTrue($userWithAdminRole->isAdmin());
        $this->assertFalse($userWithUsersRoleNotAdminRole->isAdmin());
    }

    public function testIsUser()
    {
        $adminRole = Role::where('name', 'admin')->first();
        $usersRole = Role::where('name', 'user')->first();

        $userWithAdminRole = User::whereHas('roles', function ($query) use ($adminRole) {
            $query->where('role_id', $adminRole->id);
        })->first();

        $userWithUsersRoleNotAdminRole = User::whereHas('roles', function ($query) use ($usersRole) {
            $query->where('role_id', $usersRole->id);
        })->whereDoesntHave('roles', function ($query) use ($adminRole) {
            $query->where('role_id', $adminRole->id);
        })->first();

        $this->assertTrue($userWithAdminRole->isUser());
        $this->assertTrue($userWithUsersRoleNotAdminRole->isUser());
    }
}
