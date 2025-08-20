<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Business extends Model
{
    use HasFactory;

    // Eloquent infiere 'businesses' correctamente
    protected $fillable = [
        'owner_user_id','name','slug','domain','subdomain','country_code',
        'currency','timezone','locale','contact_name','contact_email','settings','is_active',
    ];

    protected $casts = [
        'settings' => 'array',
        'is_active' => 'boolean',
    ];

    public function owner()     { return $this->belongsTo(User::class, 'owner_user_id'); }
    public function categories(){ return $this->hasMany(Category::class); }
    public function products()  { return $this->hasMany(Product::class); }
    public function subscriptions(){ return $this->hasMany(Subscription::class); }
    public function memberships(){ return $this->hasMany(Membership::class); }
}