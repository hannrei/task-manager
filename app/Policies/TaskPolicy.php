<?php

namespace App\Policies;

use App\Models\Task;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class TaskPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): Response
    {
        return $user->isUser()
            ? Response::allow()
            : Response::deny('You are not authorized to view tasks.');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Task $task): Response
    {
        return $user->isAdmin()
            ? Response::allow()
            : ($user->id === $task->creator->id|| $user->id === $task->assignee->id
                ? Response::allow()
                : Response::deny('You are not authorized to view this task.'));
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): Response
    {
        return $user->isUser()
            ? Response::allow()
            : Response::deny('You are not authorized to create tasks.');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Task $task): Response
    {
        return $user->isAdmin()
            ? Response::allow()
            : ($user->id === $task->creator->id
                ? Response::allow()
                : Response::deny('You are not authorized to update this task.'));
    }

    public function complete(User $user, Task $task): Response
    {
        return $user->isAdmin()
            ? Response::allow()
            : ($user->id === $task->assignee->id
                ? Response::allow()
                : Response::deny('You are not authorized to complete this task.'));
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Task $task): Response
    {
        return $user->isAdmin()
            ? Response::allow()
            : ($user->id === $task->creator->id
                ? Response::allow()
                : Response::deny('You are not authorized to delete this task.'));
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Task $task): Response
    {
        return $user->isAdmin()
            ? Response::allow()
            : Response::deny('You are not authorized to restore tasks.');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Task $task): Response
    {
        return $user->isAdmin()
            ? Response::allow()
            : Response::deny('You are not authorized to permanently delete tasks.');
    }
}
