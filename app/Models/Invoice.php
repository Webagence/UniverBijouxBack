<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'invoice_number',
        'order_id',
        'user_id',
        'pdf_path',
        'total_ht',
        'vat_amount',
        'total_ttc',
        'issued_at',
    ];

    protected function casts(): array
    {
        return [
            'total_ht' => 'decimal:2',
            'vat_amount' => 'decimal:2',
            'total_ttc' => 'decimal:2',
            'issued_at' => 'datetime',
        ];
    }

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function ($invoice) {
            if (empty($invoice->invoice_number)) {
                $invoice->invoice_number = 'FAC-' . now()->format('Ymd') . '-' . str_pad((string) random_int(1, 9999), 4, '0', STR_PAD_LEFT);
            }
            if (empty($invoice->issued_at)) {
                $invoice->issued_at = now();
            }
        });
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
