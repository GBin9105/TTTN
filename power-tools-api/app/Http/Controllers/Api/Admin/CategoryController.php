<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Api\Concerns\ApiResponse;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    use ApiResponse;

    public function index(Request $request)
    {
        $query = Category::query();

        if ($request->filled('q')) {
            $q = trim($request->q);
            $query->where(function ($sub) use ($q) {
                $sub->where('name', 'like', "%{$q}%")
                    ->orWhere('slug', 'like', "%{$q}%");
            });
        }

        if ($request->filled('parent_id')) {
            $query->where('parent_id', $request->parent_id);
        }

        if ($request->filled('status')) {
            $query->where('status', filter_var($request->status, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? $request->status);
        }

        $categories = $query->latest('id')->paginate((int) $request->get('per_page', 10));

        return $this->success($categories, 'Categories fetched successfully.');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'parent_id'    => ['nullable', 'exists:categories,id'],
            'name'         => ['required', 'string', 'max:255'],
            'slug'         => ['nullable', 'string', 'max:255', 'unique:categories,slug'],
            'description'  => ['nullable', 'string'],
            'image'        => ['nullable', 'string', 'max:255'],
            'status'       => ['nullable', 'boolean'],
            'sort_order'   => ['nullable', 'integer'],
        ]);

        $data['slug'] = $data['slug'] ?? Str::slug($data['name']);
        $data['status'] = $data['status'] ?? true;
        $data['sort_order'] = $data['sort_order'] ?? 0;

        $category = Category::create($data);

        return $this->success($category, 'Category created successfully.', 201);
    }

    public function show(Category $category)
    {
        return $this->success($category, 'Category detail fetched successfully.');
    }

    public function update(Request $request, Category $category)
    {
        $data = $request->validate([
            'parent_id'    => ['nullable', 'exists:categories,id'],
            'name'         => ['sometimes', 'required', 'string', 'max:255'],
            'slug'         => ['nullable', 'string', 'max:255', 'unique:categories,slug,' . $category->id],
            'description'  => ['nullable', 'string'],
            'image'        => ['nullable', 'string', 'max:255'],
            'status'       => ['nullable', 'boolean'],
            'sort_order'   => ['nullable', 'integer'],
        ]);

        if (isset($data['name']) && empty($data['slug'])) {
            $data['slug'] = Str::slug($data['name']);
        }

        $category->update($data);

        return $this->success($category->fresh(), 'Category updated successfully.');
    }

    public function destroy(Category $category)
    {
        $category->delete();

        return $this->success(null, 'Category deleted successfully.');
    }
}