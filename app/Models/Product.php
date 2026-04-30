<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Product extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'slug',
        'name',
        'reference',
        'description',
        'universe_id',
        'price_ht',
        'retail_ttc',
        'vat_rate',
        'moq',
        'pack_size',
        'stock',
        'images',
        'material',
        'finish',
        'tag',
        'is_new',
        'active',
    ];

    protected function casts(): array
    {
        return [
            'price_ht' => 'decimal:2',
            'retail_ttc' => 'decimal:2',
            'vat_rate' => 'decimal:2',
            'images' => 'array',
            'is_new' => 'boolean',
            'active' => 'boolean',
        ];
    }

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function ($product) {
            if (empty($product->slug)) {
                $product->slug = Str::slug($product->name);
            }
            if (empty($product->reference)) {
                $prefix = $product->universe ? strtoupper(Str::substr($product->universe->slug, 0, 3)) : 'PRD';
                $product->reference = 'ML-' . $prefix . '-' . str_pad((string) random_int(1, 9999), 4, '0', STR_PAD_LEFT);
            }
        });

        static::updating(function ($product) {
            if ($product->isDirty('name') && empty($product->slug)) {
                $product->slug = Str::slug($product->name);
            }
        });
    }

    public function universe()
    {
        return $this->belongsTo(Universe::class);
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function getPriceTtcAttribute(): string
    {
        return number_format($this->price_ht * (1 + $this->vat_rate / 100), 2, '.', '');
    }

    public function scopeActive($query)
    {
        return $query->where('active', true);
    }

    public function scopeNew($query)
    {
        return $query->where('is_new', true);
    }

    public function scopeByUniverse($query, $universeSlug)
    {
        return $query->whereHas('universe', function ($q) use ($universeSlug) {
            $q->where('slug', $universeSlug);
        });
    }

    public function scopeSearch($query, $term)
    {
        return $query->where(function ($q) use ($term) {
            $q->where('name', 'like', "%{$term}%")
              ->orWhere('reference', 'like', "%{$term}%")
              ->orWhere('description', 'like', "%{$term}%");
        });
    }
}
