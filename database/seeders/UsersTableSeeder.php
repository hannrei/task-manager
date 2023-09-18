<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $userCount = app()->environment('testing') ? 10 : 100;

        $adminRole = Role::where('name', 'admin')->first();
        $userRole = Role::where('name', 'user')->first();

        User::factory($userCount)->create()->each(function (User $user) use ($userRole, $adminRole) {

            $user->roles()->attach($userRole->id);

            if (rand(1, 2) % 2 === 0) {
                $user->roles()->attach($adminRole->id);
            }
        });
    }
}
