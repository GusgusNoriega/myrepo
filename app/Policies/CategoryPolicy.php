<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Category;

class CategoryPolicy
{
    public function before(User $user, $ability)
    {
        // Super admin (admin) todo permitido
        if ($user->hasRole('admin')) {
            return true;
        }
        return null;
    }

    public function viewAny(User $user): bool
    {
        // Un "administrador" puede listar (el controlador limitará a su negocio)
        return $user->hasRole('administrador');
    }

    public function view(User $user, Category $category): bool
    {
        // Solo categorías del negocio asignado
        return $user->hasRole('administrador')
            && $user->active_business_id === $category->business_id;
    }

    public function create(User $user): bool
    {
        // Puede crear si tiene negocio asignado
        return $user->hasRole('administrador')
            && !empty($user->active_business_id);
    }

    public function update(User $user, Category $category): bool
    {
        return $user->hasRole('administrador')
            && $user->active_business_id === $category->business_id;
    }

    public function delete(User $user, Category $category): bool
    {
        return $user->hasRole('administrador')
            && $user->active_business_id === $category->business_id;
    }

    public function restore(User $user, Category $category): bool { return false; }
    public function forceDelete(User $user, Category $category): bool { return false; }
}
