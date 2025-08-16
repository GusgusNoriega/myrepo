<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PlanSeeder extends Seeder
{
    public function run(): void
    {
        // code => [name, price_usd, billing_interval, storage_limit_bytes, asset_limit, product_limit, staff_limit, category_limit]
        $plans = [
            'free' => [
                'Free',      0.00, 'month',
                200 * 1024 * 1024,   // 200 MB
                200,                 // 200 archivos
                50,                  // productos
                1,                   // staff
                10,                  // categorías
            ],
            'pro' => [
                'Pro',       19.00, 'month',
                10 * 1024 * 1024 * 1024, // 10 GB
                5000,
                1000,
                5,
                200,
            ],
            'business' => [
                'Business',  49.00, 'month',
                100 * 1024 * 1024 * 1024, // 100 GB
                50000,
                10000,
                20,
                500,
            ],
        ];

        foreach ($plans as $code => [$name, $price, $interval, $bytes, $assetLimit, $productLimit, $staffLimit, $catLimit]) {
            // Upsert plan
            DB::table('plans')->updateOrInsert(
                ['code' => $code],
                [
                    'name'             => $name,
                    'price_usd'        => $price,
                    'billing_interval' => $interval,
                    'is_active'        => 1,
                    'updated_at'       => now(),
                    'created_at'       => now(),
                ]
            );

            $planId = (int) DB::table('plans')->where('code', $code)->value('id');

            // Upsert plan_features por plan_id
            DB::table('plan_features')->updateOrInsert(
                ['plan_id' => $planId],
                [
                    'product_limit'      => $productLimit,
                    'storage_limit_bytes'=> $bytes,
                    'staff_limit'        => $staffLimit,
                    'asset_limit'        => $assetLimit,
                    'category_limit'     => $catLimit,
                    'updated_at'         => now(),
                    'created_at'         => now(),
                ]
            );
        }
    }
}