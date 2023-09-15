<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;


/**
 * @OA\Schema(
 *     schema="TaskResource",
 *     type="object",
 *     title="TaskResource",
 *     @OA\Property(property="id", type="integer", example="1"),
 *     @OA\Property(property="title", type="string", example="Task title"),
 *     @OA\Property(property="description", type="string", example="Task description"),
 *     @OA\Property(property="due_date", type="string", format="date", example="2021-01-01"),
 *     @OA\Property(property="completed", type="boolean", example="false"),
 *     @OA\Property(property="created_by", type="object", ref="#/components/schemas/UserResource"),
 *     @OA\Property(property="assigned_to", type="object", ref="#/components/schemas/UserResource"),
 *     @OA\Property(property="file", type="string", format="binary", example="http://localhost:8000/api/tasks/1/file"),
 *     @OA\Property(property="created_at", type="string", format="date-time", example="2021-01-01T00:00:00.000000Z"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", example="2021-01-01T00:00:00.000000Z"),
 * )
 */
class TaskResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => (string) $this->description,
            'due_date' => $this->due_date,
            'completed' => $this->completed,
            'created_by' => $this->whenLoaded('creator') ? new UserResource($this->creator) : $this->created_by,
            'assigned_to' => $this->whenLoaded('assignee') ? new UserResource($this->assignee) : $this->assigned_to,
            'file' => Storage::exists('users/' . $this->assignee->id . '/'. 'tasks/' . $this->id . '.pdf') ? route('tasks.file', $this->id) : null,
        ];
    }
}
