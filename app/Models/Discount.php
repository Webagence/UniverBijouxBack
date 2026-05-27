<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Discount extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'code',
        'name',
        'type',
        'value',
        'applies_to',
        'billing_cycle',
        'min_order_amount',
        'max_discount_amount',
        'usage_limit',
        'usage_count',
        'valid_from',
        'valid_until',
        'is_active',
        'description',
    ];

    protected function casts(): array
    {
        return [
            'value' => 'decimal:2',
            'min_order_amount' => 'decimal:2',
            'max_discount_amount' => 'decimal:2',
            'valid_from' => 'datetime',
            'valid_until' => 'datetime',
            'is_active' => 'boolean',
        ];
    }

    public function products()
    {
        return $this->belongsToMany(Product::class, 'discount_product');
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'discount_user');
    }

    public function orderDiscounts()
    {
        return $this->hasMany(OrderDiscount::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeValid($query)
    {
        return $query->where(function ($q) {
            $q->whereNull('valid_from')
              ->orWhere('valid_from', '<=', now());
        })->where(function ($q) {
            $q->whereNull('valid_until')
              ->orWhere('valid_until', '>=', now());
        });
    }

    public function scopeAvailable($query)
    {
        return $query->active()->valid()->where(function ($q) {
            $q->whereNull('usage_limit')
              ->orWhereColumn('usage_count', '<', 'usage_limit');
        });
    }

    public function isAvailable(): bool
    {
        if (!$this->is_active) return false;

        if ($this->valid_from && $this->valid_from->isFuture()) return false;
        if ($this->valid_until && $this->valid_until->isPast()) return false;

        if ($this->usage_limit && $this->usage_count >= $this->usage_limit) return false;

        return true;
    }

    public function incrementUsage(): void
    {
        $this->increment('usage_count');
    }
}
