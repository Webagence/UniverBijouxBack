<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderDiscount extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'order_id',
        'discount_id',
        'code',
        'type',
        'value',
        'amount_ht',
    ];

    protected function casts(): array
    {
        return [
            'value' => 'decimal:2',
            'amount_ht' => 'decimal:2',
        ];
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function discount()
    {
        return $this->belongsTo(Discount::class);
    }
}
