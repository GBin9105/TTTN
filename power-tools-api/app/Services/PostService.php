<?php

namespace App\Services;

use App\Models\Post;
use App\Models\PostTopic;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class PostService
{
    public function index(array $filters = []): array
    {
        $perPage = (int) ($filters['per_page'] ?? 10);
        $sort = $filters['sort'] ?? 'latest';

        $query = Post::query()
            ->with(['topic:id,name,slug', 'author:id,name'])
            ->where('status', 'published')
            ->where(function ($subQuery) {
                $subQuery->whereNull('published_at')
                    ->orWhere('published_at', '<=', now());
            });

        if (! empty($filters['q'])) {
            $q = trim($filters['q']);
            $query->where(function ($subQuery) use ($q) {
                $subQuery->where('title', 'like', "%{$q}%")
                    ->orWhere('excerpt', 'like', "%{$q}%")
                    ->orWhere('content', 'like', "%{$q}%");
            });
        }

        if (! empty($filters['topic_slug'])) {
            $topic = PostTopic::where('slug', $filters['topic_slug'])->first();
            if ($topic) {
                $query->where('post_topic_id', $topic->id);
            }
        } elseif (! empty($filters['post_topic_id'])) {
            $query->where('post_topic_id', $filters['post_topic_id']);
        }

        switch ($sort) {
            case 'oldest':
                $query->orderBy('published_at')->orderBy('id');
                break;
            case 'title_asc':
                $query->orderBy('title');
                break;
            case 'title_desc':
                $query->orderByDesc('title');
                break;
            default:
                $query->orderByDesc('published_at')->orderByDesc('id');
                break;
        }

        $paginator = $query->paginate($perPage);

        return [
            'items' => $this->formatPostCollection(collect($paginator->items())),
            'pagination' => $this->formatPagination($paginator),
            'filters' => [
                'q' => $filters['q'] ?? null,
                'topic_slug' => $filters['topic_slug'] ?? null,
                'sort' => $sort,
            ],
        ];
    }

    public function show(string $slug): ?array
    {
        $post = Post::query()
            ->with(['topic:id,name,slug', 'author:id,name'])
            ->where('status', 'published')
            ->where(function ($subQuery) {
                $subQuery->whereNull('published_at')
                    ->orWhere('published_at', '<=', now());
            })
            ->where('slug', $slug)
            ->first();

        if (! $post) {
            return null;
        }

        $relatedPosts = Post::query()
            ->with(['topic:id,name,slug', 'author:id,name'])
            ->where('status', 'published')
            ->where('id', '!=', $post->id)
            ->where(function ($subQuery) {
                $subQuery->whereNull('published_at')
                    ->orWhere('published_at', '<=', now());
            })
            ->inRandomOrder()
            ->limit(4)
            ->get();

        return [
            'post' => [
                'id' => $post->id,
                'title' => $post->title,
                'slug' => $post->slug,
                'thumbnail' => $post->thumbnail,
                'excerpt' => $post->excerpt,
                'content' => $post->content,
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
            ],
            'related_posts' => $this->formatPostCollection($relatedPosts),
        ];
    }

    protected function formatPostCollection(Collection $posts): array
    {
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