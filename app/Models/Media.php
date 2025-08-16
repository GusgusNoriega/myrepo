<?php

namespace App\Models;

use Spatie\MediaLibrary\MediaCollections\Models\Media as BaseMedia;

class Media extends BaseMedia
{
    protected static function booted()
    {
        static::creating(function (self $media) {
            // si ya viene seteado, lo respetamos; si no, tomamos del usuario autenticado
            if (auth()->check()) {
                $media->business_id   = $media->business_id   ?? auth()->user()->active_business_id;
                $media->owner_user_id = $media->owner_user_id ?? auth()->id();
            }
        });
    }

    public function scopeForCurrentBusiness($q)
    {
        if (auth()->check() && auth()->user()->active_business_id) {
            $q->where('business_id', auth()->user()->active_business_id);
        }
        return $q;
    }
}
