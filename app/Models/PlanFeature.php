<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PlanFeature extends Model
{
    protected $table = 'plan_features';

    protected $fillable = [
        'plan_id',
        'product_limit',
        'storage_limit_bytes',
        'staff_limit',
        'asset_limit',
        'category_limit',
        'other',
    ];

    protected $casts = [
        'other' => 'array',
    ];

    public function plan()
    {
        return $this->belongsTo(Plan::class, 'plan_id');
    }
}