<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // ya controlamos por middleware (auth:api, role:admin)
    }

    public function rules(): array
    {
        $businessId = $this->input('business_id');

        return [
            'business_id' => ['required','integer','exists:businesses,id'],
            'category_id' => ['nullable','integer','exists:categories,id'],
            'name'        => ['required','string','max:255'],
            'slug'        => [
                'required','string','max:255',
                Rule::unique('products','slug')->where(fn($q) => $q->where('business_id',$businessId)),
            ],
            'sku'         => [
                'required','string','max:255',
                Rule::unique('products','sku')->where(fn($q) => $q->where('business_id',$businessId)),
            ],
            'barcode'     => ['nullable','string','max:64'],
            'description' => ['nullable','string'],
            'status'      => ['required','in:draft,active,archived'],
            'has_variants'=> ['boolean'],
            'price_cents' => ['nullable','integer','min:0'],
            'cost_cents'  => ['nullable','integer','min:0'],
            'compare_at_price_cents' => ['nullable','integer','min:0'],
            'currency'    => ['required','string','size:3'],
            'tax_included'=> ['boolean'],
            'attributes'  => ['nullable','array'],
            'weight_grams'=> ['nullable','integer','min:0'],
            'dimensions'  => ['nullable','array'],
            'published_at'=> ['nullable','date'],

            // manejo de media por IDs
            'featured_media_id'  => ['nullable','integer','exists:media,id'],
            'gallery_media_ids'  => ['nullable','string','regex:/^\s*\d+(?:\s*,\s*\d+)*\s*$/'], // "1,2,3"
        ];
    }
}