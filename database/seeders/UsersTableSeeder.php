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
        $adminRole = Role::where('name', 'admin')->first();
        $userRole = Role::where('name', 'user')->first();

        User::factory(100)->create()->each(function (User $user) use ($userRole, $adminRole) {

            $user->roles()->attach($userRole->id);

            if (rand(1, 2) % 2 === 0) {
                $user->roles()->attach($adminRole->id);
            }
        });
    }
}
