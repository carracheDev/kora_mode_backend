<?php

namespace App\Models;

use Database\Factories\PromoCodeFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['code', 'discount_percent', 'eligible_slugs', 'starts_at', 'ends_at', 'is_active', 'is_demo_data'])]
class PromoCode extends Model
{
    /** @use HasFactory<PromoCodeFactory> */
    use HasFactory;

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'eligible_slugs' => 'array',
            'starts_at' => 'immutable_datetime',
            'ends_at' => 'immutable_datetime',
            'is_active' => 'boolean',
            'is_demo_data' => 'boolean',
        ];
    }

    public function isCurrentlyValid(): bool
    {
        $currentTime = now();

        return $this->is_active
            && (! $this->starts_at || $this->starts_at->lessThanOrEqualTo($currentTime))
            && (! $this->ends_at || $this->ends_at->greaterThan($currentTime));
    }
}
