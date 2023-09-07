<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function scopeCompleted($query)
    {
        return $query->where('completed', true);
    }

    public function scopeIncompleted($query)
    {
        return $query->where('completed', false);
    }

    public function scopeOverdue($query)
    {
        return $query->where('due_date', '<', now());
    }

    public function scopeAssignedToMe($query)
    {
        return $query->where('assigned_to', auth()->id());
    }

    public function scopeCreatedByMe($query)
    {
        return $query->where('created_by', auth()->id());
    }

    public function scopeAssignedToOthers($query)
    {
        return $query->where('assigned_to', '!=', auth()->id());
    }

    public function scopeCreatedByOthers($query)
    {
        return $query->where('created_by', '!=', auth()->id());
    }

    public function scopeOrderByDueDate($query)
    {
        return $query->orderBy('due_date');
    }

    public function scopeOrderByDueDateDesc($query)
    {
        return $query->orderByDesc('due_date');
    }

    public function scopeOrderByTitle($query)
    {
        return $query->orderBy('title');
    }

    public function scopeOrderByTitleDesc($query)
    {
        return $query->orderByDesc('title');
    }

    public function scopeOrderByCompleted($query)
    {
        return $query->orderBy('completed');
    }

    public function scopeOrderByCompletedDesc($query)
    {
        return $query->orderByDesc('completed');
    }

    public function scopeOrderByCreatedAt($query)
    {
        return $query->orderBy('created_at');
    }

    public function scopeOrderByCreatedAtDesc($query)
    {
        return $query->orderByDesc('created_at');
    }
}
