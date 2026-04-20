<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\Promotion;
use Illuminate\Database\Seeder;

class PromotionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $promotions = [
            [
                'name' => 'Giảm 10% máy khoan nổi bật',
                'code' => 'KHOAN10',
                'description' => 'Áp dụng cho một số dòng máy khoan nổi bật.',
                'type' => 'percent',
                'value' => 10,
                'priority' => 1,
                'status' => true,
                'start_at' => now()->subDays(3),
                'end_at' => now()->addDays(15),
                'product_slugs' => [
                    'may-khoan-be-tong-bosch-gbh-2-26-dre',
                    'may-khoan-pin-makita-hp333dsye',
                ],
            ],
            [
                'name' => 'Giảm 150.000 cho phụ kiện',
                'code' => 'PK150',
                'description' => 'Giảm giá trực tiếp cho bộ phụ kiện.',
                'type' => 'fixed_amount',
                'value' => 150000,
                'priority' => 2,
                'status' => true,
                'start_at' => now()->subDays(1),
                'end_at' => now()->addDays(10),
                'product_slugs' => [
                    'bo-mui-khoan-da-nang-bosch-x-line-30-mon',
                ],
            ],
            [
                'name' => 'Đồng giá máy mài',
                'code' => 'MAI899',
                'description' => 'Áp dụng đồng giá cho sản phẩm máy mài được chọn.',
                'type' => 'fixed_price',
                'value' => 899000,
                'priority' => 3,
                'status' => true,
                'start_at' => now()->subDays(2),
                'end_at' => now()->addDays(7),
                'product_slugs' => [
                    'may-mai-goc-stanley-stgs8100',
                ],
            ],
        ];

        foreach ($promotions as $item) {
            $promotion = Promotion::updateOrCreate(
                ['code' => $item['code']],
                [
                    'name' => $item['name'],
                    'description' => $item['description'],
                    'type' => $item['type'],
                    'value' => $item['value'],
                    'priority' => $item['priority'],
                    'status' => $item['status'],
                    'start_at' => $item['start_at'],
                    'end_at' => $item['end_at'],
                ]
            );

            $productIds = Product::whereIn('slug', $item['product_slugs'])->pluck('id')->toArray();

            $promotion->products()->syncWithoutDetaching($productIds);
        }
    }
}