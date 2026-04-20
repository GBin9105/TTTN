<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductAttributeValue extends Model
{
    use HasFactory;

    protected $table = 'product_attribute_values';

    protected $fillable = [
        'product_id',
        'attribute_id',
        'value',
        'numeric_value',
        'boolean_value',
    ];

    protected function casts(): array
    {
        return [
            'numeric_value' => 'decimal:2',
            'boolean_value' => 'boolean',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function attribute(): BelongsTo
    {
        return $this->belongsTo(Attribute::class, 'attribute_id');
    }

    /**
     * Lấy giá trị hiển thị phù hợp theo loại thuộc tính
     */
    public function getDisplayValueAttribute(): string|int|float|bool|null
    {
        if (! $this->relationLoaded('attribute') || ! $this->attribute) {
            return $this->value ?? $this->numeric_value ?? $this->boolean_value;
        }

        return match ($this->attribute->type) {
            'number' => $this->numeric_value,
            'boolean' => $this->boolean_value,
            default => $this->value,
        };
    }
}