<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Subscription extends Model
{
    protected $table = 'subscriptions';

    protected $fillable = [
        'business_id',
        'plan_id',
        'status',                 // trialing|active|past_due|canceled
        'current_period_start',
        'current_period_end',
        'trial_ends_at',
        'canceled_at',
        'cancel_at_period_end',   // bool
        'external_ref',
        'external_customer_id',
        'payment_method',         // json
    ];

    protected $casts = [
        'current_period_start'  => 'datetime',
        'current_period_end'    => 'datetime',
        'trial_ends_at'         => 'datetime',
        'canceled_at'           => 'datetime',
        'cancel_at_period_end'  => 'boolean',
        'payment_method'        => 'array',
    ];

    // Relaciones
    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }
}
