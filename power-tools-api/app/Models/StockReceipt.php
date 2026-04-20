<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StockReceipt extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'supplier_name',
        'supplier_phone',
        'supplier_email',
        'supplier_address',
        'total_quantity',
        'total_amount',
        'note',
        'status',
        'created_by',
        'imported_at',
    ];

    protected function casts(): array
    {
        return [
            'total_quantity' => 'integer',
            'total_amount' => 'decimal:2',
            'created_by' => 'integer',
            'imported_at' => 'datetime',
        ];
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(StockReceiptItem::class);
    }
}