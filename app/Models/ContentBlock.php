<?php

namespace App\Models;

use App\Traits\Translatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContentBlock extends Model
{
    use HasFactory, Translatable;

    protected $fillable = [
        'site_id',
        'key',
        'data',
    ];

    protected function casts(): array
    {
        return [
            'data' => 'array',
        ];
    }

    public function site()
    {
        return $this->belongsTo(Site::class);
    }

    public function scopeBySite($query, $siteId)
    {
        return $query->where('site_id', $siteId);
    }

    public static function getByKey(string $key, ?string $siteId = null): ?self
    {
        $q = static::where('key', $key);
        if ($siteId) {
            $q->where('site_id', $siteId);
        }
        return $q->first();
    }

    public function getDataAttribute(mixed $value): array
    {
        if (is_array($value)) return $value;
        return json_decode($value ?? '{}', true) ?? [];
    }
}
