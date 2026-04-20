<?php

namespace App\Services;

use App\Models\Page;

class PageService
{
    /**
     * Lấy chi tiết trang đơn theo slug.
     */
    public function show(string $slug): ?array
    {
        $page = Page::query()
            ->with([
                'creator:id,name',
                'updater:id,name',
            ])
            ->where('status', true)
            ->where('slug', $slug)
            ->where(function ($query) {
                $query->whereNull('published_at')
                    ->orWhere('published_at', '<=', now());
            })
            ->first();

        if (! $page) {
            return null;
        }

        return [
            'page' => $this->formatPage($page),
        ];
    }

    /**
     * Lấy trang được đánh dấu là trang chủ (nếu có).
     * Có thể dùng sau này nếu bạn muốn dựng trang đơn kiểu homepage content.
     */
    public function getHomePage(): ?array
    {
        $page = Page::query()
            ->with([
                'creator:id,name',
                'updater:id,name',
            ])
            ->where('status', true)
            ->where('is_home', true)
            ->where(function ($query) {
                $query->whereNull('published_at')
                    ->orWhere('published_at', '<=', now());
            })
            ->orderBy('sort_order')
            ->orderByDesc('id')
            ->first();

        if (! $page) {
            return null;
        }

        return [
            'page' => $this->formatPage($page),
        ];
    }

    /**
     * Format dữ liệu page trả về cho client.
     */
    protected function formatPage(Page $page): array
    {
        return [
            'id' => $page->id,
            'title' => $page->title,
            'slug' => $page->slug,
            'thumbnail' => $page->thumbnail,
            'excerpt' => $page->excerpt,
            'content' => $page->content,
            'meta_title' => $page->meta_title,
            'meta_description' => $page->meta_description,
            'is_home' => (bool) $page->is_home,
            'status' => (bool) $page->status,
            'sort_order' => $page->sort_order,
            'published_at' => $page->published_at,
            'creator' => $page->creator ? [
                'id' => $page->creator->id,
                'name' => $page->creator->name,
            ] : null,
            'updater' => $page->updater ? [
                'id' => $page->updater->id,
                'name' => $page->updater->name,
            ] : null,
            'created_at' => $page->created_at,
            'updated_at' => $page->updated_at,
        ];
    }
}