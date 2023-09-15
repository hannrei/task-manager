<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @OA\Schema(
 *   schema="Task",
 *   title="Task",
 *   description="Task model",
 *   @OA\Property(
 *     property="id",
 *     title="id",
 *     description="Task id",
 *     format="uuid",
 *     example="123e4567-e89b-12d3-a456-426614174000"
 *   ),
 *   @OA\Property(
 *     property="created_by",
 *     title="created_by",
 *     description="User id of the creator",
 *     format="uuid",
 *     type="string",
 *     example="123e4567-e89b-12d3-a456-426614174000"
 *   ),
 *   @OA\Property(
 *     property="assigned_to",
 *     title="assigned_to",
 *     description="User id of the assignee",
 *     format="uuid",
 *     type="string",
 *     example="123e4567-e89b-12d3-a456-426614174000"
 *   ),
 *   @OA\Property(
 *     property="title",
 *     title="title",
 *     description="Task title",
 *     type="string",
 *     example="Task title"
 *   ),
 *   @OA\Property(
 *     property="description",
 *     title="description",
 *     description="Task description",
 *     type="string",
 *     example="Task description"
 *   ),
 *   @OA\Property(
 *     property="due_date",
 *     title="due_date",
 *     description="Task due date",
 *     type="string",
 *     example="2021-01-01 00:00:00"
 *   ),
 *   @OA\Property(
 *     property="completed",
 *     title="completed",
 *     description="Task completed",
 *     type="boolean",
 *     example="true"
 *   ),
 *   @OA\Property(
 *     property="created_at",
 *     title="created_at",
 *     description="The created at date",
 *     type="string",
 *     example="2021-01-01 00:00:00"
 *   ),
 *   @OA\Property(
 *     property="updated_at",
 *     title="updated_at",
 *     description="The updated at date",
 *     type="string",
 *     example="2021-01-01 00:00:00"
 *   )
 * )
 */

class Task extends Model
{
    use HasFactory, HasUuids;



    protected $fillable = [
        'title',
        'description',
        'assigned_to',
        'created_by',
        'due_date',
        'completed',
    ];

    /**
     * Get the creator of the task.
     *
     * @return BelongsTo
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the assignee of the task.
     *
     * @return BelongsTo
     */
    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    /**
     * Scope a query to only include completed tasks.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeCompleted($query): Builder
    {
        return $query->where('completed', true);
    }

    /**
     * Scope a query to only include incompleted tasks.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeIncompleted($query): Builder
    {
        return $query->where('completed', false);
    }

    /**
     * Scope a query to only include overdue tasks.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOverdue($query): Builder
    {
        return $query->where('due_date', '<', now());
    }

    /**
     * Scope a query to only include tasks assigned to the current user.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeAssignedToMe($query): Builder
    {
        return $query->where('assigned_to', auth()->id());
    }

    /**
     * Scope a query to only include tasks created by the current user.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeCreatedByMe($query): Builder
    {
        return $query->where('created_by', auth()->id());
    }

    /**
     * Scope a query to only include tasks assigned to others.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeAssignedToOthers($query): Builder
    {
        return $query->where('assigned_to', '!=', auth()->id());
    }

    /**
     * Scope a query to only include tasks created by others.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeCreatedByOthers($query): Builder
    {
        return $query->where('created_by', '!=', auth()->id());
    }

    /**
     * Order the tasks by due date.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOrderByDueDate($query): Builder
    {
        return $query->orderBy('due_date');
    }

    /**
     * Order the tasks by due date descending.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOrderByDueDateDesc($query): Builder
    {
        return $query->orderByDesc('due_date');
    }

    /**
     * Order the tasks by title.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOrderByTitle($query): Builder
    {
        return $query->orderBy('title');
    }

    /**
     * Order the tasks by title descending.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOrderByTitleDesc($query): Builder
    {
        return $query->orderByDesc('title');
    }

    /**
     * Order the tasks by completed first.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOrderByCompleted($query): Builder
    {
        return $query->orderBy('completed');
    }

    /**
     * Order the tasks by incompleted first.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOrderByCompletedDesc($query): Builder
    {
        return $query->orderByDesc('completed');
    }

    /**
     * Order the tasks by created at date.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOrderByCreatedAt($query): Builder
    {
        return $query->orderBy('created_at');
    }

    /**
     * Order the tasks by created at date descending.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOrderByCreatedAtDesc($query): Builder
    {
        return $query->orderByDesc('created_at');
    }
}
