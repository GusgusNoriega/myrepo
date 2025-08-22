<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Business;

class BusinessPolicy
{
    public function before(User $user, $ability)
    {
        // Si tiene el rol super admin, todo permitido
        if ($user->hasRole('admin')) {
            return true;
        }
        return null;
    }

    public function viewAny(User $user): bool
    {
        // Un "administrador" puede listar, pero luego el controlador restringe a su business
        return $user->hasRole('administrador');
    }

    public function view(User $user, Business $business): bool
    {
        // Solo su negocio asignado
        return $user->hasRole('administrador')
            && $user->active_business_id === $business->id;
    }

    public function create(User $user): bool
    {
        // Solo super admin (ya cubierto por before)
        return false;
    }

    public function update(User $user, Business $business): bool
    {
        // Puede actualizar SOLO su negocio asignado (con whitelist en el controlador)
        return $user->hasRole('administrador')
            && $user->active_business_id === $business->id;
    }

    public function delete(User $user, Business $business): bool
    {
        // Solo super admin (before)
        return false;
    }

    public function restore(User $user, Business $business): bool { return false; }
    public function forceDelete(User $user, Business $business): bool { return false; }
}