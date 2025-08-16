<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Plan extends Model
{
    protected $table = 'plans';

    protected $fillable = [
        'code', 'name', 'price_usd', 'billing_interval', 'is_active',
    ];

    protected $casts = [
        'is_active'  => 'boolean',
        'price_usd'  => 'decimal:2',
    ];

    public function features()
    {
        return $this->hasOne(PlanFeature::class, 'plan_id');
    }

    // Útil para listados con features
    protected $with = ['features'];

    // Normaliza code
    public function setCodeAttribute($v)
    {
        $this->attributes['code'] = strtolower($v);
    }

    // Scopes útiles
    public function scopeActive($q)   { return $q->where('is_active', 1); }
    public function scopeByCode($q,$c){ return $q->where('code', $c); }
}