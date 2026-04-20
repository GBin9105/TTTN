<?php

namespace Database\Seeders;

use App\Models\Brand;
use Illuminate\Database\Seeder;

class BrandSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $brands = [
            [
                'name' => 'Bosch',
                'slug' => 'bosch',
                'logo' => '/storage/brands/bosch.png',
                'description' => 'Thương hiệu dụng cụ điện cầm tay nổi tiếng từ Đức.',
                'status' => true,
                'sort_order' => 1,
            ],
            [
                'name' => 'Makita',
                'slug' => 'makita',
                'logo' => '/storage/brands/makita.png',
                'description' => 'Thương hiệu Nhật Bản nổi tiếng về máy khoan, máy cắt, máy mài.',
                'status' => true,
                'sort_order' => 2,
            ],
            [
                'name' => 'DeWalt',
                'slug' => 'dewalt',
                'logo' => '/storage/brands/dewalt.png',
                'description' => 'Thương hiệu chuyên dụng cho công trình và thợ chuyên nghiệp.',
                'status' => true,
                'sort_order' => 3,
            ],
            [
                'name' => 'Stanley',
                'slug' => 'stanley',
                'logo' => '/storage/brands/stanley.png',
                'description' => 'Thương hiệu dụng cụ chất lượng cao được tin dùng.',
                'status' => true,
                'sort_order' => 4,
            ],
            [
                'name' => 'Total',
                'slug' => 'total',
                'logo' => '/storage/brands/total.png',
                'description' => 'Thương hiệu phổ thông với mức giá hợp lý.',
                'status' => true,
                'sort_order' => 5,
            ],
        ];

        foreach ($brands as $brand) {
            Brand::updateOrCreate(
                ['slug' => $brand['slug']],
                $brand
            );
        }
    }
}