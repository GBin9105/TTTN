<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Api\Concerns\ApiResponse;
use App\Models\Order;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    use ApiResponse;

    public function index(Request $request)
    {
        $query = Order::query();

        if ($request->filled('q')) {
            $q = trim($request->q);
            $query->where(function ($sub) use ($q) {
                $sub->where('code', 'like', "%{$q}%")
                    ->orWhere('customer_name', 'like', "%{$q}%")
                    ->orWhere('customer_email', 'like', "%{$q}%")
                    ->orWhere('customer_phone', 'like', "%{$q}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $orders = $query->latest('id')->paginate((int) $request->get('per_page', 10));

        return $this->success($orders, 'Orders fetched successfully.');
    }

    public function show(Order $order)
    {
        return $this->success(
            $order->load(['items', 'items.product', 'user']),
            'Order detail fetched successfully.'
        );
    }

    public function updateStatus(Request $request, Order $order)
    {
        $data = $request->validate([
            'status' => ['required', 'string', 'max:50'],
        ]);

        $order->update([
            'status' => $data['status'],
        ]);

        return $this->success($order->fresh(), 'Order status updated successfully.');
    }

    public function destroy(Order $order)
    {
        $order->delete();

        return $this->success(null, 'Order deleted successfully.');
    }
}