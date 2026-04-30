<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContentBlock extends Model
{
    use HasFactory;

    protected $fillable = [
        'key',
        'data',
    ];

    protected function casts(): array
    {
        return [
            'data' => 'array',
        ];
    }

    public static function getByKey(string $key): ?self
    {
        return static::where('key', $key)->first();
    }

    public function getDataAttribute(mixed $value): array
    {
        return json_decode($value ?? '{}', true) ?? [];
    }
}
