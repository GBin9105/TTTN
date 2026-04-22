<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Api\Concerns\ApiResponse;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    use ApiResponse;

    public function index(Request $request)
    {
        $query = Product::query()
            ->with(['brand', 'category', 'images', 'attributes']);

        if ($request->filled('q')) {
            $q = trim($request->q);

            $query->where(function ($sub) use ($q) {
                $sub->where('name', 'like', "%{$q}%")
                    ->orWhere('slug', 'like', "%{$q}%")
                    ->orWhere('sku', 'like', "%{$q}%");
            });
        }

        if ($request->filled('brand_id')) {
            $query->where('brand_id', $request->brand_id);
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->filled('status')) {
            $status = filter_var(
                $request->status,
                FILTER_VALIDATE_BOOLEAN,
                FILTER_NULL_ON_FAILURE
            );

            $query->where('status', $status ?? $request->status);
        }

        if ($request->filled('is_featured')) {
            $isFeatured = filter_var(
                $request->is_featured,
                FILTER_VALIDATE_BOOLEAN,
                FILTER_NULL_ON_FAILURE
            );

            $query->where('is_featured', $isFeatured ?? $request->is_featured);
        }

        if ($request->filled('is_new')) {
            $isNew = filter_var(
                $request->is_new,
                FILTER_VALIDATE_BOOLEAN,
                FILTER_NULL_ON_FAILURE
            );

            $query->where('is_new', $isNew ?? $request->is_new);
        }

        $products = $query
            ->latest('id')
            ->paginate((int) $request->get('per_page', 10));

        return $this->success($products, 'Products fetched successfully.');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'brand_id'          => ['nullable', 'exists:brands,id'],
            'category_id'       => ['nullable', 'exists:categories,id'],
            'name'              => ['required', 'string', 'max:255'],
            'slug'              => ['nullable', 'string', 'max:255', 'unique:products,slug'],
            'sku'               => ['nullable', 'string', 'max:100', 'unique:products,sku'],
            'short_description' => ['nullable', 'string'],
            'description'       => ['nullable', 'string'],
            'content'           => ['nullable', 'string'],
            'thumbnail'         => ['nullable', 'string', 'max:255'],
            'price'             => ['required', 'numeric', 'min:0'],
            'sale_price'        => ['nullable', 'numeric', 'min:0'],
            'stock_quantity'    => ['nullable', 'integer', 'min:0'],
            'status'            => ['nullable', 'boolean'],
            'is_featured'       => ['nullable', 'boolean'],
            'is_new'            => ['nullable', 'boolean'],
            'meta_title'        => ['nullable', 'string', 'max:255'],
            'meta_description'  => ['nullable', 'string'],
            'meta_keywords'     => ['nullable', 'string'],
            'attribute_ids'     => ['nullable', 'array'],
            'attribute_ids.*'   => ['integer', 'exists:attributes,id'],
        ]);

        $data['slug'] = $data['slug'] ?? Str::slug($data['name']);
        $data['status'] = $data['status'] ?? true;
        $data['is_featured'] = $data['is_featured'] ?? false;
        $data['is_new'] = $data['is_new'] ?? false;
        $data['stock_quantity'] = $data['stock_quantity'] ?? 0;

        $attributeIds = $data['attribute_ids'] ?? [];
        unset($data['attribute_ids']);

        $product = Product::create($data);

        if (!empty($attributeIds) && method_exists($product, 'attributes')) {
            $product->attributes()->sync($attributeIds);
        }

        return $this->success(
            $product->load(['brand', 'category', 'images', 'attributes']),
            'Product created successfully.',
            201
        );
    }

    public function show(Product $product)
    {
        return $this->success(
            $product->load(['brand', 'category', 'images', 'attributes']),
            'Product detail fetched successfully.'
        );
    }

    public function update(Request $request, Product $product)
    {
        $data = $request->validate([
            'brand_id'          => ['nullable', 'exists:brands,id'],
            'category_id'       => ['nullable', 'exists:categories,id'],
            'name'              => ['sometimes', 'required', 'string', 'max:255'],
            'slug'              => ['nullable', 'string', 'max:255', 'unique:products,slug,' . $product->id],
            'sku'               => ['nullable', 'string', 'max:100', 'unique:products,sku,' . $product->id],
            'short_description' => ['nullable', 'string'],
            'description'       => ['nullable', 'string'],
            'content'           => ['nullable', 'string'],
            'thumbnail'         => ['nullable', 'string', 'max:255'],
            'price'             => ['nullable', 'numeric', 'min:0'],
            'sale_price'        => ['nullable', 'numeric', 'min:0'],
            'stock_quantity'    => ['nullable', 'integer', 'min:0'],
            'status'            => ['nullable', 'boolean'],
            'is_featured'       => ['nullable', 'boolean'],
            'is_new'            => ['nullable', 'boolean'],
            'meta_title'        => ['nullable', 'string', 'max:255'],
            'meta_description'  => ['nullable', 'string'],
            'meta_keywords'     => ['nullable', 'string'],
            'attribute_ids'     => ['nullable', 'array'],
            'attribute_ids.*'   => ['integer', 'exists:attributes,id'],
        ]);

        if (isset($data['name']) && empty($data['slug'])) {
            $data['slug'] = Str::slug($data['name']);
        }

        $attributeIds = null;
        if (array_key_exists('attribute_ids', $data)) {
            $attributeIds = $data['attribute_ids'] ?? [];
            unset($data['attribute_ids']);
        }

        $product->update($data);

        if ($attributeIds !== null && method_exists($product, 'attributes')) {
            $product->attributes()->sync($attributeIds);
        }

        return $this->success(
            $product->fresh()->load(['brand', 'category', 'images', 'attributes']),
            'Product updated successfully.'
        );
    }

    public function destroy(Product $product)
    {
        if (method_exists($product, 'attributes')) {
            $product->attributes()->detach();
        }

        if (method_exists($product, 'images')) {
            $product->images()->delete();
        }

        $product->delete();

        return $this->success(null, 'Product deleted successfully.');
    }
}