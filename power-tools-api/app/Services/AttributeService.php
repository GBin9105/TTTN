<?php

namespace App\Services;

use App\Models\Attribute;
use App\Models\Product;
use App\Models\ProductAttributeValue;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class AttributeService
{
    /**
     * Danh sách thuộc tính có lọc / tìm kiếm / sắp xếp / phân trang
     */
    public function paginate(array $filters = []): LengthAwarePaginator
    {
        $query = Attribute::query();

        if (! empty($filters['keyword'])) {
            $keyword = trim($filters['keyword']);

            $query->where(function ($q) use ($keyword) {
                $q->where('name', 'like', "%{$keyword}%")
                    ->orWhere('slug', 'like', "%{$keyword}%")
                    ->orWhere('code', 'like', "%{$keyword}%");
            });
        }

        if (array_key_exists('status', $filters) && $filters['status'] !== null && $filters['status'] !== '') {
            $status = filter_var($filters['status'], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
            if ($status !== null) {
                $query->where('status', $status);
            }
        }

        if (! empty($filters['type'])) {
            $query->where('type', $filters['type']);
        }

        if (array_key_exists('is_filterable', $filters) && $filters['is_filterable'] !== null && $filters['is_filterable'] !== '') {
            $isFilterable = filter_var($filters['is_filterable'], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
            if ($isFilterable !== null) {
                $query->where('is_filterable', $isFilterable);
            }
        }

        if (array_key_exists('is_variant', $filters) && $filters['is_variant'] !== null && $filters['is_variant'] !== '') {
            $isVariant = filter_var($filters['is_variant'], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
            if ($isVariant !== null) {
                $query->where('is_variant', $isVariant);
            }
        }

        $allowedSortBy = ['id', 'name', 'type', 'status', 'sort_order', 'created_at', 'updated_at'];
        $sortBy = in_array(($filters['sort_by'] ?? 'sort_order'), $allowedSortBy, true)
            ? $filters['sort_by']
            : 'sort_order';

        $sortOrder = strtolower($filters['sort_order'] ?? 'asc');
        $sortOrder = in_array($sortOrder, ['asc', 'desc'], true) ? $sortOrder : 'asc';

        $perPage = (int) ($filters['per_page'] ?? 10);
        $perPage = $perPage > 0 ? min($perPage, 100) : 10;

        return $query
            ->orderBy($sortBy, $sortOrder)
            ->orderBy('id', 'desc')
            ->paginate($perPage)
            ->appends($filters);
    }

    /**
     * Tạo thuộc tính
     */
    public function create(array $data): Attribute
    {
        return Attribute::create([
            'name' => $data['name'],
            'slug' => $data['slug'] ?? null,
            'code' => $data['code'] ?? null,
            'type' => $data['type'],
            'is_filterable' => $data['is_filterable'] ?? false,
            'is_variant' => $data['is_variant'] ?? false,
            'status' => $data['status'] ?? true,
            'sort_order' => $data['sort_order'] ?? 0,
            'description' => $data['description'] ?? null,
        ]);
    }

    /**
     * Cập nhật thuộc tính
     */
    public function update(Attribute $attribute, array $data): Attribute
    {
        $attribute->update([
            'name' => $data['name'],
            'slug' => $data['slug'] ?? $attribute->slug,
            'code' => $data['code'] ?? $attribute->code,
            'type' => $data['type'],
            'is_filterable' => $data['is_filterable'] ?? false,
            'is_variant' => $data['is_variant'] ?? false,
            'status' => $data['status'] ?? true,
            'sort_order' => $data['sort_order'] ?? 0,
            'description' => $data['description'] ?? null,
        ]);

        return $attribute->fresh();
    }

    /**
     * Xóa mềm thuộc tính
     */
    public function delete(Attribute $attribute): bool
    {
        return (bool) $attribute->delete();
    }

    /**
     * Gán / đồng bộ thuộc tính cho sản phẩm
     */
    public function syncProductAttributes(Product $product, array $attributes): Product
    {
        DB::transaction(function () use ($product, $attributes) {
            ProductAttributeValue::where('product_id', $product->id)->delete();

            foreach ($attributes as $item) {
                ProductAttributeValue::create([
                    'product_id' => $product->id,
                    'attribute_id' => $item['attribute_id'],
                    'value' => $item['value'] ?? null,
                    'numeric_value' => $item['numeric_value'] ?? null,
                    'boolean_value' => $item['boolean_value'] ?? null,
                ]);
            }
        });

        return $product->load(['attributeValues.attribute']);
    }

    /**
     * Lấy thuộc tính theo sản phẩm
     */
    public function getProductAttributes(Product $product): Product
    {
        return $product->load(['attributeValues.attribute']);
    }
}