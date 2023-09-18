<?php

namespace Tests\Feature;

use App\Http\Resources\TaskResource;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class TaskResourceTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();
        $this->artisan('migrate:fresh --seed');
    }

    public function testToArray()
    {
        $user = User::first();

        $task = $user->tasks()->first();

        $taskResource = new TaskResource($task);

        $this->assertJsonStringEqualsJsonString(
            json_encode([
                'id' => $task->id,
                'title' => $task->title,
                'description' => $task->description,
                'created_at' => $task->created_at,
                'due_date' => $task->due_date,
                'completed' => $task->completed,
                'created_by' => [
                    'id' => $task->creator->id,
                    'name' => $task->creator->name,
                    'email' => $task->creator->email,
                    'is_admin' => $task->creator->isAdmin(),
                ],
                'assigned_to' => [
                    'id' => $task->assignee->id,
                    'name' => $task->assignee->name,
                    'email' => $task->assignee->email,
                    'is_admin' => $task->assignee->isAdmin(),
                ],
                'file' => null,
            ]),
            $taskResource->toJson()
        );
    }
}
