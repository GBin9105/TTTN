<?php

namespace App\Services;

use App\Models\Banner;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class BannerService
{
    public function index(array $filters = []): LengthAwarePaginator
    {
        $perPage = (int) ($filters['per_page'] ?? 15);

        $query = Banner::query();

        if (! empty($filters['q'])) {
            $q = trim($filters['q']);
            $query->where('title', 'like', "%{$q}%");
        }

        if (! empty($filters['position'])) {
            $query->where('position', $filters['position']);
        }

        if (isset($filters['status']) && $filters['status'] !== '') {
            $query->where('status', filter_var($filters['status'], FILTER_VALIDATE_BOOLEAN));
        }

        return $query
            ->orderBy('sort_order')
            ->orderByDesc('id')
            ->paginate($perPage);
    }

    public function show(Banner $banner): Banner
    {
        return $banner;
    }

    public function store(array $data): Banner
    {
        return Banner::create([
            'title' => $data['title'],
            'image' => $data['image'],
            'link_url' => $data['link_url'] ?? null,
            'position' => $data['position'] ?? 'home',
            'sort_order' => $data['sort_order'] ?? 0,
            'status' => $data['status'] ?? true,
            'start_at' => $data['start_at'] ?? null,
            'end_at' => $data['end_at'] ?? null,
        ]);
    }

    public function update(Banner $banner, array $data): Banner
    {
        $banner->update([
            'title' => $data['title'] ?? $banner->title,
            'image' => $data['image'] ?? $banner->image,
            'link_url' => array_key_exists('link_url', $data) ? $data['link_url'] : $banner->link_url,
            'position' => $data['position'] ?? $banner->position,
            'sort_order' => $data['sort_order'] ?? $banner->sort_order,
            'status' => $data['status'] ?? $banner->status,
            'start_at' => array_key_exists('start_at', $data) ? $data['start_at'] : $banner->start_at,
            'end_at' => array_key_exists('end_at', $data) ? $data['end_at'] : $banner->end_at,
        ]);

        return $banner->fresh();
    }

    public function updateSortOrder(Banner $banner, int $sortOrder): Banner
    {
        $banner->update([
            'sort_order' => $sortOrder,
        ]);

        return $banner->fresh();
    }

    public function delete(Banner $banner): bool
    {
        return (bool) $banner->delete();
    }
}