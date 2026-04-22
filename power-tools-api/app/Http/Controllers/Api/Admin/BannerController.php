<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Api\Concerns\ApiResponse;
use App\Models\Banner;
use Illuminate\Http\Request;

class BannerController extends Controller
{
    use ApiResponse;

    public function index(Request $request)
    {
        $query = Banner::query();

        if ($request->filled('q')) {
            $q = trim($request->q);
            $query->where(function ($sub) use ($q) {
                $sub->where('title', 'like', "%{$q}%")
                    ->orWhere('link', 'like', "%{$q}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', filter_var($request->status, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? $request->status);
        }

        $banners = $query->orderBy('sort_order')->latest('id')->paginate((int) $request->get('per_page', 10));

        return $this->success($banners, 'Banners fetched successfully.');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title'       => ['required', 'string', 'max:255'],
            'image'       => ['required', 'string', 'max:255'],
            'link'        => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'status'      => ['nullable', 'boolean'],
            'sort_order'  => ['nullable', 'integer'],
        ]);

        $data['status'] = $data['status'] ?? true;
        $data['sort_order'] = $data['sort_order'] ?? 0;

        $banner = Banner::create($data);

        return $this->success($banner, 'Banner created successfully.', 201);
    }

    public function show(Banner $banner)
    {
        return $this->success($banner, 'Banner detail fetched successfully.');
    }

    public function update(Request $request, Banner $banner)
    {
        $data = $request->validate([
            'title'       => ['sometimes', 'required', 'string', 'max:255'],
            'image'       => ['nullable', 'string', 'max:255'],
            'link'        => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'status'      => ['nullable', 'boolean'],
            'sort_order'  => ['nullable', 'integer'],
        ]);

        $banner->update($data);

        return $this->success($banner->fresh(), 'Banner updated successfully.');
    }

    public function updateSortOrder(Request $request, Banner $banner)
    {
        $data = $request->validate([
            'sort_order' => ['required', 'integer'],
        ]);

        $banner->update([
            'sort_order' => $data['sort_order'],
        ]);

        return $this->success($banner->fresh(), 'Banner sort order updated successfully.');
    }

    public function destroy(Banner $banner)
    {
        $banner->delete();

        return $this->success(null, 'Banner deleted successfully.');
    }
}