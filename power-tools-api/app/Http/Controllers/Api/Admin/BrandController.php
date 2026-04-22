<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Api\Concerns\ApiResponse;
use App\Models\Brand;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class BrandController extends Controller
{
    use ApiResponse;

    public function index(Request $request)
    {
        $query = Brand::query();

        if ($request->filled('q')) {
            $q = trim($request->q);
            $query->where(function ($sub) use ($q) {
                $sub->where('name', 'like', "%{$q}%")
                    ->orWhere('slug', 'like', "%{$q}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', filter_var($request->status, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? $request->status);
        }

        $brands = $query->latest('id')->paginate((int) $request->get('per_page', 10));

        return $this->success($brands, 'Brands fetched successfully.');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'        => ['required', 'string', 'max:255'],
            'slug'        => ['nullable', 'string', 'max:255', 'unique:brands,slug'],
            'description' => ['nullable', 'string'],
            'logo'        => ['nullable', 'string', 'max:255'],
            'status'      => ['nullable', 'boolean'],
        ]);

        $data['slug'] = $data['slug'] ?? Str::slug($data['name']);
        $data['status'] = $data['status'] ?? true;

        $brand = Brand::create($data);

        return $this->success($brand, 'Brand created successfully.', 201);
    }

    public function show(Brand $brand)
    {
        return $this->success($brand, 'Brand detail fetched successfully.');
    }

    public function update(Request $request, Brand $brand)
    {
        $data = $request->validate([
            'name'        => ['sometimes', 'required', 'string', 'max:255'],
            'slug'        => ['nullable', 'string', 'max:255', 'unique:brands,slug,' . $brand->id],
            'description' => ['nullable', 'string'],
            'logo'        => ['nullable', 'string', 'max:255'],
            'status'      => ['nullable', 'boolean'],
        ]);

        if (isset($data['name']) && empty($data['slug'])) {
            $data['slug'] = Str::slug($data['name']);
        }

        $brand->update($data);

        return $this->success($brand->fresh(), 'Brand updated successfully.');
    }

    public function destroy(Brand $brand)
    {
        $brand->delete();

        return $this->success(null, 'Brand deleted successfully.');
    }
}