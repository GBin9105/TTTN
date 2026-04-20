<?php

namespace Database\Seeders;

use App\Models\Banner;
use Illuminate\Database\Seeder;

class BannerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $banners = [
            [
                'title' => 'Khuyến mãi dụng cụ điện tháng này',
                'image' => '/storage/banners/banner-1.jpg',
                'link_url' => '/products',
                'position' => 'home',
                'sort_order' => 1,
                'status' => true,
                'start_at' => now()->subDays(1),
                'end_at' => now()->addDays(30),
            ],
            [
                'title' => 'Máy khoan chính hãng giá tốt',
                'image' => '/storage/banners/banner-2.jpg',
                'link_url' => '/products?category=may-khoan',
                'position' => 'home',
                'sort_order' => 2,
                'status' => true,
                'start_at' => now()->subDays(1),
                'end_at' => now()->addDays(30),
            ],
            [
                'title' => 'Bài viết hướng dẫn sử dụng máy mài an toàn',
                'image' => '/storage/banners/banner-3.jpg',
                'link_url' => '/posts',
                'position' => 'home',
                'sort_order' => 3,
                'status' => true,
                'start_at' => now()->subDays(1),
                'end_at' => now()->addDays(30),
            ],
        ];

        foreach ($banners as $banner) {
            Banner::updateOrCreate(
                ['title' => $banner['title']],
                $banner
            );
        }
    }
}