<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

use App\Models\User;
use App\Models\Business;
use App\Models\Membership;

class DemoBusinessSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            // Asegura que existan planes antes:
            $this->call(PlanSeeder::class); // ← IMPORTANTE

            $email = env('ADMIN_EMAIL', 'gusgusnoriega@gmail.com');
            $user  = User::where('email', $email)->first();

            $defs = [
                ['name' => 'Acme Store',  'currency' => 'USD', 'country_code' => 'PE', 'timezone' => 'America/Lima', 'locale' => 'es-PE'],
                ['name' => 'Beta Market', 'currency' => 'USD', 'country_code' => 'PE', 'timezone' => 'America/Lima', 'locale' => 'es-PE'],
                ['name' => 'Gamma Shop',  'currency' => 'USD', 'country_code' => 'PE', 'timezone' => 'America/Lima', 'locale' => 'es-PE'],
            ];

            $businesses = [];
            foreach ($defs as $row) {
                $slug = $this->uniqueSlug($row['name']);
                $biz = Business::firstOrCreate(
                    ['slug' => $slug],
                    [
                        'name'         => $row['name'],
                        'currency'     => $row['currency'],
                        'country_code' => $row['country_code'],
                        'timezone'     => $row['timezone'],
                        'locale'       => $row['locale'],
                        'owner_user_id'=> $user?->id,
                        'is_active'    => 1,
                    ]
                );
                $businesses[] = $biz;

                // Inicializa usage_counters si no existe
                DB::table('usage_counters')->updateOrInsert(
                    ['business_id' => $biz->id],
                    [
                        'products_count' => 0,
                        'assets_count'   => 0,
                        'storage_bytes'  => 0,
                        'created_at'     => now(),
                        'updated_at'     => now(),
                    ]
                );
            }

            if ($user) {
                foreach ($businesses as $i => $biz) {
                    Membership::firstOrCreate(
                        ['user_id' => $user->id, 'business_id' => $biz->id],
                        ['role' => $i === 0 ? 'owner' : 'admin', 'state' => 'active']
                    );
                }
                if (isset($businesses[0])) {
                    $user->forceFill(['active_business_id' => $businesses[0]->id])->save();
                }
            }

            // === ASIGNAR SUSCRIPCIONES ===
            // Primero negocio => plan Pro activo por 1 mes
            if (isset($businesses[0])) {
                $this->ensureSubscription($businesses[0]->id, 'pro', 'active', 1);
            }
            // Segundo negocio => plan Free trial por 14 días (opcional)
            if (isset($businesses[1])) {
                $this->ensureSubscription($businesses[1]->id, 'free', 'trialing', 1, 14);
            }
            // Tercero negocio => plan Business activo (opcional)
            if (isset($businesses[2])) {
                $this->ensureSubscription($businesses[2]->id, 'business', 'active', 1);
            }
        });
    }

    private function uniqueSlug(string $name): string
    {
        $base = Str::slug($name);
        $slug = $base;
        $i = 1;
        while (Business::where('slug', $slug)->exists()) {
            $slug = $base.'-'.$i++;
        }
        return $slug;
    }

    private function ensureSubscription(int $businessId, string $planCode, string $status = 'active', int $months = 1, ?int $trialDays = null): void
    {
        $planId = DB::table('plans')->where('code', $planCode)->value('id');
        if (!$planId) return;

        // ¿Ya tiene una suscripción vigente?
        $exists = DB::table('subscriptions')
            ->where('business_id', $businessId)
            ->whereIn('status', ['active','trialing'])
            ->where('current_period_end', '>=', now())
            ->exists();

        if ($exists) return;

        $start = now();
        $end   = now()->copy()->addMonths($months);

        DB::table('subscriptions')->insert([
            'business_id'         => $businessId,
            'plan_id'             => $planId,
            'status'              => $status,
            'current_period_start'=> $start,
            'current_period_end'  => $end,
            'trial_ends_at'       => $trialDays ? now()->copy()->addDays($trialDays) : null,
            'cancel_at_period_end'=> 0,
            'created_at'          => now(),
            'updated_at'          => now(),
        ]);
    }
}