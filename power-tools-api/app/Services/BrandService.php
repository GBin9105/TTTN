<?php

namespace App\Services;

use App\Models\Brand;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class BrandService
{
    public function paginate(array $filters = []): LengthAwarePaginator
    {
        $query = Brand::query();

        if (!empty($filters['keyword'])) {
            $keyword = trim($filters['keyword']);

            $query->where(function ($q) use ($keyword) {
                $q->where('name', 'like', "%{$keyword}%")
                    ->orWhere('slug', 'like', "%{$keyword}%")
                    ->orWhere('website', 'like', "%{$keyword}%");
            });
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

    public function create(array $data): Brand
    {
        if (isset($data['logo']) && $data['logo'] instanceof UploadedFile) {
            $data['logo'] = $this->storeLogo($data['logo']);
        }

        return Brand::create($data);
    }

    public function update(Brand $brand, array $data): Brand
    {
        if (isset($data['logo']) && $data['logo'] instanceof UploadedFile) {
            $this->deleteFileIfExists($brand->logo);
            $data['logo'] = $this->storeLogo($data['logo']);
        }

        $brand->update($data);

        return $brand->fresh();
    }

    public function delete(Brand $brand): bool
    {
        $this->deleteFileIfExists($brand->logo);

        return (bool) $brand->delete();
    }

    protected function storeLogo(UploadedFile $file): string
    {
        return '/storage/' . $file->store('brands', 'public');
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