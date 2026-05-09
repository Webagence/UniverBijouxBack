<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShippingboSyncLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'action',
        'entity_type',
        'entity_id',
        'status',
        'request_data',
        'response_data',
        'error_message',
        'attempt',
        'synced_at',
    ];

    protected function casts(): array
    {
        return [
            'request_data' => 'array',
            'response_data' => 'array',
            'synced_at' => 'datetime',
        ];
    }

    public static function log(string $action, string $entityType, string $entityId, string $status, array $requestData = [], array $responseData = [], ?string $errorMessage = null, int $attempt = 1): self
    {
        return self::create([
            'action' => $action,
            'entity_type' => $entityType,
            'entity_id' => $entityId,
            'status' => $status,
            'request_data' => $requestData,
            'response_data' => $responseData,
            'error_message' => $errorMessage,
            'attempt' => $attempt,
            'synced_at' => $status === 'success' ? now() : null,
        ]);
    }

    public function scopeRecent($query, int $days = 7)
    {
        return $query->where('created_at', '>=', now()->subDays($days))->orderByDesc('created_at');
    }

    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    public function scopeByAction($query, string $action)
    {
        return $query->where('action', $action);
    }
}
