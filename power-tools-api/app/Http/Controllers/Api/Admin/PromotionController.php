<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Api\Concerns\ApiResponse;
use App\Models\Promotion;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PromotionController extends Controller
{
    use ApiResponse;

    public function index(Request $request)
    {
        $query = Promotion::query();

        if ($request->filled('q')) {
            $q = trim($request->q);
            $query->where(function ($sub) use ($q) {
                $sub->where('name', 'like', "%{$q}%")
                    ->orWhere('slug', 'like', "%{$q}%")
                    ->orWhere('code', 'like', "%{$q}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', filter_var($request->status, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? $request->status);
        }

        $promotions = $query->latest('id')->paginate((int) $request->get('per_page', 10));

        return $this->success($promotions, 'Promotions fetched successfully.');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'           => ['required', 'string', 'max:255'],
            'slug'           => ['nullable', 'string', 'max:255', 'unique:promotions,slug'],
            'code'           => ['nullable', 'string', 'max:100', 'unique:promotions,code'],
            'type'           => ['required', 'string', 'max:50'],
            'value'          => ['required', 'numeric', 'min:0'],
            'description'    => ['nullable', 'string'],
            'starts_at'      => ['nullable', 'date'],
            'ends_at'        => ['nullable', 'date', 'after_or_equal:starts_at'],
            'status'         => ['nullable', 'boolean'],
            'product_ids'    => ['nullable', 'array'],
            'product_ids.*'  => ['integer', 'exists:products,id'],
        ]);

        $promotion = Promotion::create([
            'name'        => $data['name'],
            'slug'        => $data['slug'] ?? Str::slug($data['name']),
            'code'        => $data['code'] ?? strtoupper(Str::random(8)),
            'type'        => $data['type'],
            'value'       => $data['value'],
            'description' => $data['description'] ?? null,
            'starts_at'   => $data['starts_at'] ?? null,
            'ends_at'     => $data['ends_at'] ?? null,
            'status'      => $data['status'] ?? true,
        ]);

        if (!empty($data['product_ids'])) {
            $promotion->products()->sync($data['product_ids']);
        }

        return $this->success(
            $promotion->load('products'),
            'Promotion created successfully.',
            201
        );
    }

    public function show(Promotion $promotion)
    {
        return $this->success($promotion->load('products'), 'Promotion detail fetched successfully.');
    }

    public function update(Request $request, Promotion $promotion)
    {
        $data = $request->validate([
            'name'           => ['sometimes', 'required', 'string', 'max:255'],
            'slug'           => ['nullable', 'string', 'max:255', 'unique:promotions,slug,' . $promotion->id],
            'code'           => ['nullable', 'string', 'max:100', 'unique:promotions,code,' . $promotion->id],
            'type'           => ['nullable', 'string', 'max:50'],
            'value'          => ['nullable', 'numeric', 'min:0'],
            'description'    => ['nullable', 'string'],
            'starts_at'      => ['nullable', 'date'],
            'ends_at'        => ['nullable', 'date', 'after_or_equal:starts_at'],
            'status'         => ['nullable', 'boolean'],
            'product_ids'    => ['nullable', 'array'],
            'product_ids.*'  => ['integer', 'exists:products,id'],
        ]);

        if (isset($data['name']) && empty($data['slug'])) {
            $data['slug'] = Str::slug($data['name']);
        }

        $promotion->update($data);

        if (array_key_exists('product_ids', $data)) {
            $promotion->products()->sync($data['product_ids'] ?? []);
        }

        return $this->success(
            $promotion->fresh()->load('products'),
            'Promotion updated successfully.'
        );
    }

    public function destroy(Promotion $promotion)
    {
        $promotion->products()->detach();
        $promotion->delete();

        return $this->success(null, 'Promotion deleted successfully.');
    }
}