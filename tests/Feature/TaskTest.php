<?php

namespace Tests\Feature;

use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Carbon\Carbon;

class TaskTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();
        $this->artisan('migrate:fresh --seed');
    }

    public function testMassAssignment()
    {
        $user = User::first();

        $task = Task::create([
            'title' => 'Test Task',
            'description' => 'Test Description',
            'assigned_to' => $user->id,
            'created_by' => $user->id,
            'due_date' => now()->addDays(7),
            'completed' => false,
        ]);

        $taskFromDb = Task::find($task->id);

        $this->assertEquals($task->title, $taskFromDb->title);
        $this->assertEquals($task->description, $taskFromDb->description);
        $this->assertEquals($task->assigned_to, $taskFromDb->assigned_to);
        $this->assertEquals($task->created_by, $taskFromDb->created_by);
        $this->assertEquals($task->due_date, $taskFromDb->due_date);
        $this->assertEquals($task->completed, $taskFromDb->completed);
    }

    public function testRelationships()
    {
        $task = Task::first();

        $this->assertInstanceOf('App\Models\User', $task->creator);
        $this->assertInstanceOf('App\Models\User', $task->assignee);
    }

    public function testScopeCompleted()
    {
        $user = User::first();

        $task = Task::create([
            'title' => 'Test Task',
            'description' => 'Test Description',
            'assigned_to' => $user->id,
            'created_by' => $user->id,
            'due_date' => now()->addDays(7),
            'completed' => true,
        ]);

        $completedTasks = $user->tasks()->completed()->get();
        foreach ($completedTasks as $task) {
            $this->assertEquals($task->completed, true);
        }
    }

    public function testScopeIncompleted()
    {
        $user = User::first();

        $task = Task::create([
            'title' => 'Test Task',
            'description' => 'Test Description',
            'assigned_to' => $user->id,
            'created_by' => $user->id,
            'due_date' => now()->addDays(7),
            'completed' => false,
        ]);

        $incompletedTasks = $user->tasks()->incompleted()->get();

        foreach ($incompletedTasks as $task) {
            $this->assertEquals($task->completed, false);
        }
    }

    public function testScopeOverdue()
    {
        $user = User::first();

        $task = Task::create([
            'title' => 'Test Task',
            'description' => 'Test Description',
            'assigned_to' => $user->id,
            'created_by' => $user->id,
            'due_date' => now()->subDays(7),
            'completed' => false,
        ]);

        $overdueTasks = $user->tasks()->overdue()->get();

        foreach ($overdueTasks as $task) {
            $this->assertTrue(Carbon::parse($task->due_date)->isPast());
        }
    }

    public function testScopeAssignedToMe()
    {
        $user = User::first();
        $this->actingAs($user);

        $tasks = $user->tasks()->assignedToMe()->get();

        foreach ($tasks as $task) {
            $this->assertEquals($task->assigned_to, $user->id);
        }
    }

    public function testScopeCreatedByMe()
    {
        $user = User::first();
        $this->actingAs($user);

        $tasks = $user->tasks()->createdByMe()->get();

        foreach ($tasks as $task) {
            $this->assertEquals($task->created_by, $user->id);
        }
    }

    public function testScopeAssignedToOthers()
    {
        $user = User::first();
        $this->actingAs($user);

        $tasks = Task::assignedToOthers()->get();

        foreach ($tasks as $task) {
            $this->assertNotEquals($task->assigned_to, $user->id);
        }
    }

    public function testScopeCreatedByOthers()
    {
        $user = User::first();
        $this->actingAs($user);

        $tasks = Task::createdByOthers($user->id)->get();

        foreach ($tasks as $task) {
            $this->assertNotEquals($task->created_by, $user->id);
        }
    }

    public function testScopeOrderByDueDate()
    {
        $user = User::first();

        $tasks = $user->tasks()->orderByDueDate()->get();

        for ($i = 0; $i < count($tasks) - 1; $i++) {
            $this->assertTrue(Carbon::parse($tasks[$i]->due_date)->lessThanOrEqualTo(Carbon::parse($tasks[$i + 1]->due_date)));
        }
    }

    public function testScopeOrderByDueDateDesc()
    {
        $user = User::first();

        $tasks = $user->tasks()->orderByDueDateDesc()->get();

        for ($i = 0; $i < count($tasks) - 1; $i++) {
            $this->assertTrue(Carbon::parse($tasks[$i]->due_date)->greaterThanOrEqualTo(Carbon::parse($tasks[$i + 1]->due_date)));
        }
    }

    public function testScopeOrderByTitle()
    {
        $user = User::first();

        $tasks = $user->tasks()->orderByTitle()->get();

        for ($i = 0; $i < count($tasks) - 1; $i++) {
            $this->assertTrue($tasks[$i]->title <= $tasks[$i + 1]->title);
        }
    }

    public function testScopeOrderByTitleDesc()
    {
        $user = User::first();

        $tasks = $user->tasks()->orderByTitleDesc()->get();

        for ($i = 0; $i < count($tasks) - 1; $i++) {
            $this->assertTrue($tasks[$i]->title >= $tasks[$i + 1]->title);
        }
    }

    public function testScopeOrderByCompleted()
    {
        $user = User::first();

        $tasks = $user->tasks()->orderByCompleted()->get();

        for ($i = 0; $i < count($tasks) - 1; $i++) {
            $this->assertTrue($tasks[$i]->completed <= $tasks[$i + 1]->completed);
        }
    }

    public function testScopeOrderByCompletedDesc()
    {
        $user = User::first();

        $tasks = $user->tasks()->orderByCompletedDesc()->get();

        for ($i = 0; $i < count($tasks) - 1; $i++) {
            $this->assertTrue($tasks[$i]->completed >= $tasks[$i + 1]->completed);
        }
    }

    public function testScopeOrderByCreatedAt()
    {
        $user = User::first();

        $tasks = $user->tasks()->orderByCreatedAt()->get();

        for ($i = 0; $i < count($tasks) - 1; $i++) {
            $this->assertTrue(Carbon::parse($tasks[$i]->created_at)->lessThanOrEqualTo(Carbon::parse($tasks[$i + 1]->created_at)));
        }
    }

    public function testScopeOrderByCreatedAtDesc()
    {
        $user = User::first();

        $tasks = $user->tasks()->orderByCreatedAtDesc()->get();

        for ($i = 0; $i < count($tasks) - 1; $i++) {
            $this->assertTrue(Carbon::parse($tasks[$i]->created_at)->greaterThanOrEqualTo(Carbon::parse($tasks[$i + 1]->created_at)));
        }
    }
}
