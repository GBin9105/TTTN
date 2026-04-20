<?php

namespace Database\Seeders;

use App\Models\Page;
use App\Models\User;
use Illuminate\Database\Seeder;

class PageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $admin = User::where('role', 'admin')->first();

        $pages = [
            [
                'title' => 'Giới thiệu',
                'slug' => 'gioi-thieu',
                'thumbnail' => null,
                'excerpt' => 'Thông tin giới thiệu về cửa hàng Power Tools Store.',
                'content' => '<h2>Giới thiệu</h2><p>Power Tools Store chuyên cung cấp dụng cụ điện cầm tay chính hãng, hỗ trợ khách hàng cá nhân và nhà thầu chuyên nghiệp.</p>',
                'meta_title' => 'Giới thiệu - Power Tools Store',
                'meta_description' => 'Giới thiệu về Power Tools Store và định hướng phát triển.',
                'is_home' => false,
                'status' => true,
                'sort_order' => 1,
                'created_by' => $admin?->id,
                'updated_by' => $admin?->id,
                'published_at' => now(),
            ],
            [
                'title' => 'Chính sách giao hàng',
                'slug' => 'chinh-sach-giao-hang',
                'thumbnail' => null,
                'excerpt' => 'Thông tin về thời gian và phạm vi giao hàng.',
                'content' => '<h2>Chính sách giao hàng</h2><p>Chúng tôi giao hàng toàn quốc, kiểm tra đơn trong giờ hành chính và bàn giao đơn vị vận chuyển trong 24h.</p>',
                'meta_title' => 'Chính sách giao hàng',
                'meta_description' => 'Chính sách giao hàng của Power Tools Store.',
                'is_home' => false,
                'status' => true,
                'sort_order' => 2,
                'created_by' => $admin?->id,
                'updated_by' => $admin?->id,
                'published_at' => now(),
            ],
            [
                'title' => 'Chính sách đổi trả',
                'slug' => 'chinh-sach-doi-tra',
                'thumbnail' => null,
                'excerpt' => 'Thông tin đổi trả sản phẩm khi có lỗi hoặc sai đơn.',
                'content' => '<h2>Chính sách đổi trả</h2><p>Sản phẩm lỗi kỹ thuật do nhà sản xuất hoặc giao sai sẽ được hỗ trợ đổi trả theo quy định.</p>',
                'meta_title' => 'Chính sách đổi trả',
                'meta_description' => 'Chính sách đổi trả sản phẩm của Power Tools Store.',
                'is_home' => false,
                'status' => true,
                'sort_order' => 3,
                'created_by' => $admin?->id,
                'updated_by' => $admin?->id,
                'published_at' => now(),
            ],
            [
                'title' => 'Điều khoản sử dụng',
                'slug' => 'dieu-khoan-su-dung',
                'thumbnail' => null,
                'excerpt' => 'Điều khoản và điều kiện khi sử dụng website.',
                'content' => '<h2>Điều khoản sử dụng</h2><p>Khách hàng vui lòng đọc kỹ điều khoản trước khi sử dụng website và đặt hàng.</p>',
                'meta_title' => 'Điều khoản sử dụng',
                'meta_description' => 'Điều khoản sử dụng website Power Tools Store.',
                'is_home' => false,
                'status' => true,
                'sort_order' => 4,
                'created_by' => $admin?->id,
                'updated_by' => $admin?->id,
                'published_at' => now(),
            ],
        ];

        foreach ($pages as $page) {
            Page::updateOrCreate(
                ['slug' => $page['slug']],
                $page
            );
        }
    }
}