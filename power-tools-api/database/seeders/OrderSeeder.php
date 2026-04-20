<?php

namespace Database\Seeders;

use App\Models\InventoryLog;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderStatusHistory;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class OrderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $admin = User::where('role', 'admin')->first();
        $customer1 = User::where('email', 'an@example.com')->first();
        $customer2 = User::where('email', 'binh@example.com')->first();

        $completedOrder = Order::firstOrCreate(
            ['code' => 'DH000001'],
            [
                'user_id' => $customer1?->id,
                'customer_name' => $customer1?->name ?? 'Nguyễn Văn An',
                'customer_email' => $customer1?->email ?? 'an@example.com',
                'customer_phone' => $customer1?->phone ?? '0900000002',
                'shipping_address' => $customer1?->address ?? 'Quận 1, TP. Hồ Chí Minh',
                'note' => 'Giao giờ hành chính',
                'cancel_reason' => null,
                'payment_method' => 'cod',
                'payment_status' => 'paid',
                'order_status' => 'completed',
                'subtotal' => 0,
                'discount_amount' => 0,
                'shipping_fee' => 30000,
                'grand_total' => 0,
                'placed_at' => now()->subDays(3),
                'confirmed_at' => now()->subDays(3)->addHours(2),
                'cancelled_at' => null,
            ]
        );

        if ($completedOrder->items()->count() === 0) {
            DB::transaction(function () use ($completedOrder, $admin) {
                $items = [
                    [
                        'slug' => 'may-khoan-be-tong-bosch-gbh-2-26-dre',
                        'quantity' => 1,
                        'discount_amount' => 325000,
                    ],
                    [
                        'slug' => 'bo-mui-khoan-da-nang-bosch-x-line-30-mon',
                        'quantity' => 2,
                        'discount_amount' => 150000,
                    ],
                ];

                $subtotal = 0;
                $discount = 0;

                foreach ($items as $row) {
                    $product = Product::where('slug', $row['slug'])->first();

                    if (! $product) {
                        continue;
                    }

                    $lineSubtotal = $product->price * $row['quantity'];
                    $lineTotal = $lineSubtotal - $row['discount_amount'];

                    OrderItem::create([
                        'order_id' => $completedOrder->id,
                        'product_id' => $product->id,
                        'product_name' => $product->name,
                        'product_sku' => $product->sku,
                        'product_thumbnail' => $product->thumbnail,
                        'unit_price' => $product->price,
                        'discount_amount' => $row['discount_amount'],
                        'quantity' => $row['quantity'],
                        'line_total' => $lineTotal,
                    ]);

                    $qtyBefore = $product->stock_qty;
                    $qtyAfter = max(0, $qtyBefore - $row['quantity']);

                    $product->update([
                        'stock_qty' => $qtyAfter,
                    ]);

                    InventoryLog::create([
                        'product_id' => $product->id,
                        'action' => 'order',
                        'qty_before' => $qtyBefore,
                        'qty_change' => -$row['quantity'],
                        'qty_after' => $qtyAfter,
                        'reference_type' => 'order',
                        'reference_id' => $completedOrder->id,
                        'note' => 'Xuất kho cho đơn ' . $completedOrder->code,
                        'created_by' => $admin?->id,
                    ]);

                    $subtotal += $lineSubtotal;
                    $discount += $row['discount_amount'];
                }

                $completedOrder->update([
                    'subtotal' => $subtotal,
                    'discount_amount' => $discount,
                    'grand_total' => ($subtotal - $discount) + $completedOrder->shipping_fee,
                ]);

                $histories = [
                    [
                        'old_status' => null,
                        'new_status' => 'pending_verification',
                        'note' => 'Đơn hàng được tạo',
                        'changed_by' => $completedOrder->user_id,
                        'changed_at' => now()->subDays(3),
                    ],
                    [
                        'old_status' => 'pending_verification',
                        'new_status' => 'confirmed',
                        'note' => 'Đơn hàng được xác nhận',
                        'changed_by' => $admin?->id,
                        'changed_at' => now()->subDays(3)->addHours(2),
                    ],
                    [
                        'old_status' => 'confirmed',
                        'new_status' => 'processing',
                        'note' => 'Đơn hàng đang xử lý',
                        'changed_by' => $admin?->id,
                        'changed_at' => now()->subDays(2),
                    ],
                    [
                        'old_status' => 'processing',
                        'new_status' => 'shipping',
                        'note' => 'Đơn hàng đang giao',
                        'changed_by' => $admin?->id,
                        'changed_at' => now()->subDay(),
                    ],
                    [
                        'old_status' => 'shipping',
                        'new_status' => 'completed',
                        'note' => 'Đơn hàng hoàn tất',
                        'changed_by' => $admin?->id,
                        'changed_at' => now()->subHours(10),
                    ],
                ];

                foreach ($histories as $history) {
                    OrderStatusHistory::create([
                        'order_id' => $completedOrder->id,
                        ...$history,
                    ]);
                }
            });
        }

        $pendingOrder = Order::firstOrCreate(
            ['code' => 'DH000002'],
            [
                'user_id' => $customer2?->id,
                'customer_name' => $customer2?->name ?? 'Trần Thị Bình',
                'customer_email' => $customer2?->email ?? 'binh@example.com',
                'customer_phone' => $customer2?->phone ?? '0900000003',
                'shipping_address' => $customer2?->address ?? 'Thủ Đức, TP. Hồ Chí Minh',
                'note' => 'Gọi trước khi giao',
                'cancel_reason' => null,
                'payment_method' => 'bank_transfer',
                'payment_status' => 'unpaid',
                'order_status' => 'pending_verification',
                'subtotal' => 3990000,
                'discount_amount' => 0,
                'shipping_fee' => 0,
                'grand_total' => 3990000,
                'placed_at' => now()->subHours(8),
                'confirmed_at' => null,
                'cancelled_at' => null,
            ]
        );

        if ($pendingOrder->items()->count() === 0) {
            $product = Product::where('slug', 'may-bat-vit-dung-pin-dewalt-dcf787d2')->first();

            if ($product) {
                OrderItem::create([
                    'order_id' => $pendingOrder->id,
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'product_sku' => $product->sku,
                    'product_thumbnail' => $product->thumbnail,
                    'unit_price' => $product->price,
                    'discount_amount' => 0,
                    'quantity' => 1,
                    'line_total' => $product->price,
                ]);
            }

            OrderStatusHistory::create([
                'order_id' => $pendingOrder->id,
                'old_status' => null,
                'new_status' => 'pending_verification',
                'note' => 'Đơn hàng mới, chờ xác thực',
                'changed_by' => $pendingOrder->user_id,
                'changed_at' => now()->subHours(8),
            ]);
        }

        $cancelledOrder = Order::firstOrCreate(
            ['code' => 'DH000003'],
            [
                'user_id' => $customer1?->id,
                'customer_name' => $customer1?->name ?? 'Nguyễn Văn An',
                'customer_email' => $customer1?->email ?? 'an@example.com',
                'customer_phone' => $customer1?->phone ?? '0900000002',
                'shipping_address' => $customer1?->address ?? 'Quận 1, TP. Hồ Chí Minh',
                'note' => null,
                'cancel_reason' => 'Khách đổi nhu cầu mua hàng',
                'payment_method' => 'cod',
                'payment_status' => 'unpaid',
                'order_status' => 'cancelled',
                'subtotal' => 990000,
                'discount_amount' => 0,
                'shipping_fee' => 0,
                'grand_total' => 990000,
                'placed_at' => now()->subDays(1),
                'confirmed_at' => null,
                'cancelled_at' => now()->subHours(20),
            ]
        );

        if ($cancelledOrder->items()->count() === 0) {
            $product = Product::where('slug', 'may-mai-goc-stanley-stgs8100')->first();

            if ($product) {
                OrderItem::create([
                    'order_id' => $cancelledOrder->id,
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'product_sku' => $product->sku,
                    'product_thumbnail' => $product->thumbnail,
                    'unit_price' => $product->price,
                    'discount_amount' => 0,
                    'quantity' => 1,
                    'line_total' => $product->price,
                ]);
            }

            OrderStatusHistory::create([
                'order_id' => $cancelledOrder->id,
                'old_status' => null,
                'new_status' => 'pending_verification',
                'note' => 'Đơn hàng được tạo',
                'changed_by' => $cancelledOrder->user_id,
                'changed_at' => now()->subDay(),
            ]);

            OrderStatusHistory::create([
                'order_id' => $cancelledOrder->id,
                'old_status' => 'pending_verification',
                'new_status' => 'cancelled',
                'note' => 'Đơn hàng bị hủy trước khi xác thực',
                'changed_by' => $cancelledOrder->user_id,
                'changed_at' => now()->subHours(20),
            ]);
        }
    }
}