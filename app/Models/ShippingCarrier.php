<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class ShippingCarrier extends Model
{
    use HasUuids;

    protected $fillable = [
        'shippingbo_method_id',
        'name',
        'carrier_name',
        'price',
        'delay',
        'is_active',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true)->orderBy('sort_order');
    }

    public static function calculateShipping(?string $carrierId, float $subtotalHt, mixed $discount = null): float
    {
        if ($discount && $discount->type === 'free_shipping') {
            return 0;
        }

        $threshold = \App\Models\SiteSetting::where('key', 'general')->first()?->value['freeShippingFrom'] ?? 300;
        if ($subtotalHt >= (float) $threshold) {
            return 0;
        }

        if ($carrierId) {
            $carrier = static::where('id', $carrierId)->orWhere('name', $carrierId)->first();
            if ($carrier) {
                return (float) $carrier->price;
            }
        }

        $first = static::active()->first();
        return $first ? (float) $first->price : 15;
    }
}
