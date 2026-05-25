<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Locale extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'native_name',
        'flag_emoji',
        'is_active',
        'is_default',
        'direction',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'is_default' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function translations(): HasMany
    {
        return $this->hasMany(Translation::class, 'locale', 'code');
    }

    public static function default(): ?self
    {
        return static::where('is_default', true)->first();
    }

    public static function active(): \Illuminate\Database\Eloquent\Builder
    {
        return static::where('is_active', true)->orderBy('sort_order');
    }

    public static function activeCodes(): array
    {
        return static::active()->pluck('code')->toArray();
    }

    public static function nonDefaultActive(): \Illuminate\Database\Eloquent\Builder
    {
        return static::active()->where('is_default', false);
    }
}
