<?php

namespace App\Policies;

use App\Models\Service;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class ServicePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['admin', 'tecnico', 'cliente']);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Service $service): bool
    {
        // Admin pode ver todos os serviços
        if ($user->hasRole('admin')) {
            return true;
        }
        
        // Técnico pode ver serviços atribuídos a ele
        if ($user->hasRole('tecnico')) {
            return $service->technician_id === $user->id;
        }
        
        // Cliente pode ver serviços dos seus veículos ou equipamentos
        if ($user->hasRole('cliente')) {
            return $service->customer_id === $user->customer_id;
        }
        
        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        // Apenas admin pode criar serviços
        return $user->hasRole('admin');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Service $service): bool
    {
        // Admin pode atualizar todos os serviços
        if ($user->hasRole('admin')) {
            return true;
        }
        
        // Técnico pode atualizar serviços atribuídos a ele
        if ($user->hasRole('tecnico')) {
            return $service->technician_id === $user->id;
        }
        
        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Service $service): bool
    {
        // Apenas admin pode excluir serviços
        return $user->hasRole('admin');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Service $service): bool
    {
        // Apenas admin pode restaurar serviços excluídos
        return $user->hasRole('admin');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Service $service): bool
    {
        // Apenas admin pode excluir permanentemente serviços
        return $user->hasRole('admin');
    }
}
