<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Documento extends Model implements HasMedia
{
    use InteractsWithMedia;

    protected $fillable = [
        'business_id','owner_user_id','titulo','descripcion','tipo','ruta',
    ];

    protected static function booted()
    {
        static::creating(function (Documento $doc) {
            if (! $doc->business_id && auth()->check()) {
                $doc->business_id = auth()->user()->active_business_id;
            }
            if (! $doc->owner_user_id && auth()->check()) {
                $doc->owner_user_id = auth()->id();
            }
        });
    }

    // Opcional, por claridad: define colecciones
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('imagenes');
        $this->addMediaCollection('archivos');
    }
}