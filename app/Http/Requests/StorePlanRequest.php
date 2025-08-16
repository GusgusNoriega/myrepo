<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePlanRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Ya usamos middleware role:admin, pero dejámoslo en true
        return true;
    }

    public function rules(): array
    {
        return [
            'code'              => ['required','string','alpha_dash','max:255','unique:plans,code'],
            'name'              => ['required','string','max:255'],
            'price_usd'         => ['required','numeric','min:0'],
            'billing_interval'  => ['required','in:month,year'],
            'is_active'         => ['sometimes','boolean'],

            // features anidadas
            'features'                 => ['required','array'],
            'features.product_limit'   => ['required','integer','min:0'],
            'features.storage_limit_bytes' => ['required','integer','min:0'],
            'features.staff_limit'     => ['required','integer','min:0'],
            'features.asset_limit'     => ['nullable','integer','min:0'],
            'features.category_limit'  => ['nullable','integer','min:0'],
            'features.other'           => ['nullable','array'],
        ];
    }
}