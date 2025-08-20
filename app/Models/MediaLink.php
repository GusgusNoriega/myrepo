<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class MediaLink extends Model
{
    protected $table = 'media_links';

    protected $fillable = [
        'business_id',
        'linkable_type',
        'linkable_id',
        'media_id',
        'is_featured',
        'is_gallery',
        'position',
    ];

    protected $casts = [
        'is_featured' => 'boolean',
        'is_gallery'  => 'boolean',
        'position'    => 'integer',
    ];

    public function linkable(): MorphTo
    {
        return $this->morphTo();
    }

    public function media()
    {
        // Modelo de Spatie (no necesitas extenderlo si no lo hiciste)
        return $this->belongsTo(Media::class, 'media_id');
    }
}