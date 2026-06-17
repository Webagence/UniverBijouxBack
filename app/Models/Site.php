<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class Site extends Model
{
    use HasUuids;

    protected $fillable = [
        'slug',
        'name',
        'domain',
        'description',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function products()
    {
        return $this->hasMany(Product::class);
    }

    public function universes()
    {
        return $this->hasMany(Universe::class);
    }

    public function contentBlocks()
    {
        return $this->hasMany(ContentBlock::class);
    }

    public function testimonials()
    {
        return $this->hasMany(Testimonial::class);
    }

    public function faqItems()
    {
        return $this->hasMany(FaqItem::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public static function fromDomain(?string $domain): ?self
    {
        if (!$domain) return null;
        return static::where('domain', $domain)->orWhere('domain', 'https://' . $domain)->first();
    }
}
