<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PageView extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'site', 'url', 'path', 'title', 'referrer', 'ip', 'user_agent', 'country', 'device', 'visited_at',
    ];

    protected function casts(): array
    {
        return ['visited_at' => 'datetime'];
    }
}
