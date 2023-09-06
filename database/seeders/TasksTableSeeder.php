<?php

namespace Database\Seeders;

use App\Models\Task;
use App\Models\User;
use Database\Factories\TaskFactory;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TasksTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::get();

        $users->each(function (User $user) use ($users) {
            Task::factory()
                ->count(5)
                ->for($user, 'creator')
                ->for($user, 'assignee')
                ->create();

            $createdBy = $users[array_rand($users->toArray())];
            Task::factory()
                ->count(5)
                ->for($createdBy, 'creator')
                ->for($user, 'assignee')
                ->create();
        });
    }
}
