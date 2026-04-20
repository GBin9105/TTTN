<?php

namespace Database\Seeders;

use App\Models\Post;
use App\Models\PostTopic;
use App\Models\User;
use Illuminate\Database\Seeder;

class PostSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $admin = User::where('role', 'admin')->first();

        $posts = [
            [
                'topic_slug' => 'tin-tuc',
                'title' => 'Xu hướng sử dụng dụng cụ điện cầm tay năm 2026',
                'slug' => 'xu-huong-su-dung-dung-cu-dien-cam-tay-nam-2026',
                'thumbnail' => '/storage/posts/post-1.jpg',
                'excerpt' => 'Những xu hướng nổi bật về máy khoan, máy cắt và máy bắt vít trong năm 2026.',
                'content' => '<p>Thị trường dụng cụ điện cầm tay tiếp tục tăng trưởng nhờ nhu cầu thi công, sửa chữa và DIY tại nhà.</p>',
                'is_featured' => true,
                'status' => 'published',
                'published_at' => now()->subDays(5),
            ],
            [
                'topic_slug' => 'kinh-nghiem',
                'title' => 'Cách chọn máy khoan phù hợp cho gia đình',
                'slug' => 'cach-chon-may-khoan-phu-hop-cho-gia-dinh',
                'thumbnail' => '/storage/posts/post-2.jpg',
                'excerpt' => 'Hướng dẫn chọn máy khoan dựa trên mục đích sử dụng và ngân sách.',
                'content' => '<p>Khi chọn máy khoan cho gia đình, bạn nên ưu tiên các tiêu chí như công suất, độ bền, thương hiệu và mức giá.</p>',
                'is_featured' => true,
                'status' => 'published',
                'published_at' => now()->subDays(4),
            ],
            [
                'topic_slug' => 'kinh-nghiem',
                'title' => '5 lưu ý an toàn khi sử dụng máy mài góc',
                'slug' => '5-luu-y-an-toan-khi-su-dung-may-mai-goc',
                'thumbnail' => '/storage/posts/post-3.jpg',
                'excerpt' => 'Những nguyên tắc an toàn cơ bản để tránh rủi ro khi sử dụng máy mài.',
                'content' => '<p>Máy mài góc là thiết bị mạnh, vì vậy người dùng phải sử dụng kính bảo hộ, găng tay và kiểm tra đá mài trước khi vận hành.</p>',
                'is_featured' => false,
                'status' => 'published',
                'published_at' => now()->subDays(3),
            ],
            [
                'topic_slug' => 'khuyen-mai',
                'title' => 'Chương trình ưu đãi máy khoan Bosch cuối tháng',
                'slug' => 'chuong-trinh-uu-dai-may-khoan-bosch-cuoi-thang',
                'thumbnail' => '/storage/posts/post-4.jpg',
                'excerpt' => 'Nhiều dòng máy khoan Bosch đang được giảm giá hấp dẫn.',
                'content' => '<p>Chương trình ưu đãi áp dụng cho các dòng máy khoan Bosch chính hãng với số lượng có hạn.</p>',
                'is_featured' => true,
                'status' => 'published',
                'published_at' => now()->subDays(2),
            ],
        ];

        foreach ($posts as $item) {
            $topic = PostTopic::where('slug', $item['topic_slug'])->first();

            Post::updateOrCreate(
                ['slug' => $item['slug']],
                [
                    'post_topic_id' => $topic?->id,
                    'author_id' => $admin?->id,
                    'title' => $item['title'],
                    'thumbnail' => $item['thumbnail'],
                    'excerpt' => $item['excerpt'],
                    'content' => $item['content'],
                    'is_featured' => $item['is_featured'],
                    'status' => $item['status'],
                    'published_at' => $item['published_at'],
                ]
            );
        }
    }
}