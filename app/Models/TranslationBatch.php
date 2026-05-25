<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class TranslationBatch extends Model
{
    use HasFactory;

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'name',
        'source_locale',
        'target_locale',
        'total_items',
        'completed_items',
        'failed_items',
        'status',
        'error_message',
        'started_at',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
            'total_items' => 'integer',
            'completed_items' => 'integer',
            'failed_items' => 'integer',
        ];
    }

    protected static function boot(): void
    {
        parent::boot();
        static::creating(function ($model) {
            if (!$model->id) {
                $model->id = (string) Str::uuid();
            }
        });
    }

    public function jobs(): HasMany
    {
        return $this->hasMany(TranslationJob::class, 'batch_id');
    }

    public function progress(): float
    {
        if ($this->total_items === 0) {
            return 0;
        }
        return round(($this->completed_items / $this->total_items) * 100, 2);
    }

    public function markAsStarted(): void
    {
        $this->update([
            'status' => 'processing',
            'started_at' => now(),
        ]);
    }

    public function markAsCompleted(): void
    {
        $this->update([
            'status' => 'completed',
            'completed_at' => now(),
        ]);
    }

    public function markAsFailed(string $error): void
    {
        $this->update([
            'status' => 'failed',
            'error_message' => $error,
        ]);
    }

    public function incrementCompleted(): void
    {
        $this->increment('completed_items');
        $this->refresh();

        if ($this->completed_items + $this->failed_items >= $this->total_items) {
            $this->markAsCompleted();
        }
    }

    public function incrementFailed(): void
    {
        $this->increment('failed_items');
        $this->refresh();

        if ($this->completed_items + $this->failed_items >= $this->total_items) {
            $this->markAsCompleted();
        }
    }
}
