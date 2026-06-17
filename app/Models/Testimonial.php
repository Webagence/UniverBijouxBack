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
        'site_id',
        'author',
        'role',
        'shop',
        'quote',
        'rating',
        'display_order',
        'active',
        'needs_translation',
        'submitted_by',
        'submitted_at',
    ];

    protected function casts(): array
    {
        return [
            'active' => 'boolean',
            'needs_translation' => 'boolean',
            'submitted_at' => 'datetime',
        ];
    }

    public function site()
    {
        return $this->belongsTo(Site::class);
    }

    public function submittedBy()
    {
        return $this->belongsTo(User::class, 'submitted_by');
    }

    public function scopeActive($query)
    {
        return $query->where('active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('display_order')->orderBy('created_at', 'desc');
    }

    public function scopePending($query)
    {
        return $query->where('active', false);
    }

    public function scopeBySite($query, $siteId)
    {
        return $query->where('site_id', $siteId);
    }
}
