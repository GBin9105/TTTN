<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\InventoryLog;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderStatusHistory;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CheckoutService
{
    public function __construct(
        protected CartService $cartService,
        protected PromotionService $promotionService
    ) {}

    public function store(array $data, ?User $user, string $sessionId): Order
    {
        return DB::transaction(function () use ($data, $user, $sessionId) {
            $cart = $this->cartService->getCurrentCart($user, $sessionId);

            if (! $cart || $cart->items->isEmpty()) {
                throw ValidationException::withMessages([
                    'cart' => ['Giỏ hàng đang trống.'],
                ]);
            }

            $cart->load(['items.product.brand', 'items.product.category', 'items.product.images', 'items.product.promotions']);

            $subtotal = 0;
            $discountAmount = 0;
            $shippingFee = (float) ($data['shipping_fee'] ?? 0);

            foreach ($cart->items as $item) {
                if (! $item->product || ! $item->product->status) {
                    throw ValidationException::withMessages([
                        'cart' => ['Giỏ hàng có sản phẩm không hợp lệ hoặc đã ngừng kinh doanh.'],
                    ]);
                }

                if ($item->quantity > $item->product->stock_qty) {
                    throw ValidationException::withMessages([
                        'cart' => ['Một hoặc nhiều sản phẩm không đủ tồn kho để đặt hàng.'],
                    ]);
                }

                $promotion = $this->promotionService->resolveActivePromotionForProduct($item->product);
                $pricing = $this->promotionService->calculatePricing((float) $item->product->price, $promotion);

                $subtotal += ((float) $item->product->price * $item->quantity);
                $discountAmount += ($pricing['discount_amount'] * $item->quantity);
            }

            $grandTotal = ($subtotal - $discountAmount) + $shippingFee;

            $order = Order::create([
                'code' => $this->generateOrderCode(),
                'user_id' => $user?->id,
                'customer_name' => $data['customer_name'] ?? $user?->name,
                'customer_email' => $data['customer_email'] ?? $user?->email,
                'customer_phone' => $data['customer_phone'] ?? $user?->phone,
                'shipping_address' => $data['shipping_address'] ?? $user?->address,
                'note' => $data['note'] ?? null,
                'cancel_reason' => null,
                'payment_method' => $data['payment_method'] ?? 'cod',
                'payment_status' => 'unpaid',
                'order_status' => 'pending_verification',
                'subtotal' => round($subtotal, 2),
                'discount_amount' => round($discountAmount, 2),
                'shipping_fee' => round($shippingFee, 2),
                'grand_total' => round($grandTotal, 2),
                'placed_at' => now(),
                'confirmed_at' => null,
                'cancelled_at' => null,
            ]);

            foreach ($cart->items as $item) {
                $product = $item->product;
                $promotion = $this->promotionService->resolveActivePromotionForProduct($product);
                $pricing = $this->promotionService->calculatePricing((float) $product->price, $promotion);
                $lineDiscount = $pricing['discount_amount'] * $item->quantity;
                $lineTotal = $pricing['final_price'] * $item->quantity;

                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'product_sku' => $product->sku,
                    'product_thumbnail' => $product->thumbnail,
                    'unit_price' => $product->price,
                    'discount_amount' => round($lineDiscount, 2),
                    'quantity' => $item->quantity,
                    'line_total' => round($lineTotal, 2),
                ]);

                $qtyBefore = $product->stock_qty;
                $qtyAfter = max(0, $qtyBefore - $item->quantity);

                $product->update([
                    'stock_qty' => $qtyAfter,
                ]);

                InventoryLog::create([
                    'product_id' => $product->id,
                    'action' => 'order',
                    'qty_before' => $qtyBefore,
                    'qty_change' => -$item->quantity,
                    'qty_after' => $qtyAfter,
                    'reference_type' => 'order',
                    'reference_id' => $order->id,
                    'note' => 'Xuất kho do tạo đơn hàng ' . $order->code,
                    'created_by' => $user?->id,
                ]);
            }

            OrderStatusHistory::create([
                'order_id' => $order->id,
                'old_status' => null,
                'new_status' => 'pending_verification',
                'note' => 'Đơn hàng được tạo từ checkout.',
                'changed_by' => $user?->id,
                'changed_at' => now(),
            ]);

            $cart->items()->delete();
            $cart->update(['status' => 'converted']);

            return $order->fresh(['items', 'statusHistories']);
        });
    }

    protected function generateOrderCode(): string
    {
        do {
            $code = 'DH' . now()->format('Ymd') . random_int(1000, 9999);
        } while (Order::where('code', $code)->exists());

        return $code;
    }
}