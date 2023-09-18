<?php

namespace Tests\Feature;

use App\Http\Resources\TaskResource;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class TaskControllerTest extends TestCase
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

    public function testIndexAsAdminSuccess200()
    {
        $this->actingAs($this->userAdmin);

        $response = $this->getJson('/api/tasks');
        $response->assertStatus(200)->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'title',
                    'description',
                    'created_at',
                    'due_date',
                    'completed',
                    'created_by',
                    'assigned_to',
                    'file',
                ],
            ],
        ]);
    }

    public function testIndexAsNotAdminSuccess200()
    {
        $this->actingAs($this->userNotAdmin);

        $response = $this->getJson('/api/tasks');
        $response->assertStatus(200)->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'title',
                    'description',
                    'created_at',
                    'due_date',
                    'completed',
                    'created_by',
                    'assigned_to',
                    'file',
                ],
            ],
        ]);

        $tasks = $response->json('data');

        foreach ($tasks as $task) {
            $this->assertContains($this->userNotAdmin->id, [$task['assigned_to']['id'], $task['created_by']['id']]);
        }
    }

    public function testIndexUnauthenticated401()
    {
        $response = $this->getJson('/api/tasks');
        $response->assertStatus(401);
        $response->assertJson([
            'message' => 'Unauthenticated.',
        ]);
    }

    public function testStoreForMeSuccess201()
    {
        $this->actingAs($this->userNotAdmin);

        $response = $this->postJson('/api/tasks', [
            'title' => 'Task 1',
            'description' => 'Description 1',
            'due_date' => '2021-09-06',
            'assigned_to' => $this->userNotAdmin->id,
        ]);

        $response->assertStatus(201)->assertJsonStructure([
            'data' => [
                'id',
                'title',
                'description',
                'created_at',
                'due_date',
                'completed',
                'created_by',
                'assigned_to',
                'file',
            ],
        ]);

        $response->assertJson([
            'data' => [
                'title' => 'Task 1',
                'description' => 'Description 1',
                'due_date' => '2021-09-06',
                'completed' => false,
                'created_by' => [
                    'id' => $this->userNotAdmin->id,
                ],
                'assigned_to' => [
                    'id' => $this->userNotAdmin->id,
                ],
            ],
        ]);
    }

    public function testStoreForOtherSuccess201()
    {
        $this->actingAs($this->userAdmin);

        $response = $this->postJson('/api/tasks', [
            'title' => 'Task 1',
            'description' => 'Description 1',
            'due_date' => '2021-09-06',
            'assigned_to' => $this->userNotAdmin->id,
        ]);

        $response->assertStatus(201)->assertJsonStructure([
            'data' => [
                'id',
                'title',
                'description',
                'created_at',
                'due_date',
                'completed',
                'created_by',
                'assigned_to',
                'file',
            ],
        ]);

        $response->assertJson([
            'data' => [
                'title' => 'Task 1',
                'description' => 'Description 1',
                'due_date' => '2021-09-06',
                'completed' => false,
                'created_by' => [
                    'id' => $this->userAdmin->id,
                ],
                'assigned_to' => [
                    'id' => $this->userNotAdmin->id,
                ],
            ],
        ]);
    }

    public function testStoreUnauthenticated401()
    {
        $response = $this->postJson('/api/tasks', [
            'title' => 'Task 1',
            'description' => 'Description 1',
            'due_date' => '2021-09-06',
            'assigned_to' => $this->userNotAdmin->id,
        ]);

        $response->assertStatus(401);
        $response->assertJson([
            'message' => 'Unauthenticated.',
        ]);
    }

    public function testStoreInvalidData422()
    {
        $this->actingAs($this->userNotAdmin);

        $response = $this->postJson('/api/tasks', [
            'title' => '',
            'description' => 'Description 1',
            'due_date' => '2021-09-06',
            'assigned_to' => $this->userNotAdmin->id,
        ]);

        $response->assertStatus(422);
        $response->assertJson([
            'message' => 'The title field is required.',
            'errors' => [
                'title' => [
                    'The title field is required.',
                ],
            ],
        ]);
    }

    public function testShowAsAdminSuccess200()
    {
        $this->actingAs($this->userAdmin);

        $task = $this->userNotAdmin->tasks()->first();

        $response = $this->getJson('/api/tasks/' . $task->id);

        $response->assertStatus(200)->assertJsonStructure([
            'data' => [
                'id',
                'title',
                'description',
                'created_at',
                'due_date',
                'completed',
                'created_by',
                'assigned_to',
                'file',
            ],
        ]);
    }

    public function testShowAsNotAdminSuccess200()
    {
        $this->actingAs($this->userNotAdmin);

        $task = $this->userNotAdmin->tasks()->first();

        $response = $this->getJson('/api/tasks/' . $task->id);

        $response->assertStatus(200)->assertJsonStructure([
            'data' => [
                'id',
                'title',
                'description',
                'created_at',
                'due_date',
                'completed',
                'created_by',
                'assigned_to',
                'file',
            ],
        ]);
    }

    public function testShowUnauthenticated401()
    {
        $task = $this->userNotAdmin->tasks()->first();

        $response = $this->getJson('/api/tasks/' . $task->id);

        $response->assertStatus(401);
        $response->assertJson([
            'message' => 'Unauthenticated.',
        ]);
    }

    public function testShowForbidden403()
    {
        $this->actingAs($this->userNotAdmin);

        $task = $this->userAdmin->tasks()
            ->where('created_by', '!=', $this->userNotAdmin->id)
            ->where('assigned_to', '!=', $this->userNotAdmin->id)
            ->first();

        $response = $this->getJson('/api/tasks/' . $task->id);

        $response->assertStatus(403);
    }

    public function testShowNotFound404()
    {
        $this->actingAs($this->userNotAdmin);

        $response = $this->getJson('/api/tasks/123');

        $response->assertStatus(404);
        $response->assertJson([
            'message' => 'No query results for model [App\Models\Task] 123',
        ]);
    }

    public function testUpdateAsAdminSuccess200()
    {
        $this->actingAs($this->userAdmin);

        $task = $this->userNotAdmin->tasks()->first();

        $response = $this->putJson('/api/tasks/' . $task->id, [
            'title' => 'New Title',
        ]);

        $response->assertStatus(200)->assertJsonStructure([
            'data' => [
                'id',
                'title',
                'description',
                'created_at',
                'due_date',
                'completed',
                'created_by',
                'assigned_to',
                'file',
            ],
        ]);

        $response->assertJson([
            'data' => [
                'title' => 'New Title',
                'description' => $task->description,
                'due_date' => $task->due_date,
                'completed' => $task->completed,
                'created_by' => [
                    'id' => $task->created_by,
                ],
                'assigned_to' => [
                    'id' => $task->assigned_to,
                ],
            ],
        ]);
    }

    public function testUpdateAsNotAdminSuccess200()
    {
        $this->actingAs($this->userNotAdmin);

        $task = $this->userNotAdmin->tasks()
            ->where('created_by', $this->userNotAdmin->id)
            ->first();

        $response = $this->putJson('/api/tasks/' . $task->id, [
            'title' => 'New Title',
        ]);

        $response->assertStatus(200)->assertJsonStructure([
            'data' => [
                'id',
                'title',
                'description',
                'created_at',
                'due_date',
                'completed',
                'created_by',
                'assigned_to',
                'file',
            ],
        ]);

        $response->assertJson([
            'data' => [
                'title' => 'New Title',
                'description' => $task->description,
                'due_date' => $task->due_date,
                'completed' => $task->completed,
                'created_by' => [
                    'id' => $task->created_by,
                ],
                'assigned_to' => [
                    'id' => $task->assigned_to,
                ],
            ],
        ]);
    }

    public function testUpdateUnauthenticated401()
    {
        $task = $this->userNotAdmin->tasks()->first();

        $response = $this->putJson('/api/tasks/' . $task->id, [
            'title' => 'New Title',
        ]);

        $response->assertStatus(401);
        $response->assertJson([
            'message' => 'Unauthenticated.',
        ]);
    }

    public function testUpdateForbidden403()
    {
        $this->actingAs($this->userNotAdmin);

        $task = $this->userNotAdmin->tasks()
            ->where('created_by', '!=', $this->userNotAdmin->id)
            ->first();

        $response = $this->putJson('/api/tasks/' . $task->id, [
            'title' => 'New Title',
        ]);

        $response->assertStatus(403);
    }

    public function testUpdateNotFound404()
    {
        $this->actingAs($this->userAdmin);

        $response = $this->putJson('/api/tasks/123', [
            'title' => 'New Title',
        ]);

        $response->assertStatus(404);
    }

    public function testUpdateInvalidInput422()
    {
        $this->actingAs($this->userAdmin);

        $task = $this->userNotAdmin->tasks()->first();

        $response = $this->putJson('/api/tasks/' . $task->id, [
            'title' => '',
        ]);

        $response->assertStatus(422);
    }

    public function testDeleteAsAdminSuccess200()
    {
        $this->actingAs($this->userAdmin);

        $task = $this->userNotAdmin->tasks()->first();

        $response = $this->deleteJson('/api/tasks/' . $task->id);

        $response->assertStatus(200)->assertJsonStructure([
            'message',
        ]);
    }

    public function testDeleteAsNotAdminSuccess200()
    {
        $this->actingAs($this->userNotAdmin);

        $task = $this->userNotAdmin->tasks()->where('created_by', $this->userNotAdmin->id)->first();

        $response = $this->deleteJson('/api/tasks/' . $task->id);

        $response->assertStatus(200)->assertJsonStructure([
            'message',
        ]);
    }

    public function testDeleteUnauthenticated401()
    {
        $task = $this->userNotAdmin->tasks()->first();

        $response = $this->deleteJson('/api/tasks/' . $task->id);

        $response->assertStatus(401);
        $response->assertJson([
            'message' => 'Unauthenticated.',
        ]);
    }

    public function testDeleteForbidden403()
    {
        $this->actingAs($this->userNotAdmin);

        $task = $this->userNotAdmin->tasks()
            ->where('created_by', '!=', $this->userNotAdmin->id)
            ->first();

        $response = $this->deleteJson('/api/tasks/' . $task->id);

        $response->assertStatus(403);
    }

    public function testDeleteNotFound404()
    {
        $this->actingAs($this->userNotAdmin);

        $response = $this->deleteJson('/api/tasks/123');

        $response->assertStatus(404);
    }

    public function testCompleteAsAdminSuccess200()
    {
        $this->actingAs($this->userAdmin);

        $task = $this->userNotAdmin->tasks()->first();

        $response = $this->putJson('/api/tasks/' . $task->id . '/complete');

        $response->assertStatus(200)->assertJsonStructure([
            'data' => [
                'id',
                'title',
                'description',
                'created_at',
                'due_date',
                'completed',
                'created_by',
                'assigned_to',
                'file',
            ],
        ]);

        $response->assertJson([
            'data' => [
                'title' => $task->title,
                'description' => $task->description,
                'due_date' => $task->due_date,
                'completed' => true,
                'created_by' => [
                    'id' => $task->created_by,
                ],
                'assigned_to' => [
                    'id' => $task->assigned_to,
                ],
            ],
        ]);
    }

    public function testCompleteAsNotAdminSuccess200()
    {
        $this->actingAs($this->userNotAdmin);

        $task = $this->userNotAdmin->tasks()->where('created_by', $this->userNotAdmin->id)->first();

        $response = $this->putJson('/api/tasks/' . $task->id . '/complete');

        $response->assertStatus(200)->assertJsonStructure([
            'data' => [
                'id',
                'title',
                'description',
                'created_at',
                'due_date',
                'completed',
                'created_by',
                'assigned_to',
                'file',
            ],
        ]);

        $response->assertJson([
            'data' => [
                'title' => $task->title,
                'description' => $task->description,
                'due_date' => $task->due_date,
                'completed' => true,
                'created_by' => [
                    'id' => $task->created_by,
                ],
                'assigned_to' => [
                    'id' => $task->assigned_to,
                ],
            ],
        ]);
    }

    public function testCompleteUnauthenticated401()
    {
        $task = $this->userNotAdmin->tasks()->first();

        $response = $this->putJson('/api/tasks/' . $task->id . '/complete');

        $response->assertStatus(401);
        $response->assertJson([
            'message' => 'Unauthenticated.',
        ]);
    }

    public function testCompleteForbidden403()
    {
        $this->actingAs($this->userNotAdmin);

        $task = $this->userAdmin->tasks()
            ->where('created_by', '!=', $this->userNotAdmin->id)
            ->first();

        $response = $this->putJson('/api/tasks/' . $task->id . '/complete');

        $response->assertStatus(403);
    }

    public function testCompleteNotFound404()
    {
        $this->actingAs($this->userNotAdmin);

        $response = $this->putJson('/api/tasks/123/complete');

        $response->assertStatus(404);
    }
}
