<?php

namespace Database\Seeders;

use App\Models\PostTopic;
use Illuminate\Database\Seeder;

class PostTopicSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $topics = [
            [
                'name' => 'Tin tức',
                'slug' => 'tin-tuc',
                'description' => 'Tin tức mới nhất về sản phẩm, thị trường và thương hiệu.',
                'status' => true,
                'sort_order' => 1,
            ],
            [
                'name' => 'Kinh nghiệm',
                'slug' => 'kinh-nghiem',
                'description' => 'Chia sẻ mẹo sử dụng và bảo quản dụng cụ điện.',
                'status' => true,
                'sort_order' => 2,
            ],
            [
                'name' => 'Khuyến mãi',
                'slug' => 'khuyen-mai',
                'description' => 'Thông tin ưu đãi, giảm giá và chương trình đang diễn ra.',
                'status' => true,
                'sort_order' => 3,
            ],
        ];

        foreach ($topics as $topic) {
            PostTopic::updateOrCreate(
                ['slug' => $topic['slug']],
                $topic
            );
        }
    }
}