<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $parent = Category::updateOrCreate(
            ['slug' => 'dung-cu-dien-cam-tay'],
            [
                'parent_id' => null,
                'name' => 'Dụng cụ điện cầm tay',
                'image' => '/storage/categories/dung-cu-dien-cam-tay.jpg',
                'description' => 'Danh mục tổng hợp các loại máy và dụng cụ điện cầm tay.',
                'status' => true,
                'sort_order' => 1,
            ]
        );

        $categories = [
            [
                'parent_id' => $parent->id,
                'name' => 'Máy khoan',
                'slug' => 'may-khoan',
                'image' => '/storage/categories/may-khoan.jpg',
                'description' => 'Máy khoan cầm tay, khoan bê tông, khoan pin.',
                'status' => true,
                'sort_order' => 1,
            ],
            [
                'parent_id' => $parent->id,
                'name' => 'Máy cắt',
                'slug' => 'may-cat',
                'image' => '/storage/categories/may-cat.jpg',
                'description' => 'Máy cắt sắt, máy cắt gỗ, máy cắt cầm tay.',
                'status' => true,
                'sort_order' => 2,
            ],
            [
                'parent_id' => $parent->id,
                'name' => 'Máy mài',
                'slug' => 'may-mai',
                'image' => '/storage/categories/may-mai.jpg',
                'description' => 'Máy mài góc, máy mài cầm tay phục vụ thi công.',
                'status' => true,
                'sort_order' => 3,
            ],
            [
                'parent_id' => $parent->id,
                'name' => 'Máy bắt vít',
                'slug' => 'may-bat-vit',
                'image' => '/storage/categories/may-bat-vit.jpg',
                'description' => 'Máy bắt vít dùng pin và máy bắt vít chuyên dụng.',
                'status' => true,
                'sort_order' => 4,
            ],
            [
                'parent_id' => $parent->id,
                'name' => 'Phụ kiện',
                'slug' => 'phu-kien',
                'image' => '/storage/categories/phu-kien.jpg',
                'description' => 'Mũi khoan, đá cắt, đá mài, đầu vít và phụ kiện khác.',
                'status' => true,
                'sort_order' => 5,
            ],
        ];

        foreach ($categories as $category) {
            Category::updateOrCreate(
                ['slug' => $category['slug']],
                $category
            );
        }
    }
}