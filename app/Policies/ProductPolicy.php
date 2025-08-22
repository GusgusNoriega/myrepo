<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Product;

class ProductPolicy
{
    public function before(User $user, $ability)
    {
        // Super admin: todo permitido
        if ($user->hasRole('admin')) return true;
        return null;
    }

    public function viewAny(User $user): bool
    {
        return $user->hasRole('administrador');
    }

    public function view(User $user, Product $product): bool
    {
        return $user->hasRole('administrador')
            && $user->active_business_id === $product->business_id;
    }

    public function create(User $user): bool
    {
        return $user->hasRole('administrador') && !empty($user->active_business_id);
    }

    public function update(User $user, Product $product): bool
    {
        return $user->hasRole('administrador')
            && $user->active_business_id === $product->business_id;
    }

    public function delete(User $user, Product $product): bool
    {
        return $user->hasRole('administrador')
            && $user->active_business_id === $product->business_id;
    }
}