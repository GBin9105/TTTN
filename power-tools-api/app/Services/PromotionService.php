<?php

namespace App\Services;

use App\Models\Product;
use App\Models\Promotion;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PromotionService
{
    public function index(array $filters = []): LengthAwarePaginator
    {
        $perPage = (int) ($filters['per_page'] ?? 15);
        $sort = $filters['sort'] ?? 'latest';

        $query = Promotion::query()
            ->withCount('products');

        if (! empty($filters['q'])) {
            $q = trim($filters['q']);
            $query->where(function ($subQuery) use ($q) {
                $subQuery->where('name', 'like', "%{$q}%")
                    ->orWhere('code', 'like', "%{$q}%")
                    ->orWhere('description', 'like', "%{$q}%");
            });
        }

        if (isset($filters['status']) && $filters['status'] !== '') {
            $query->where('status', filter_var($filters['status'], FILTER_VALIDATE_BOOLEAN));
        }

        if (! empty($filters['type'])) {
            $query->where('type', $filters['type']);
        }

        switch ($sort) {
            case 'oldest':
                $query->orderBy('id');
                break;
            case 'priority_asc':
                $query->orderBy('priority');
                break;
            case 'priority_desc':
                $query->orderByDesc('priority');
                break;
            default:
                $query->orderByDesc('id');
                break;
        }

        return $query->paginate($perPage);
    }

    public function show(int|string $id): ?Promotion
    {
        return Promotion::query()
            ->with('products:id,name,slug,thumbnail,price')
            ->find($id);
    }

    public function store(array $data): Promotion
    {
        return DB::transaction(function () use ($data) {
            $promotion = Promotion::create([
                'name' => $data['name'],
                'code' => $data['code'] ?? null,
                'description' => $data['description'] ?? null,
                'type' => $data['type'],
                'value' => $data['value'],
                'priority' => $data['priority'] ?? 0,
                'status' => $data['status'] ?? true,
                'start_at' => $data['start_at'] ?? null,
                'end_at' => $data['end_at'] ?? null,
            ]);

            if (! empty($data['product_ids']) && is_array($data['product_ids'])) {
                $promotion->products()->sync($data['product_ids']);
            }

            return $promotion->load('products:id,name,slug,thumbnail,price');
        });
    }

    public function update(Promotion $promotion, array $data): Promotion
    {
        return DB::transaction(function () use ($promotion, $data) {
            $promotion->update([
                'name' => $data['name'] ?? $promotion->name,
                'code' => array_key_exists('code', $data) ? $data['code'] : $promotion->code,
                'description' => array_key_exists('description', $data) ? $data['description'] : $promotion->description,
                'type' => $data['type'] ?? $promotion->type,
                'value' => $data['value'] ?? $promotion->value,
                'priority' => $data['priority'] ?? $promotion->priority,
                'status' => $data['status'] ?? $promotion->status,
                'start_at' => array_key_exists('start_at', $data) ? $data['start_at'] : $promotion->start_at,
                'end_at' => array_key_exists('end_at', $data) ? $data['end_at'] : $promotion->end_at,
            ]);

            if (array_key_exists('product_ids', $data) && is_array($data['product_ids'])) {
                $promotion->products()->sync($data['product_ids']);
            }

            return $promotion->load('products:id,name,slug,thumbnail,price');
        });
    }

    public function delete(Promotion $promotion): bool
    {
        return DB::transaction(function () use ($promotion) {
            $promotion->products()->detach();

            return (bool) $promotion->delete();
        });
    }

    public function resolveActivePromotionForProduct(Product $product): ?Promotion
    {
        if (! $product->relationLoaded('promotions')) {
            $product->load([
                'promotions' => function ($query) {
                    $query->where('status', true)
                        ->where(function ($subQuery) {
                            $subQuery->whereNull('start_at')
                                ->orWhere('start_at', '<=', now());
                        })
                        ->where(function ($subQuery) {
                            $subQuery->whereNull('end_at')
                                ->orWhere('end_at', '>=', now());
                        })
                        ->orderBy('priority');
                }
            ]);
        }

        return $product->promotions
            ->filter(fn ($promotion) => $this->isPromotionActive($promotion))
            ->sortBy('priority')
            ->first();
    }

    public function resolveActivePromotionFromCollection(Collection $promotions): ?Promotion
    {
        return $promotions
            ->filter(fn ($promotion) => $this->isPromotionActive($promotion))
            ->sortBy('priority')
            ->first();
    }

    public function calculatePricing(float $originalPrice, ?Promotion $promotion = null): array
    {
        $finalPrice = $originalPrice;
        $discountAmount = 0;

        if ($promotion) {
            switch ($promotion->type) {
                case 'fixed_amount':
                    $discountAmount = min($originalPrice, (float) $promotion->value);
                    $finalPrice = max(0, $originalPrice - $discountAmount);
                    break;

                case 'percent':
                    $discountAmount = round($originalPrice * ((float) $promotion->value / 100), 2);
                    $finalPrice = max(0, $originalPrice - $discountAmount);
                    break;

                case 'fixed_price':
                    $finalPrice = max(0, (float) $promotion->value);
                    $discountAmount = max(0, $originalPrice - $finalPrice);
                    break;
            }
        }

        return [
            'original_price' => round($originalPrice, 2),
            'final_price' => round($finalPrice, 2),
            'discount_amount' => round($discountAmount, 2),
        ];
    }

    public function validatePromotionPeriod(array $data): void
    {
        if (
            ! empty($data['start_at']) &&
            ! empty($data['end_at']) &&
            strtotime((string) $data['start_at']) > strtotime((string) $data['end_at'])
        ) {
            throw ValidationException::withMessages([
                'end_at' => ['Thời gian kết thúc phải lớn hơn hoặc bằng thời gian bắt đầu.'],
            ]);
        }
    }

    protected function isPromotionActive(Promotion $promotion): bool
    {
        if (! $promotion->status) {
            return false;
        }

        $now = now();

        if ($promotion->start_at && $now->lt($promotion->start_at)) {
            return false;
        }

        if ($promotion->end_at && $now->gt($promotion->end_at)) {
            return false;
        }

        return true;
    }
}