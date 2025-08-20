<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\MediaLink;

class Product extends Model
{
    protected $fillable = [
        'business_id','category_id','name','slug','sku','barcode','description',
        'status','has_variants','price_cents','cost_cents','compare_at_price_cents',
        'currency','tax_included','attributes','weight_grams','dimensions',
        'published_at',
    ];

    protected $casts = [
        'has_variants' => 'bool',
        'tax_included' => 'bool',
        'attributes'   => 'array',
        'dimensions'   => 'array',
        'published_at' => 'datetime',
    ];

    public function business()
    {
        return $this->belongsTo(Business::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function mediaLinks()
    {
        return $this->morphMany(MediaLink::class, 'linkable');
    }

    public function featuredMediaLink()
    {
        return $this->mediaLinks()->where('is_featured', true)->orderByDesc('id');
    }

    public function galleryMediaLinks()
    {
        return $this->mediaLinks()
            ->where('is_gallery', true)
            ->orderBy('position')
            ->orderBy('id');
    }
}