<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('view users');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, User $model): bool
    {
        return $user->hasPermissionTo('view users') || $user->id === $model->id;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hasPermissionTo('create users');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, User $model): bool
    {
        // Usuários podem editar seu próprio perfil
        if ($user->id === $model->id) {
            return true;
        }

        // Apenas administradores podem editar outros usuários
        return $user->hasPermissionTo('update users');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, User $model): bool
    {
        // Impedir que um usuário delete a si mesmo
        if ($user->id === $model->id) {
            return false;
        }

        return $user->hasPermissionTo('delete users');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, User $model): bool
    {
        return $user->hasPermissionTo('update users');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, User $model): bool
    {
        // Impedir que um usuário delete a si mesmo
        if ($user->id === $model->id) {
            return false;
        }

        return $user->hasPermissionTo('delete users');
    }
}
