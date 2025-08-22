<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class UserPolicy
{
    use HandlesAuthorization;

    /**
     * Atajo: si el autenticado tiene rol "admin" (superadmin),
     * permite todo y omite el resto de checks.
     */
    public function before(User $auth, $ability)
    {
        if ($auth->hasRole('admin')) {
            return true;
        }
        return null;
    }

    public function viewAny(User $auth): bool
    {
        // Solo superadmin ve el listado completo (el before ya lo permite).
        return false;
    }

    public function view(User $auth, User $user): bool
    {
        // Usuarios normales sólo pueden verse a sí mismos
        return $auth->id === $user->id;
    }

    public function create(User $auth): bool
    {
        // Solo superadmin crea usuarios (before ya lo permite)
        return false;
    }

    public function update(User $auth, User $user): bool
    {
        // Usuarios normales: solo editar su propio perfil (name/email/password)
        return $auth->id === $user->id;
    }

    public function delete(User $auth, User $user): bool
    {
        // Solo superadmin elimina (before ya lo permite)
        return false;
    }

    /**
     * Capacidad específica: cambiar el active_business_id de un usuario.
     * Solo superadmin (el before ya lo permite). Para el resto: NO.
     */
    public function updateActiveBusiness(User $auth, User $user): bool
    {
        return false;
    }

    /**
     * Capacidad específica: permitir setear active_business_id al CREAR usuarios.
     * Solo superadmin (el before ya lo permite).
     */
    public function setActiveBusinessOnCreate(User $auth): bool
    {
        return false;
    }
}