<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PromotionProduct extends Model
{
    use HasFactory;

    protected $table = 'product_promotion';

    protected $fillable = [
        'product_id',
        'promotion_id',
    ];

    protected function casts(): array
    {
        return [
            'product_id' => 'integer',
            'promotion_id' => 'integer',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function promotion(): BelongsTo
    {
        return $this->belongsTo(Promotion::class);
    }
}