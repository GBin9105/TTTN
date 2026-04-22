<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Api\Concerns\ApiResponse;
use App\Models\InventoryLog;
use Illuminate\Http\Request;

class InventoryLogController extends Controller
{
    use ApiResponse;

    public function index(Request $request)
    {
        $query = InventoryLog::query()->with('product');

        if ($request->filled('product_id')) {
            $query->where('product_id', $request->product_id);
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        $logs = $query->latest('id')->paginate((int) $request->get('per_page', 10));

        return $this->success($logs, 'Inventory logs fetched successfully.');
    }

    public function show(InventoryLog $inventoryLog)
    {
        return $this->success(
            $inventoryLog->load('product'),
            'Inventory log detail fetched successfully.'
        );
    }
}