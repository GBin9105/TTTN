<?php

namespace App\Services;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class ProductService
{
    public function __construct(
        protected PromotionService $promotionService
    ) {}

    public function index(array $filters = []): array
    {
        $perPage = (int) ($filters['per_page'] ?? 12);
        $sort = $filters['sort'] ?? 'latest';

        $query = Product::query()
            ->with([
                'brand:id,name,slug',
                'category:id,name,slug',
                'images:id,product_id,image,alt_text,is_primary,sort_order',
                'promotions',
            ])
            ->where('status', true);

        if (! empty($filters['q'])) {
            $q = trim($filters['q']);
            $query->where(function ($subQuery) use ($q) {
                $subQuery->where('name', 'like', "%{$q}%")
                    ->orWhere('slug', 'like', "%{$q}%")
                    ->orWhere('sku', 'like', "%{$q}%")
                    ->orWhere('short_description', 'like', "%{$q}%");
            });
        }

        if (! empty($filters['category_slug'])) {
            $category = Category::where('slug', $filters['category_slug'])->first();

            if ($category) {
                $categoryIds = Category::query()
                    ->where('id', $category->id)
                    ->orWhere('parent_id', $category->id)
                    ->pluck('id');

                $query->whereIn('category_id', $categoryIds);
            }
        } elseif (! empty($filters['category_id'])) {
            $query->where('category_id', (int) $filters['category_id']);
        }

        if (! empty($filters['brand_slug'])) {
            $brand = Brand::where('slug', $filters['brand_slug'])->first();

            if ($brand) {
                $query->where('brand_id', $brand->id);
            }
        } elseif (! empty($filters['brand_id'])) {
            $query->where('brand_id', (int) $filters['brand_id']);
        }

        if (isset($filters['is_featured']) && $filters['is_featured'] !== '') {
            $query->where('is_featured', filter_var($filters['is_featured'], FILTER_VALIDATE_BOOLEAN));
        }

        if (isset($filters['is_new']) && $filters['is_new'] !== '') {
            $query->where('is_new', filter_var($filters['is_new'], FILTER_VALIDATE_BOOLEAN));
        }

        if (isset($filters['has_promotion']) && filter_var($filters['has_promotion'], FILTER_VALIDATE_BOOLEAN)) {
            $query->whereHas('promotions', function ($promotionQuery) {
                $promotionQuery->where('status', true)
                    ->where(function ($subQuery) {
                        $subQuery->whereNull('start_at')
                            ->orWhere('start_at', '<=', now());
                    })
                    ->where(function ($subQuery) {
                        $subQuery->whereNull('end_at')
                            ->orWhere('end_at', '>=', now());
                    });
            });
        }

        if ($filters['min_price'] ?? null) {
            $query->where('price', '>=', (float) $filters['min_price']);
        }

        if ($filters['max_price'] ?? null) {
            $query->where('price', '<=', (float) $filters['max_price']);
        }

        switch ($sort) {
            case 'price_asc':
                $query->orderBy('price');
                break;

            case 'price_desc':
                $query->orderByDesc('price');
                break;

            case 'popular':
                $query->orderByDesc('view_count');
                break;

            case 'name_asc':
                $query->orderBy('name');
                break;

            case 'name_desc':
                $query->orderByDesc('name');
                break;

            default:
                $query->orderByDesc('id');
                break;
        }

        $paginator = $query->paginate($perPage);

        return [
            'items' => $this->formatProductCollection(collect($paginator->items())),
            'pagination' => $this->formatPagination($paginator),
            'filters' => [
                'q' => $filters['q'] ?? null,
                'category_slug' => $filters['category_slug'] ?? null,
                'brand_slug' => $filters['brand_slug'] ?? null,
                'min_price' => $filters['min_price'] ?? null,
                'max_price' => $filters['max_price'] ?? null,
                'sort' => $sort,
            ],
        ];
    }

    public function show(string $slug): ?array
    {
        $product = Product::query()
            ->with([
                'brand:id,name,slug',
                'category:id,name,slug',
                'images:id,product_id,image,alt_text,is_primary,sort_order',
                'promotions',
                'reviews' => function ($query) {
                    $query->where('is_approved', true)
                        ->with('user:id,name,avatar')
                        ->orderByDesc('id');
                },
            ])
            ->where('status', true)
            ->where('slug', $slug)
            ->first();

        if (! $product) {
            return null;
        }

        $product->increment('view_count');
        $product->refresh();

        $relatedProducts = Product::query()
            ->with([
                'brand:id,name,slug',
                'category:id,name,slug',
                'images:id,product_id,image,alt_text,is_primary,sort_order',
                'promotions',
            ])
            ->where('status', true)
            ->where('id', '!=', $product->id)
            ->where('category_id', $product->category_id)
            ->orderByDesc('is_featured')
            ->orderByDesc('is_new')
            ->orderByDesc('id')
            ->limit(8)
            ->get();

        $promotion = $this->promotionService->resolveActivePromotionForProduct($product);
        $pricing = $this->promotionService->calculatePricing(
            (float) $product->price,
            $promotion
        );

        return [
            'product' => [
                'id' => $product->id,
                'name' => $product->name,
                'slug' => $product->slug,
                'sku' => $product->sku,
                'thumbnail' => $product->thumbnail ?: $this->getPrimaryImage($product),
                'images' => $product->images->map(fn ($image) => [
                    'id' => $image->id,
                    'image' => $image->image,
                    'alt_text' => $image->alt_text,
                    'is_primary' => (bool) $image->is_primary,
                    'sort_order' => $image->sort_order,
                ])->values()->all(),
                'short_description' => $product->short_description,
                'description' => $product->description,
                'price' => $pricing['original_price'],
                'final_price' => $pricing['final_price'],
                'discount_amount' => $pricing['discount_amount'],
                'stock_qty' => $product->stock_qty,
                'is_featured' => (bool) $product->is_featured,
                'is_new' => (bool) $product->is_new,
                'view_count' => $product->view_count,
                'brand' => $product->brand ? [
                    'id' => $product->brand->id,
                    'name' => $product->brand->name,
                    'slug' => $product->brand->slug,
                ] : null,
                'category' => $product->category ? [
                    'id' => $product->category->id,
                    'name' => $product->category->name,
                    'slug' => $product->category->slug,
                ] : null,
                'promotion' => $promotion ? [
                    'id' => $promotion->id,
                    'name' => $promotion->name,
                    'code' => $promotion->code,
                    'type' => $promotion->type,
                    'value' => (float) $promotion->value,
                    'start_at' => $promotion->start_at,
                    'end_at' => $promotion->end_at,
                ] : null,
                'reviews' => $product->reviews->map(fn ($review) => [
                    'id' => $review->id,
                    'rating' => $review->rating,
                    'title' => $review->title,
                    'content' => $review->content,
                    'approved_at' => $review->approved_at,
                    'user' => $review->user ? [
                        'id' => $review->user->id,
                        'name' => $review->user->name,
                        'avatar' => $review->user->avatar,
                    ] : null,
                ])->values()->all(),
                'review_summary' => [
                    'count' => $product->reviews->count(),
                    'average' => round((float) $product->reviews->avg('rating'), 1),
                ],
                'created_at' => $product->created_at,
                'updated_at' => $product->updated_at,
            ],
            'related_products' => $this->formatProductCollection($relatedProducts),
        ];
    }

    protected function formatProductCollection(Collection $products): array
    {
        return $products->map(function (Product $product) {
            $promotion = $this->promotionService->resolveActivePromotionForProduct($product);
            $pricing = $this->promotionService->calculatePricing((float) $product->price, $promotion);

            return [
                'id' => $product->id,
                'name' => $product->name,
                'slug' => $product->slug,
                'sku' => $product->sku,
                'thumbnail' => $product->thumbnail ?: $this->getPrimaryImage($product),
                'short_description' => $product->short_description,
                'price' => $pricing['original_price'],
                'final_price' => $pricing['final_price'],
                'discount_amount' => $pricing['discount_amount'],
                'stock_qty' => $product->stock_qty,
                'is_featured' => (bool) $product->is_featured,
                'is_new' => (bool) $product->is_new,
                'view_count' => $product->view_count,
                'brand' => $product->brand ? [
                    'id' => $product->brand->id,
                    'name' => $product->brand->name,
                    'slug' => $product->brand->slug,
                ] : null,
                'category' => $product->category ? [
                    'id' => $product->category->id,
                    'name' => $product->category->name,
                    'slug' => $product->category->slug,
                ] : null,
                'promotion' => $promotion ? [
                    'id' => $promotion->id,
                    'name' => $promotion->name,
                    'code' => $promotion->code,
                    'type' => $promotion->type,
                    'value' => (float) $promotion->value,
                ] : null,
                'created_at' => $product->created_at,
                'updated_at' => $product->updated_at,
            ];
        })->values()->all();
    }

    protected function getPrimaryImage(Product $product): ?string
    {
        if (! $product->relationLoaded('images')) {
            return null;
        }

        $primary = $product->images
            ->sortBy([
                ['is_primary', 'desc'],
                ['sort_order', 'asc'],
                ['id', 'asc'],
            ])
            ->first();

        return $primary?->image;
    }

    protected function formatPagination(LengthAwarePaginator $paginator): array
    {
        return [
            'current_page' => $paginator->currentPage(),
            'per_page' => $paginator->perPage(),
            'total' => $paginator->total(),
            'last_page' => $paginator->lastPage(),
            'from' => $paginator->firstItem(),
            'to' => $paginator->lastItem(),
        ];
    }
}