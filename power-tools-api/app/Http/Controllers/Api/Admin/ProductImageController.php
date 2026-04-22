<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Api\Concerns\ApiResponse;
use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Http\Request;

class ProductImageController extends Controller
{
    use ApiResponse;

    public function store(Request $request, Product $product)
    {
        $data = $request->validate([
            'image'      => ['required', 'string', 'max:255'],
            'alt'        => ['nullable', 'string', 'max:255'],
            'is_primary' => ['nullable', 'boolean'],
            'sort_order' => ['nullable', 'integer'],
        ]);

        $data['product_id'] = $product->id;
        $data['is_primary'] = $data['is_primary'] ?? false;
        $data['sort_order'] = $data['sort_order'] ?? 0;

        if ($data['is_primary']) {
            ProductImage::where('product_id', $product->id)->update(['is_primary' => false]);
        }

        $productImage = ProductImage::create($data);

        return $this->success($productImage, 'Product image created successfully.', 201);
    }

    public function destroy(ProductImage $productImage)
    {
        $productImage->delete();

        return $this->success(null, 'Product image deleted successfully.');
    }

    public function setPrimary(ProductImage $productImage)
    {
        ProductImage::where('product_id', $productImage->product_id)->update(['is_primary' => false]);

        $productImage->update(['is_primary' => true]);

        return $this->success($productImage->fresh(), 'Primary image updated successfully.');
    }
}