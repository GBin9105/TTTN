<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Attribute extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'attributes';

    protected $fillable = [
        'name',
        'slug',
        'code',
        'type',
        'is_filterable',
        'is_variant',
        'status',
        'sort_order',
        'description',
    ];

    protected $hidden = [
        'deleted_at',
    ];

    protected function casts(): array
    {
        return [
            'is_filterable' => 'boolean',
            'is_variant' => 'boolean',
            'status' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (self $attribute) {
            if (blank($attribute->slug) && filled($attribute->name)) {
                $attribute->slug = Str::slug($attribute->name);
            }

            if (blank($attribute->code) && filled($attribute->name)) {
                $attribute->code = Str::slug($attribute->name);
            }
        });

        static::updating(function (self $attribute) {
            if (blank($attribute->slug) && filled($attribute->name)) {
                $attribute->slug = Str::slug($attribute->name);
            }

            if (blank($attribute->code) && filled($attribute->name)) {
                $attribute->code = Str::slug($attribute->name);
            }
        });
    }

    public function productAttributeValues(): HasMany
    {
        return $this->hasMany(ProductAttributeValue::class, 'attribute_id');
    }

    public function isTextType(): bool
    {
        return $this->type === 'text';
    }

    public function isNumberType(): bool
    {
        return $this->type === 'number';
    }

    public function isBooleanType(): bool
    {
        return $this->type === 'boolean';
    }

    public function isSelectType(): bool
    {
        return $this->type === 'select';
    }

    public function isActive(): bool
    {
        return (bool) $this->status;
    }
}