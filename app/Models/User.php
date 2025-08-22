<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles;



    protected $fillable = [
        'name',
        'email',
        'password',
        'active_business_id',
        // agrega aquí otros campos si los tuvieras
    ];

    protected $hidden = ['password', 'remember_token'];

    protected $casts = [
        'email_verified_at' => 'datetime',
        // Si tu proyecto es Laravel 10+, puedes dejar este cast:
        'password' => 'hashed',
    ];

    // =======================
    // Relaciones existentes
    // =======================

    public function activeBusiness()
    {
        return $this->belongsTo(Business::class, 'active_business_id');
    }

    public function memberships()
    {
        return $this->hasMany(Membership::class);
    }

    public function businesses()
    {
        return $this->belongsToMany(Business::class, 'memberships')
            ->withPivot(['role', 'state', 'accepted_at', 'invited_by'])
            ->withTimestamps();
    }

    public function documentos()
    {
        return $this->hasMany(Documento::class, 'owner_user_id');
    }

    public function media()
    {
        return $this->hasMany(\Spatie\MediaLibrary\MediaCollections\Models\Media::class, 'owner_user_id');
    }

    // ==========================================================
    // Helpers filtrados por guard para que TU UI lea siempre web
    // ==========================================================

    public function rolesWeb()
    {
        // La relación roles() la da HasRoles; aquí solo filtramos por guard
        return $this->roles()->where('guard_name', 'web');
    }

    public function permissionsWeb()
    {
        return $this->permissions()->where('guard_name', 'web');
    }

    // Accesores opcionales (si te sirven en Blade/API)
    public function getRolesWebNamesAttribute()
    {
        return $this->rolesWeb()->pluck('name')->values();
    }

    public function getPermissionsWebNamesAttribute()
    {
        return $this->permissionsWeb()->pluck('name')->values();
    }

    /**
     * Payload estándar para tu API/UI.
     * Devuelve roles/permisos SOLO del guard 'web' para evitar el alerta
     * "The given role or permission should use guard api instead of web"
     * y para que se marquen bien los checkboxes.
     */
    public function toAclArray(): array
    {
        // Evita datos en caché cargando relaciones ya filtradas por guard
        $this->loadMissing([
            'roles' => fn ($q) => $q->where('guard_name', 'web'),
            'permissions' => fn ($q) => $q->where('guard_name', 'web'),
        ]);

        return [
            'id'                   => $this->id,
            'name'                 => $this->name,
            'email'                => $this->email,
            'roles'                => $this->roles->pluck('name')->values(),
            'permissions'          => $this->permissions->pluck('name')->values(),
            'active_business_id'   => $this->active_business_id,
        ];
    }
}