<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $product   = $this->route('product');
        $businessId = $product?->business_id ?? $this->input('business_id');

        return [
            'business_id' => ['sometimes','integer','exists:businesses,id'],
            'category_id' => ['nullable','integer','exists:categories,id'],
            'name'        => ['sometimes','string','max:255'],
            'slug'        => [
                'sometimes','string','max:255',
                Rule::unique('products','slug')
                    ->ignore($product?->id)
                    ->where(fn($q) => $q->where('business_id',$businessId)),
            ],
            'sku'         => [
                'sometimes','string','max:255',
                Rule::unique('products','sku')
                    ->ignore($product?->id)
                    ->where(fn($q) => $q->where('business_id',$businessId)),
            ],
            'barcode'     => ['nullable','string','max:64'],
            'description' => ['nullable','string'],
            'status'      => ['sometimes','in:draft,active,archived'],
            'has_variants'=> ['sometimes','boolean'],
            'price_cents' => ['nullable','integer','min:0'],
            'cost_cents'  => ['nullable','integer','min:0'],
            'compare_at_price_cents' => ['nullable','integer','min:0'],
            'currency'    => ['sometimes','string','size:3'],
            'tax_included'=> ['sometimes','boolean'],
            'attributes'  => ['nullable','array'],
            'weight_grams'=> ['nullable','integer','min:0'],
            'dimensions'  => ['nullable','array'],
            'published_at'=> ['nullable','date'],

            'featured_media_id'  => ['nullable','integer','exists:media,id'],
            'gallery_media_ids'  => ['nullable','string','regex:/^\s*\d+(?:\s*,\s*\d+)*\s*$/'],
        ];
    }
}