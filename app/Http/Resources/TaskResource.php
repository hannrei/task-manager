<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

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
        ];
    }
}
