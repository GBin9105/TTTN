<?php

namespace App\Services;

use App\Models\Category;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class CategoryService
{
    public function paginate(array $filters = []): LengthAwarePaginator
    {
        $query = Category::query()->with('parent');

        if (!empty($filters['keyword'])) {
            $keyword = trim($filters['keyword']);

            $query->where(function ($q) use ($keyword) {
                $q->where('name', 'like', "%{$keyword}%")
                    ->orWhere('slug', 'like', "%{$keyword}%");
            });
        }

        if (!empty($filters['parent_id'])) {
            $query->where('parent_id', $filters['parent_id']);
        }

        if (array_key_exists('status', $filters) && $filters['status'] !== '' && $filters['status'] !== null) {
            $status = filter_var($filters['status'], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
            if ($status !== null) {
                $query->where('status', $status);
            }
        }

        $allowedSortBy = ['id', 'name', 'sort_order', 'status', 'created_at'];
        $sortBy = in_array(($filters['sort_by'] ?? 'sort_order'), $allowedSortBy, true)
            ? $filters['sort_by']
            : 'sort_order';

        $sortOrder = strtolower($filters['sort_order'] ?? 'asc');
        $sortOrder = in_array($sortOrder, ['asc', 'desc'], true) ? $sortOrder : 'asc';

        $perPage = max(1, min((int)($filters['per_page'] ?? 10), 100));

        return $query
            ->orderBy($sortBy, $sortOrder)
            ->orderBy('id', 'desc')
            ->paginate($perPage)
            ->appends($filters);
    }

    public function create(array $data): Category
    {
        if (isset($data['image']) && $data['image'] instanceof UploadedFile) {
            $data['image'] = $this->storeImage($data['image']);
        }

        return Category::create($data);
    }

    public function update(Category $category, array $data): Category
    {
        if (isset($data['image']) && $data['image'] instanceof UploadedFile) {
            $this->deleteFileIfExists($category->image);
            $data['image'] = $this->storeImage($data['image']);
        }

        $category->update($data);

        return $category->fresh();
    }

    public function delete(Category $category): bool
    {
        $this->deleteFileIfExists($category->image);

        return (bool) $category->delete();
    }

    protected function storeImage(UploadedFile $file): string
    {
        return '/storage/' . $file->store('categories', 'public');
    }

    protected function deleteFileIfExists(?string $path): void
    {
        if (!$path) {
            return;
        }

        $relativePath = ltrim(str_replace('/storage/', '', $path), '/');

        if ($relativePath && Storage::disk('public')->exists($relativePath)) {
            Storage::disk('public')->delete($relativePath);
        }
    }
}