<?php

namespace Database\Seeders;

use App\Models\Order;
use App\Models\Review;
use Illuminate\Database\Seeder;

class ReviewSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $order = Order::where('code', 'DH000001')->with(['items', 'user'])->first();

        if (! $order || ! $order->user) {
            return;
        }

        foreach ($order->items as $index => $item) {
            Review::updateOrCreate(
                [
                    'product_id' => $item->product_id,
                    'user_id' => $order->user_id,
                    'order_item_id' => $item->id,
                ],
                [
                    'rating' => $index === 0 ? 5 : 4,
                    'title' => $index === 0 ? 'Sản phẩm rất tốt' : 'Phụ kiện dùng ổn',
                    'content' => $index === 0
                        ? 'Máy khoan chạy mạnh, cầm chắc tay, rất phù hợp thi công thực tế.'
                        : 'Bộ phụ kiện khá tiện, đủ dùng cho nhu cầu cơ bản và gia đình.',
                    'is_approved' => true,
                    'approved_by' => 1,
                    'approved_at' => now()->subHours(6),
                ]
            );
        }
    }
}