<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePlanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $id = $this->route('plan'); // route-model-binding o {plan}
        if (is_object($id) && method_exists($id,'getKey')) {
            $id = $id->getKey();
        }

        return [
            'code'              => ['sometimes','string','alpha_dash','max:255', Rule::unique('plans','code')->ignore($id)],
            'name'              => ['sometimes','string','max:255'],
            'price_usd'         => ['sometimes','numeric','min:0'],
            'billing_interval'  => ['sometimes','in:month,year'],
            'is_active'         => ['sometimes','boolean'],

            'features'                 => ['sometimes','array'],
            'features.product_limit'   => ['sometimes','integer','min:0'],
            'features.storage_limit_bytes' => ['sometimes','integer','min:0'],
            'features.staff_limit'     => ['sometimes','integer','min:0'],
            'features.asset_limit'     => ['sometimes','nullable','integer','min:0'],
            'features.category_limit'  => ['sometimes','nullable','integer','min:0'],
            'features.other'           => ['sometimes','nullable','array'],
        ];
    }
}