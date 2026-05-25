<?php

namespace App\Models;

use App\Traits\Translatable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Testimonial extends Model
{
    use HasFactory, HasUuids, Translatable;

    protected $fillable = [
        'author',
        'role',
        'shop',
        'quote',
        'rating',
        'display_order',
        'active',
        'needs_translation',
    ];

    protected function casts(): array
    {
        return [
            'active' => 'boolean',
            'needs_translation' => 'boolean',
        ];
    }

    public function scopeActive($query)
    {
        return $query->where('active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('display_order')->orderBy('created_at', 'desc');
    }
}
