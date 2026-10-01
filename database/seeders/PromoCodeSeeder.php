<?php

namespace Database\Seeders;

use App\Models\PromoCode;
use Illuminate\Database\Seeder;

class PromoCodeSeeder extends Seeder
{
    public function run(): void
    {
        $codes = [
            [
                'code' => 'BF40',
                'discount_percent' => 40,
                'eligible_slugs' => ['bomber-urban-ivoire', 'jean-relaxed-dakar', 'sac-structure-ayo'],
                'starts_at' => '2026-10-01 00:00:00+00:00',
                'ends_at' => '2026-11-30 22:59:59+00:00',
            ],
            [
                'code' => 'NOEL15',
                'discount_percent' => 15,
                'eligible_slugs' => ['sac-structure-ayo', 'sneakers-blanc-studio', 'blazer-oversize-nova'],
                'starts_at' => '2026-12-01 00:00:00+00:00',
                'ends_at' => '2026-12-25 22:59:59+00:00',
            ],
            [
                'code' => '2027',
                'discount_percent' => 10,
                'eligible_slugs' => ['blazer-oversize-nova', 'pantalon-wide-leg-atlas', 'crop-top-lune'],
                'starts_at' => '2026-12-26 00:00:00+00:00',
                'ends_at' => '2027-01-05 22:59:59+00:00',
            ],
        ];

        foreach ($codes as $code) {
            PromoCode::query()->updateOrCreate(['code' => $code['code']], [...$code, 'is_active' => true, 'is_demo_data' => true]);
        }
    }
}
