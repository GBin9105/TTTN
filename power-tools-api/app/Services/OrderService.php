<?php

namespace App\Services;

use App\Models\InventoryLog;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderStatusHistory;
use App\Models\Review;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class OrderService
{
    public function index(User $user, array $filters = []): array
    {
        $perPage = (int) ($filters['per_page'] ?? 10);

        $query = Order::query()
            ->withCount('items')
            ->where('user_id', $user->id);

        if (! empty($filters['status'])) {
            $query->where('order_status', $filters['status']);
        }

        if (! empty($filters['q'])) {
            $q = trim($filters['q']);
            $query->where(function ($subQuery) use ($q) {
                $subQuery->where('code', 'like', "%{$q}%")
                    ->orWhere('customer_name', 'like', "%{$q}%")
                    ->orWhere('customer_phone', 'like', "%{$q}%");
            });
        }

        $paginator = $query
            ->orderByDesc('placed_at')
            ->orderByDesc('id')
            ->paginate($perPage);

        return [
            'items' => collect($paginator->items())->map(fn ($order) => [
                'id' => $order->id,
                'code' => $order->code,
                'customer_name' => $order->customer_name,
                'customer_phone' => $order->customer_phone,
                'payment_method' => $order->payment_method,
                'payment_status' => $order->payment_status,
                'order_status' => $order->order_status,
                'subtotal' => $order->subtotal,
                'discount_amount' => $order->discount_amount,
                'shipping_fee' => $order->shipping_fee,
                'grand_total' => $order->grand_total,
                'items_count' => $order->items_count,
                'placed_at' => $order->placed_at,
                'confirmed_at' => $order->confirmed_at,
                'cancelled_at' => $order->cancelled_at,
                'created_at' => $order->created_at,
                'updated_at' => $order->updated_at,
            ])->values()->all(),
            'pagination' => [
                'current_page' => $paginator->currentPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
                'last_page' => $paginator->lastPage(),
                'from' => $paginator->firstItem(),
                'to' => $paginator->lastItem(),
            ],
        ];
    }

    public function showByCode(User $user, string $code): ?array
    {
        $order = Order::query()
            ->with([
                'items.product',
                'statusHistories.changer:id,name',
            ])
            ->where('user_id', $user->id)
            ->where('code', $code)
            ->first();

        if (! $order) {
            return null;
        }

        return [
            'order' => [
                'id' => $order->id,
                'code' => $order->code,
                'customer_name' => $order->customer_name,
                'customer_email' => $order->customer_email,
                'customer_phone' => $order->customer_phone,
                'shipping_address' => $order->shipping_address,
                'note' => $order->note,
                'cancel_reason' => $order->cancel_reason,
                'payment_method' => $order->payment_method,
                'payment_status' => $order->payment_status,
                'order_status' => $order->order_status,
                'subtotal' => $order->subtotal,
                'discount_amount' => $order->discount_amount,
                'shipping_fee' => $order->shipping_fee,
                'grand_total' => $order->grand_total,
                'placed_at' => $order->placed_at,
                'confirmed_at' => $order->confirmed_at,
                'cancelled_at' => $order->cancelled_at,
                'items' => $order->items->map(fn ($item) => [
                    'id' => $item->id,
                    'product_id' => $item->product_id,
                    'product_name' => $item->product_name,
                    'product_sku' => $item->product_sku,
                    'product_thumbnail' => $item->product_thumbnail,
                    'unit_price' => $item->unit_price,
                    'discount_amount' => $item->discount_amount,
                    'quantity' => $item->quantity,
                    'line_total' => $item->line_total,
                ])->values()->all(),
                'status_histories' => $order->statusHistories->map(fn ($history) => [
                    'id' => $history->id,
                    'old_status' => $history->old_status,
                    'new_status' => $history->new_status,
                    'note' => $history->note,
                    'changed_at' => $history->changed_at,
                    'changed_by' => $history->changer ? [
                        'id' => $history->changer->id,
                        'name' => $history->changer->name,
                    ] : null,
                ])->values()->all(),
                'created_at' => $order->created_at,
                'updated_at' => $order->updated_at,
            ],
        ];
    }

    public function cancelByCode(User $user, string $code, array $data): Order
    {
        return DB::transaction(function () use ($user, $code, $data) {
            $order = Order::query()
                ->with(['items.product'])
                ->where('user_id', $user->id)
                ->where('code', $code)
                ->first();

            if (! $order) {
                throw ValidationException::withMessages([
                    'order' => ['Không tìm thấy đơn hàng.'],
                ]);
            }

            if ($order->order_status !== 'pending_verification') {
                throw ValidationException::withMessages([
                    'order' => ['Chỉ được hủy đơn hàng chưa xác thực.'],
                ]);
            }

            foreach ($order->items as $item) {
                if ($item->product) {
                    $qtyBefore = $item->product->stock_qty;
                    $qtyAfter = $qtyBefore + $item->quantity;

                    $item->product->update([
                        'stock_qty' => $qtyAfter,
                    ]);

                    InventoryLog::create([
                        'product_id' => $item->product->id,
                        'action' => 'cancel_order',
                        'qty_before' => $qtyBefore,
                        'qty_change' => $item->quantity,
                        'qty_after' => $qtyAfter,
                        'reference_type' => 'order',
                        'reference_id' => $order->id,
                        'note' => 'Hoàn kho do hủy đơn ' . $order->code,
                        'created_by' => $user->id,
                    ]);
                }
            }

            $oldStatus = $order->order_status;

            $order->update([
                'order_status' => 'cancelled',
                'cancel_reason' => $data['cancel_reason'] ?? 'Khách hàng chủ động hủy đơn.',
                'cancelled_at' => now(),
            ]);

            OrderStatusHistory::create([
                'order_id' => $order->id,
                'old_status' => $oldStatus,
                'new_status' => 'cancelled',
                'note' => $data['cancel_reason'] ?? 'Khách hàng chủ động hủy đơn.',
                'changed_by' => $user->id,
                'changed_at' => now(),
            ]);

            return $order->fresh(['items', 'statusHistories']);
        });
    }

    public function storeReview(User $user, array $data): Review
    {
        $orderItem = OrderItem::query()
            ->with('order')
            ->findOrFail($data['order_item_id']);

        if (! $orderItem->order || $orderItem->order->user_id !== $user->id) {
            throw ValidationException::withMessages([
                'order_item_id' => ['Bạn không có quyền đánh giá sản phẩm này.'],
            ]);
        }

        if ($orderItem->order->order_status !== 'completed') {
            throw ValidationException::withMessages([
                'order_item_id' => ['Chỉ có thể đánh giá sản phẩm từ đơn hàng đã hoàn tất.'],
            ]);
        }

        return Review::updateOrCreate(
            [
                'product_id' => $orderItem->product_id,
                'user_id' => $user->id,
                'order_item_id' => $orderItem->id,
            ],
            [
                'rating' => $data['rating'],
                'title' => $data['title'] ?? null,
                'content' => $data['content'] ?? null,
                'is_approved' => false,
                'approved_by' => null,
                'approved_at' => null,
            ]
        );
    }
}