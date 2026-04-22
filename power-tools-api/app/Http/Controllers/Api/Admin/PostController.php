<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Api\Concerns\ApiResponse;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PostController extends Controller
{
    use ApiResponse;

    public function index(Request $request)
    {
        $query = Post::query();

        if ($request->filled('q')) {
            $q = trim($request->q);
            $query->where(function ($sub) use ($q) {
                $sub->where('title', 'like', "%{$q}%")
                    ->orWhere('slug', 'like', "%{$q}%");
            });
        }

        if ($request->filled('post_topic_id')) {
            $query->where('post_topic_id', $request->post_topic_id);
        }

        if ($request->filled('status')) {
            $query->where('status', filter_var($request->status, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? $request->status);
        }

        $posts = $query->latest('id')->paginate((int) $request->get('per_page', 10));

        return $this->success($posts, 'Posts fetched successfully.');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'post_topic_id' => ['nullable', 'exists:post_topics,id'],
            'title'         => ['required', 'string', 'max:255'],
            'slug'          => ['nullable', 'string', 'max:255', 'unique:posts,slug'],
            'summary'       => ['nullable', 'string'],
            'content'       => ['nullable', 'string'],
            'image'         => ['nullable', 'string', 'max:255'],
            'status'        => ['nullable', 'boolean'],
            'published_at'  => ['nullable', 'date'],
        ]);

        $data['slug'] = $data['slug'] ?? Str::slug($data['title']);
        $data['status'] = $data['status'] ?? true;

        $post = Post::create($data);

        return $this->success($post, 'Post created successfully.', 201);
    }

    public function show(Post $post)
    {
        return $this->success($post, 'Post detail fetched successfully.');
    }

    public function update(Request $request, Post $post)
    {
        $data = $request->validate([
            'post_topic_id' => ['nullable', 'exists:post_topics,id'],
            'title'         => ['sometimes', 'required', 'string', 'max:255'],
            'slug'          => ['nullable', 'string', 'max:255', 'unique:posts,slug,' . $post->id],
            'summary'       => ['nullable', 'string'],
            'content'       => ['nullable', 'string'],
            'image'         => ['nullable', 'string', 'max:255'],
            'status'        => ['nullable', 'boolean'],
            'published_at'  => ['nullable', 'date'],
        ]);

        if (isset($data['title']) && empty($data['slug'])) {
            $data['slug'] = Str::slug($data['title']);
        }

        $post->update($data);

        return $this->success($post->fresh(), 'Post updated successfully.');
    }

    public function destroy(Post $post)
    {
        $post->delete();

        return $this->success(null, 'Post deleted successfully.');
    }
}