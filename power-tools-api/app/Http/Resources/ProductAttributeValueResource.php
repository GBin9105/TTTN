<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductAttributeValueResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $attributeType = $this->whenLoaded('attribute', function () {
            return $this->attribute?->type;
        });

        $displayValue = $this->display_value;

        return [
            'id' => $this->id,
            'product_id' => $this->product_id,
            'attribute_id' => $this->attribute_id,

            'attribute' => $this->whenLoaded('attribute', function () {
                return [
                    'id' => $this->attribute?->id,
                    'name' => $this->attribute?->name,
                    'slug' => $this->attribute?->slug,
                    'code' => $this->attribute?->code,
                    'type' => $this->attribute?->type,
                    'is_filterable' => (bool) $this->attribute?->is_filterable,
                    'is_variant' => (bool) $this->attribute?->is_variant,
                ];
            }),

            'value' => $this->value,
            'numeric_value' => $this->numeric_value,
            'boolean_value' => $this->boolean_value,

            'display_value' => $displayValue,
            'display_type' => $attributeType,

            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}