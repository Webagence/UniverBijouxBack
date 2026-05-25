<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TranslationJob extends Model
{
    use HasFactory;

    protected $fillable = [
        'batch_id',
        'model_type',
        'model_id',
        'locale',
        'attempts',
        'processed_at',
    ];

    protected function casts(): array
    {
        return [
            'processed_at' => 'datetime',
            'attempts' => 'integer',
        ];
    }

    public function batch(): BelongsTo
    {
        return $this->belongsTo(TranslationBatch::class, 'batch_id');
    }

    public function markAsProcessed(): void
    {
        $this->update(['processed_at' => now()]);
    }

    public function incrementAttempts(): void
    {
        $this->increment('attempts');
    }
}
