<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class Documento extends Model implements HasMedia
{
    use InteractsWithMedia;

    protected $fillable = ['titulo', 'descripcion', 'tipo', 'ruta'];

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('imagenes')
            ->useDisk('public')
            ->acceptsMimeTypes(['image/jpeg','image/png']);

        $this->addMediaCollection('archivos')
            ->useDisk('public')
            ->acceptsMimeTypes(['application/pdf','application/msword','application/vnd.openxmlformats-officedocument.wordprocessingml.document']);
    }

    /*
    public function registerMediaConversions(Media $media = null): void
    {
        $this->addMediaConversion('thumb')
             ->width(300)->height(300)
             ->performOnCollections('imagenes');
    }
    */
}
