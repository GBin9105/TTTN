<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Api\Concerns\ApiResponse;
use App\Models\PostTopic;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PostTopicController extends Controller
{
    use ApiResponse;

    public function index(Request $request)
    {
        $query = PostTopic::query();

        if ($request->filled('q')) {
            $q = trim($request->q);
            $query->where(function ($sub) use ($q) {
                $sub->where('name', 'like', "%{$q}%")
                    ->orWhere('slug', 'like', "%{$q}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', filter_var($request->status, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? $request->status);
        }

        $topics = $query->latest('id')->paginate((int) $request->get('per_page', 10));

        return $this->success($topics, 'Post topics fetched successfully.');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'        => ['required', 'string', 'max:255'],
            'slug'        => ['nullable', 'string', 'max:255', 'unique:post_topics,slug'],
            'description' => ['nullable', 'string'],
            'status'      => ['nullable', 'boolean'],
        ]);

        $data['slug'] = $data['slug'] ?? Str::slug($data['name']);
        $data['status'] = $data['status'] ?? true;

        $topic = PostTopic::create($data);

        return $this->success($topic, 'Post topic created successfully.', 201);
    }

    public function show(PostTopic $postTopic)
    {
        return $this->success($postTopic, 'Post topic detail fetched successfully.');
    }

    public function update(Request $request, PostTopic $postTopic)
    {
        $data = $request->validate([
            'name'        => ['sometimes', 'required', 'string', 'max:255'],
            'slug'        => ['nullable', 'string', 'max:255', 'unique:post_topics,slug,' . $postTopic->id],
            'description' => ['nullable', 'string'],
            'status'      => ['nullable', 'boolean'],
        ]);

        if (isset($data['name']) && empty($data['slug'])) {
            $data['slug'] = Str::slug($data['name']);
        }

        $postTopic->update($data);

        return $this->success($postTopic->fresh(), 'Post topic updated successfully.');
    }

    public function destroy(PostTopic $postTopic)
    {
        $postTopic->delete();

        return $this->success(null, 'Post topic deleted successfully.');
    }
}