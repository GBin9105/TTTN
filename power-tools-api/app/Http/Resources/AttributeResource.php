<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AttributeResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,

            'name' => $this->name,
            'slug' => $this->slug,
            'code' => $this->code,
            'type' => $this->type,

            'is_filterable' => (bool) $this->is_filterable,
            'is_variant' => (bool) $this->is_variant,
            'status' => (bool) $this->status,
            'sort_order' => (int) $this->sort_order,

            'description' => $this->description,

            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}