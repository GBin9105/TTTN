<?php

namespace Database\Seeders;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $products = [
            [
                'brand_slug' => 'bosch',
                'category_slug' => 'may-khoan',
                'name' => 'Máy khoan bê tông Bosch GBH 2-26 DRE',
                'slug' => 'may-khoan-be-tong-bosch-gbh-2-26-dre',
                'sku' => 'BOSCH-GBH2-26DRE',
                'thumbnail' => '/storage/products/bosch-gbh-2-26-dre.jpg',
                'short_description' => 'Máy khoan bê tông mạnh mẽ, phù hợp công trình dân dụng và chuyên nghiệp.',
                'description' => 'Bosch GBH 2-26 DRE là dòng máy khoan bê tông nổi bật với công suất ổn định, độ bền cao và thao tác chắc chắn.',
                'price' => 3250000,
                'stock_qty' => 15,
                'is_featured' => true,
                'is_new' => true,
                'view_count' => 120,
                'status' => true,
                'sort_order' => 1,
                'images' => [
                    '/storage/products/bosch-gbh-2-26-dre.jpg',
                    '/storage/products/bosch-gbh-2-26-dre-2.jpg',
                ],
            ],
            [
                'brand_slug' => 'makita',
                'category_slug' => 'may-khoan',
                'name' => 'Máy khoan pin Makita HP333DSYE',
                'slug' => 'may-khoan-pin-makita-hp333dsye',
                'sku' => 'MAKITA-HP333DSYE',
                'thumbnail' => '/storage/products/makita-hp333dsye.jpg',
                'short_description' => 'Máy khoan pin nhỏ gọn, linh hoạt, dùng cho gia đình và thợ kỹ thuật.',
                'description' => 'Makita HP333DSYE có thiết kế gọn nhẹ, sử dụng pin tiện lợi và đáp ứng tốt nhu cầu khoan bắt vít cơ bản.',
                'price' => 2850000,
                'stock_qty' => 20,
                'is_featured' => true,
                'is_new' => true,
                'view_count' => 95,
                'status' => true,
                'sort_order' => 2,
                'images' => [
                    '/storage/products/makita-hp333dsye.jpg',
                    '/storage/products/makita-hp333dsye-2.jpg',
                ],
            ],
            [
                'brand_slug' => 'dewalt',
                'category_slug' => 'may-bat-vit',
                'name' => 'Máy bắt vít dùng pin DeWalt DCF787D2',
                'slug' => 'may-bat-vit-dung-pin-dewalt-dcf787d2',
                'sku' => 'DEWALT-DCF787D2',
                'thumbnail' => '/storage/products/dewalt-dcf787d2.jpg',
                'short_description' => 'Máy bắt vít pin lực siết tốt, phù hợp công việc lắp đặt chuyên dụng.',
                'description' => 'DeWalt DCF787D2 được đánh giá cao bởi độ bền, lực siết mạnh và hiệu suất ổn định.',
                'price' => 3990000,
                'stock_qty' => 10,
                'is_featured' => true,
                'is_new' => false,
                'view_count' => 80,
                'status' => true,
                'sort_order' => 3,
                'images' => [
                    '/storage/products/dewalt-dcf787d2.jpg',
                ],
            ],
            [
                'brand_slug' => 'stanley',
                'category_slug' => 'may-mai',
                'name' => 'Máy mài góc Stanley STGS8100',
                'slug' => 'may-mai-goc-stanley-stgs8100',
                'sku' => 'STANLEY-STGS8100',
                'thumbnail' => '/storage/products/stanley-stgs8100.jpg',
                'short_description' => 'Máy mài góc công suất tốt, phù hợp cắt và mài vật liệu phổ biến.',
                'description' => 'Stanley STGS8100 là lựa chọn phù hợp cho thợ cơ khí, sửa chữa và thi công dân dụng.',
                'price' => 990000,
                'stock_qty' => 25,
                'is_featured' => false,
                'is_new' => true,
                'view_count' => 60,
                'status' => true,
                'sort_order' => 4,
                'images' => [
                    '/storage/products/stanley-stgs8100.jpg',
                ],
            ],
            [
                'brand_slug' => 'total',
                'category_slug' => 'may-cat',
                'name' => 'Máy cắt sắt Total TS92035526',
                'slug' => 'may-cat-sat-total-ts92035526',
                'sku' => 'TOTAL-TS92035526',
                'thumbnail' => '/storage/products/total-ts92035526.jpg',
                'short_description' => 'Máy cắt sắt mạnh mẽ, giá hợp lý cho nhu cầu xưởng nhỏ.',
                'description' => 'Total TS92035526 đáp ứng nhu cầu cắt sắt, thép cơ bản với hiệu suất tốt trong tầm giá.',
                'price' => 2450000,
                'stock_qty' => 8,
                'is_featured' => false,
                'is_new' => false,
                'view_count' => 40,
                'status' => true,
                'sort_order' => 5,
                'images' => [
                    '/storage/products/total-ts92035526.jpg',
                ],
            ],
            [
                'brand_slug' => 'bosch',
                'category_slug' => 'phu-kien',
                'name' => 'Bộ mũi khoan đa năng Bosch X-Line 30 món',
                'slug' => 'bo-mui-khoan-da-nang-bosch-x-line-30-mon',
                'sku' => 'BOSCH-XLINE-30',
                'thumbnail' => '/storage/products/bosch-xline-30.jpg',
                'short_description' => 'Bộ phụ kiện khoan đa năng cho nhiều vật liệu khác nhau.',
                'description' => 'Bosch X-Line 30 món bao gồm nhiều đầu khoan và đầu vít tiện dụng cho gia đình và thợ kỹ thuật.',
                'price' => 450000,
                'stock_qty' => 30,
                'is_featured' => true,
                'is_new' => false,
                'view_count' => 70,
                'status' => true,
                'sort_order' => 6,
                'images' => [
                    '/storage/products/bosch-xline-30.jpg',
                ],
            ],
        ];

        foreach ($products as $item) {
            $brand = Brand::where('slug', $item['brand_slug'])->first();
            $category = Category::where('slug', $item['category_slug'])->first();

            $product = Product::updateOrCreate(
                ['slug' => $item['slug']],
                [
                    'brand_id' => $brand?->id,
                    'category_id' => $category?->id,
                    'name' => $item['name'],
                    'sku' => $item['sku'],
                    'thumbnail' => $item['thumbnail'],
                    'short_description' => $item['short_description'],
                    'description' => $item['description'],
                    'price' => $item['price'],
                    'stock_qty' => $item['stock_qty'],
                    'is_featured' => $item['is_featured'],
                    'is_new' => $item['is_new'],
                    'view_count' => $item['view_count'],
                    'status' => $item['status'],
                    'sort_order' => $item['sort_order'],
                ]
            );

            foreach ($item['images'] as $index => $image) {
                ProductImage::updateOrCreate(
                    [
                        'product_id' => $product->id,
                        'image' => $image,
                    ],
                    [
                        'alt_text' => $product->name,
                        'is_primary' => $index === 0,
                        'sort_order' => $index + 1,
                    ]
                );
            }
        }
    }
}