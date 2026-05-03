<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Universe extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'slug',
        'name',
        'description',
        'image_url',
        'display_order',
    ];

    protected function casts(): array
    {
        return [];
    }

    public function products()
    {
        return $this->hasMany(Product::class);
    }

    public function getProductsCountAttribute(): int
    {
        return $this->products()->where('active', true)->count();
    }

    public function getImageUrlAttribute($value): ?string
    {
        if ($value) {
            if (filter_var($value, FILTER_VALIDATE_URL)) {
                return $value;
            }
            $value = ltrim($value, '/');
            if (str_starts_with($value, 'storage/')) {
                return asset($value);
            }
            return asset("storage/{$value}");
        }
        return asset("images/products/{$this->slug}.jpg");
    }
}
