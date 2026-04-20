<?php

namespace App\Services;

use App\Models\Category;
use App\Models\Page;
use App\Models\Post;
use App\Models\Product;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class SearchService
{
    public function __construct(
        protected PromotionService $promotionService
    ) {}

    public function index(array $filters = []): array
    {
        $q = trim((string) ($filters['q'] ?? ''));
        $perPage = (int) ($filters['per_page'] ?? 10);
        $page = (int) ($filters['page'] ?? 1);

        if ($q === '') {
            return [
                'items' => [],
                'pagination' => [
                    'current_page' => 1,
                    'per_page' => $perPage,
                    'total' => 0,
                    'last_page' => 1,
                    'from' => null,
                    'to' => null,
                ],
                'keyword' => '',
            ];
        }

        $results = collect()
            ->merge($this->searchProducts($q))
            ->merge($this->searchCategories($q))
            ->merge($this->searchPosts($q))
            ->merge($this->searchPages($q))
            ->sortByDesc('score')
            ->values();

        $total = $results->count();
        $items = $results->forPage($page, $perPage)->values();

        return [
            'items' => $items->all(),
            'pagination' => [
                'current_page' => $page,
                'per_page' => $perPage,
                'total' => $total,
                'last_page' => max(1, (int) ceil($total / $perPage)),
                'from' => $total ? (($page - 1) * $perPage) + 1 : null,
                'to' => $total ? min($page * $perPage, $total) : null,
            ],
            'keyword' => $q,
        ];
    }

    protected function searchProducts(string $q): Collection
    {
        $products = Product::query()
            ->with(['brand:id,name,slug', 'category:id,name,slug', 'images:id,product_id,image,alt_text,is_primary,sort_order', 'promotions'])
            ->where('status', true)
            ->where(function ($query) use ($q) {
                $query->where('name', 'like', "%{$q}%")
                    ->orWhere('slug', 'like', "%{$q}%")
                    ->orWhere('sku', 'like', "%{$q}%")
                    ->orWhere('short_description', 'like', "%{$q}%");
            })
            ->limit(50)
            ->get();

        return $products->map(function (Product $product) {
            $promotion = $this->promotionService->resolveActivePromotionForProduct($product);
            $pricing = $this->promotionService->calculatePricing((float) $product->price, $promotion);

            return [
                'type' => 'product',
                'score' => 100,
                'id' => $product->id,
                'title' => $product->name,
                'slug' => $product->slug,
                'thumbnail' => $product->thumbnail,
                'excerpt' => $product->short_description,
                'url' => '/products/' . $product->slug,
                'meta' => [
                    'price' => $pricing['original_price'],
                    'final_price' => $pricing['final_price'],
                    'discount_amount' => $pricing['discount_amount'],
                    'brand' => $product->brand?->name,
                    'category' => $product->category?->name,
                ],
            ];
        });
    }

    protected function searchCategories(string $q): Collection
    {
        return Category::query()
            ->where('status', true)
            ->where(function ($query) use ($q) {
                $query->where('name', 'like', "%{$q}%")
                    ->orWhere('slug', 'like', "%{$q}%")
                    ->orWhere('description', 'like', "%{$q}%");
            })
            ->limit(30)
            ->get()
            ->map(fn ($category) => [
                'type' => 'category',
                'score' => 80,
                'id' => $category->id,
                'title' => $category->name,
                'slug' => $category->slug,
                'thumbnail' => $category->image,
                'excerpt' => $category->description,
                'url' => '/products?category=' . $category->slug,
                'meta' => [],
            ]);
    }

    protected function searchPosts(string $q): Collection
    {
        return Post::query()
            ->where('status', 'published')
            ->where(function ($dateQuery) {
                $dateQuery->whereNull('published_at')
                    ->orWhere('published_at', '<=', now());
            })
            ->where(function ($query) use ($q) {
                $query->where('title', 'like', "%{$q}%")
                    ->orWhere('slug', 'like', "%{$q}%")
                    ->orWhere('excerpt', 'like', "%{$q}%")
                    ->orWhere('content', 'like', "%{$q}%");
            })
            ->limit(30)
            ->get()
            ->map(fn ($post) => [
                'type' => 'post',
                'score' => 70,
                'id' => $post->id,
                'title' => $post->title,
                'slug' => $post->slug,
                'thumbnail' => $post->thumbnail,
                'excerpt' => $post->excerpt,
                'url' => '/posts/' . $post->slug,
                'meta' => [
                    'published_at' => $post->published_at,
                ],
            ]);
    }

    protected function searchPages(string $q): Collection
    {
        return Page::query()
            ->where('status', true)
            ->where(function ($dateQuery) {
                $dateQuery->whereNull('published_at')
                    ->orWhere('published_at', '<=', now());
            })
            ->where(function ($query) use ($q) {
                $query->where('title', 'like', "%{$q}%")
                    ->orWhere('slug', 'like', "%{$q}%")
                    ->orWhere('excerpt', 'like', "%{$q}%")
                    ->orWhere('content', 'like', "%{$q}%");
            })
            ->limit(20)
            ->get()
            ->map(fn ($page) => [
                'type' => 'page',
                'score' => 60,
                'id' => $page->id,
                'title' => $page->title,
                'slug' => $page->slug,
                'thumbnail' => $page->thumbnail,
                'excerpt' => $page->excerpt,
                'url' => '/pages/' . $page->slug,
                'meta' => [],
            ]);
    }
}