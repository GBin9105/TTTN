<?php

namespace App\Services;

use App\Models\Banner;
use App\Models\Category;
use App\Models\Post;
use App\Models\Product;
use App\Models\Setting;
use Illuminate\Database\Eloquent\Collection;

class HomeService
{
    public function __construct(
        protected PromotionService $promotionService
    ) {}

    protected int $bannerLimit = 10;
    protected int $promotionProductLimit = 8;
    protected int $newProductLimit = 8;
    protected int $featuredProductLimit = 8;
    protected int $categoryLimit = 4;
    protected int $categoryProductLimit = 8;
    protected int $latestPostLimit = 6;

    public function index(): array
    {
        return [
            'settings' => $this->getPublicSettings(),
            'banners' => $this->getHomeBanners(),
            'promotion_products' => $this->getPromotionProducts(),
            'new_products' => $this->getNewProducts(),
            'featured_products' => $this->getFeaturedProducts(),
            'products_by_categories' => $this->getProductsByCategories(),
            'latest_posts' => $this->getLatestPosts(),
        ];
    }

    protected function getPublicSettings(): array
    {
        $settings = Setting::query()
            ->where('is_public', true)
            ->orderBy('group')
            ->orderBy('id')
            ->get();

        $grouped = [];

        foreach ($settings as $setting) {
            $grouped[$setting->group][$setting->key] = $this->castSettingValue(
                $setting->value,
                $setting->type
            );
        }

        return $grouped;
    }

    protected function getHomeBanners(): array
    {
        $now = now();

        $banners = Banner::query()
            ->where('status', true)
            ->where('position', 'home')
            ->where(function ($query) use ($now) {
                $query->whereNull('start_at')->orWhere('start_at', '<=', $now);
            })
            ->where(function ($query) use ($now) {
                $query->whereNull('end_at')->orWhere('end_at', '>=', $now);
            })
            ->orderBy('sort_order')
            ->orderByDesc('id')
            ->limit($this->bannerLimit)
            ->get();

        return $banners->map(fn ($banner) => [
            'id' => $banner->id,
            'title' => $banner->title,
            'image' => $banner->image,
            'link_url' => $banner->link_url,
            'position' => $banner->position,
            'sort_order' => $banner->sort_order,
            'start_at' => $banner->start_at,
            'end_at' => $banner->end_at,
            'created_at' => $banner->created_at,
            'updated_at' => $banner->updated_at,
        ])->values()->all();
    }

    protected function getPromotionProducts(): array
    {
        $products = Product::query()
            ->with([
                'brand:id,name,slug',
                'category:id,name,slug',
                'images:id,product_id,image,alt_text,is_primary,sort_order',
                'promotions',
            ])
            ->where('status', true)
            ->whereHas('promotions', function ($query) {
                $query->where('status', true)
                    ->where(function ($subQuery) {
                        $subQuery->whereNull('start_at')
                            ->orWhere('start_at', '<=', now());
                    })
                    ->where(function ($subQuery) {
                        $subQuery->whereNull('end_at')
                            ->orWhere('end_at', '>=', now());
                    });
            })
            ->orderByDesc('is_featured')
            ->orderByDesc('is_new')
            ->orderBy('sort_order')
            ->orderByDesc('id')
            ->limit($this->promotionProductLimit)
            ->get();

        return $this->formatProductCollection($products);
    }

    protected function getNewProducts(): array
    {
        $products = Product::query()
            ->with(['brand:id,name,slug', 'category:id,name,slug', 'images:id,product_id,image,alt_text,is_primary,sort_order', 'promotions'])
            ->where('status', true)
            ->where('is_new', true)
            ->orderByDesc('id')
            ->limit($this->newProductLimit)
            ->get();

        return $this->formatProductCollection($products);
    }

    protected function getFeaturedProducts(): array
    {
        $products = Product::query()
            ->with(['brand:id,name,slug', 'category:id,name,slug', 'images:id,product_id,image,alt_text,is_primary,sort_order', 'promotions'])
            ->where('status', true)
            ->where('is_featured', true)
            ->orderBy('sort_order')
            ->orderByDesc('id')
            ->limit($this->featuredProductLimit)
            ->get();

        return $this->formatProductCollection($products);
    }

    protected function getProductsByCategories(): array
    {
        $categories = Category::query()
            ->where('status', true)
            ->whereNotNull('parent_id')
            ->whereHas('products', fn ($query) => $query->where('status', true))
            ->withCount([
                'products' => fn ($query) => $query->where('status', true),
            ])
            ->orderBy('sort_order')
            ->orderBy('id')
            ->limit($this->categoryLimit)
            ->get();

        return $categories->map(function (Category $category) {
            $products = Product::query()
                ->with(['brand:id,name,slug', 'category:id,name,slug', 'images:id,product_id,image,alt_text,is_primary,sort_order', 'promotions'])
                ->where('status', true)
                ->where('category_id', $category->id)
                ->orderByDesc('is_featured')
                ->orderByDesc('is_new')
                ->orderBy('sort_order')
                ->orderByDesc('id')
                ->limit($this->categoryProductLimit)
                ->get();

            return [
                'id' => $category->id,
                'name' => $category->name,
                'slug' => $category->slug,
                'image' => $category->image,
                'description' => $category->description,
                'product_count' => $category->products_count,
                'products' => $this->formatProductCollection($products),
            ];
        })->values()->all();
    }

    protected function getLatestPosts(): array
    {
        $posts = Post::query()
            ->with(['topic:id,name,slug', 'author:id,name'])
            ->where('status', 'published')
            ->where(function ($query) {
                $query->whereNull('published_at')
                    ->orWhere('published_at', '<=', now());
            })
            ->orderByDesc('published_at')
            ->orderByDesc('id')
            ->limit($this->latestPostLimit)
            ->get();

        return $posts->map(fn ($post) => [
            'id' => $post->id,
            'title' => $post->title,
            'slug' => $post->slug,
            'thumbnail' => $post->thumbnail,
            'excerpt' => $post->excerpt,
            'is_featured' => (bool) $post->is_featured,
            'status' => $post->status,
            'published_at' => $post->published_at,
            'topic' => $post->topic ? [
                'id' => $post->topic->id,
                'name' => $post->topic->name,
                'slug' => $post->topic->slug,
            ] : null,
            'author' => $post->author ? [
                'id' => $post->author->id,
                'name' => $post->author->name,
            ] : null,
            'created_at' => $post->created_at,
            'updated_at' => $post->updated_at,
        ])->values()->all();
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
                'status' => (bool) $product->status,
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

    protected function castSettingValue(?string $value, string $type): mixed
    {
        if ($value === null) {
            return null;
        }

        return match ($type) {
            'boolean' => filter_var($value, FILTER_VALIDATE_BOOLEAN),
            'json' => json_decode($value, true) ?? $value,
            default => $value,
        };
    }
}