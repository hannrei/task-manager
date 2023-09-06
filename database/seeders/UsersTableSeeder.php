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
        $attachableRolesIds = Role::pluck('id')->toArray();

        User::factory(1000)->create()->each(function (User $user) use ($attachableRolesIds) {
            $randomRoleNumer = rand(1, count($attachableRolesIds));
            $rolesToAttach = array_rand($attachableRolesIds, $randomRoleNumer);
            $rolesToAttach = is_array($rolesToAttach) ? $rolesToAttach : [$rolesToAttach];
            foreach ($rolesToAttach as $roleToAttach) {
                $user->roles()->attach($attachableRolesIds[$roleToAttach]);
            }
        });
    }
}
