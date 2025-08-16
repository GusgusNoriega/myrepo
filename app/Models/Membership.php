<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Membership extends Model
{
    use HasFactory;

    // Eloquent infiere 'memberships'
    protected $fillable = [
        'user_id','business_id','role','state','accepted_at','invited_by',
    ];

    protected $casts = [
        'accepted_at' => 'datetime',
    ];
}