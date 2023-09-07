<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\Response;

class UserPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): Response
    {
        return $user->isAdmin()
            ? Response::allow()
            : Response::deny('You must be an administrator to view users.');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, User $model): Response
    {
        return $user->isAdmin()
            ? Response::allow()
            : ($user->id === $model->id
                ? Response::allow()
                : Response::deny('You must be the owner of this user to view it.'));
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): Response
    {
        return Response::deny('Invalid action.');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, User $model): Response
    {
        return $user->isAdmin()
            ? Response::allow()
            : ($user->id === $model->id
                ? Response::allow()
                : Response::deny('You must be the owner of this user to update it.'));
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, User $model): Response
    {
        return $user->isAdmin()
            ? Response::allow()
            : ($user->id === $model->id
                ? Response::allow()
                : Response::deny('You must be the owner of this user to delete it.'));
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, User $model): Response
    {
        return $user->isAdmin()
            ? Response::allow()
            : Response::deny('You must be an administrator to restore users.');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, User $model): Response
    {
        return $user->isAdmin()
            ? Response::allow()
            : Response::deny('You must be an administrator to permanently delete users.');
    }
}
