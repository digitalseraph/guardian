<?php

namespace App\Policies;

use App\Models\Users\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class UserPolicy
{
    use HandlesAuthorization;

    public function before($user, $ability)
    {
        if ($user->hasRole('system.permissions.all') || $user->hasRole('admin.system')) {
            return true;
        }
    }

    /**
     * Determine whether the user can view the user.
     *
     * @param  \App\Models\Users\User  $user
     * @param  \App\Models\Users\User  $user
     * @return mixed
     */
    public function view(User $user)
    {
        return $user->permissions(['user.view']);
    }

    /**
     * Determine whether the user can create users.
     *
     * @param  \App\Models\Users\User  $user
     * @return mixed
     */
    public function create(User $user)
    {
        return $user->permissions(['user.create']);
    }

    /**
     * Determine whether the user can update the user.
     *
     * @param  \App\Models\Users\User  $user
     * @param  \App\Models\Users\User  $user
     * @return mixed
     */
    public function update(User $user, User $userToUpdate)
    {
        return $user->permissions(['user.update']);
    }

    /**
     * Determine whether the user can delete the user.
     *
     * @param  \App\Models\Users\User  $user
     * @param  \App\Models\Users\User  $user
     * @return mixed
     */
    public function delete(User $user, User $userToDelete)
    {
        return $user->permissions(['user.delete']);
    }
}
