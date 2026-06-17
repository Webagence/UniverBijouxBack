<?php

namespace App\Models;

use App\Traits\Translatable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FaqItem extends Model
{
    use HasFactory, HasUuids, Translatable;

    protected $fillable = [
        'site_id',
        'question',
        'answer',
        'category',
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

    public function site()
    {
        return $this->belongsTo(Site::class);
    }

    public function scopeActive($query)
    {
        return $query->where('active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('display_order')->orderBy('created_at', 'desc');
    }

    public function scopeByCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    public function scopeBySite($query, $siteId)
    {
        return $query->where('site_id', $siteId);
    }
}
