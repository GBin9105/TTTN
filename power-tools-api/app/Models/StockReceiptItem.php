<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockReceiptItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'stock_receipt_id',
        'product_id',
        'quantity',
        'import_price',
        'line_total',
    ];

    protected function casts(): array
    {
        return [
            'stock_receipt_id' => 'integer',
            'product_id' => 'integer',
            'quantity' => 'integer',
            'import_price' => 'decimal:2',
            'line_total' => 'decimal:2',
        ];
    }

    public function stockReceipt(): BelongsTo
    {
        return $this->belongsTo(StockReceipt::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}