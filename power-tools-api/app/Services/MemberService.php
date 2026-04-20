<?php

namespace App\Services;

use App\Models\User;

class MemberService
{
    public function index(User $user): array
    {
        $ordersQuery = $user->orders();

        return [
            'profile' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'avatar' => $user->avatar,
                'address' => $user->address,
                'role' => $user->role,
                'status' => (bool) $user->status,
                'last_login_at' => $user->last_login_at,
                'created_at' => $user->created_at,
                'updated_at' => $user->updated_at,
            ],
            'stats' => [
                'total_orders' => (clone $ordersQuery)->count(),
                'pending_verification_orders' => (clone $ordersQuery)->where('order_status', 'pending_verification')->count(),
                'confirmed_orders' => (clone $ordersQuery)->where('order_status', 'confirmed')->count(),
                'processing_orders' => (clone $ordersQuery)->where('order_status', 'processing')->count(),
                'shipping_orders' => (clone $ordersQuery)->where('order_status', 'shipping')->count(),
                'completed_orders' => (clone $ordersQuery)->where('order_status', 'completed')->count(),
                'cancelled_orders' => (clone $ordersQuery)->where('order_status', 'cancelled')->count(),
                'reviews_count' => $user->reviews()->count(),
            ],
            'recent_orders' => $user->orders()
                ->orderByDesc('placed_at')
                ->orderByDesc('id')
                ->limit(5)
                ->get()
                ->map(fn ($order) => [
                    'id' => $order->id,
                    'code' => $order->code,
                    'order_status' => $order->order_status,
                    'payment_status' => $order->payment_status,
                    'grand_total' => $order->grand_total,
                    'placed_at' => $order->placed_at,
                ])
                ->values()
                ->all(),
        ];
    }
}