<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Media as MediaModel;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Support\Facades\DB;

class MediaPolicy
{
    use HandlesAuthorization;

    /**
     * Super admin (rol global 'admin' vía Spatie) puede todo.
     */
    public function before(User $user, string $ability)
    {
        if ($this->isSuperAdmin($user)) {
            return true;
        }
    }

    protected function isSuperAdmin(User $user): bool
    {
        return method_exists($user, 'hasRole') && $user->hasRole('admin');
    }

    /**
     * Devuelve true si el usuario es owner/admin activo en el negocio dado.
     */
    protected function isBizAdmin(User $user, int $bizId): bool
    {
        $role = DB::table('memberships')
            ->where('business_id', $bizId)
            ->where('user_id', $user->id)
            ->where('state', 'active')
            ->value('role');

        return in_array($role, ['owner','admin'], true);
    }

    /**
     * Listar medios de un negocio.
     * Para no-admin, debe venir un $bizId (negocio activo).
     */
    public function viewAny(User $user, ?int $bizId = null): bool
    {
        return !is_null($bizId);
    }

    /**
     * Ver un medio puntual.
     * Permitido si es admin del negocio o si es el propietario del archivo.
     */
    public function view(User $user, MediaModel $media): bool
    {
        return $this->isBizAdmin($user, (int) $media->business_id)
            || (int) $media->owner_user_id === (int) $user->id;
    }

    /**
     * Crear medios en un negocio.
     * - Biz admin: puede crear para su negocio (owner cualquiera).
     * - Usuario normal: solo si el owner_user_id === su propio id.
     */
    public function create(User $user, int $bizId, ?int $ownerUserId = null): bool
    {
        if ($this->isBizAdmin($user, $bizId)) {
            return true;
        }
        return (int) $ownerUserId === (int) $user->id;
    }

    /**
     * Actualizar metadatos de un medio.
     */
    public function update(User $user, MediaModel $media): bool
    {
        return $this->isBizAdmin($user, (int) $media->business_id)
            || (int) $media->owner_user_id === (int) $user->id;
    }

    /**
     * Eliminar un medio.
     */
    public function delete(User $user, MediaModel $media): bool
    {
        return $this->isBizAdmin($user, (int) $media->business_id)
            || (int) $media->owner_user_id === (int) $user->id;
    }
}
