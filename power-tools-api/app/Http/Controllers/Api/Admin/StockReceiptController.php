<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Api\Concerns\ApiResponse;
use App\Models\InventoryLog;
use App\Models\Product;
use App\Models\StockReceipt;
use App\Models\StockReceiptItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StockReceiptController extends Controller
{
    use ApiResponse;

    public function index(Request $request)
    {
        $query = StockReceipt::query()->with(['items', 'items.product']);

        if ($request->filled('q')) {
            $q = trim($request->q);
            $query->where(function ($sub) use ($q) {
                $sub->where('code', 'like', "%{$q}%")
                    ->orWhere('supplier_name', 'like', "%{$q}%")
                    ->orWhere('note', 'like', "%{$q}%");
            });
        }

        $receipts = $query->latest('id')->paginate((int) $request->get('per_page', 10));

        return $this->success($receipts, 'Stock receipts fetched successfully.');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'code'                    => ['nullable', 'string', 'max:100', 'unique:stock_receipts,code'],
            'supplier_name'           => ['nullable', 'string', 'max:255'],
            'note'                    => ['nullable', 'string'],
            'received_at'             => ['nullable', 'date'],
            'items'                   => ['required', 'array', 'min:1'],
            'items.*.product_id'      => ['required', 'exists:products,id'],
            'items.*.quantity'        => ['required', 'integer', 'min:1'],
            'items.*.unit_price'      => ['required', 'numeric', 'min:0'],
        ]);

        DB::beginTransaction();

        try {
            $receipt = StockReceipt::create([
                'code'          => $data['code'] ?? 'SR-' . now()->format('YmdHis'),
                'supplier_name' => $data['supplier_name'] ?? null,
                'note'          => $data['note'] ?? null,
                'received_at'   => $data['received_at'] ?? now(),
            ]);

            foreach ($data['items'] as $item) {
                $receiptItem = StockReceiptItem::create([
                    'stock_receipt_id' => $receipt->id,
                    'product_id'       => $item['product_id'],
                    'quantity'         => $item['quantity'],
                    'unit_price'       => $item['unit_price'],
                    'subtotal'         => $item['quantity'] * $item['unit_price'],
                ]);

                $product = Product::findOrFail($item['product_id']);
                $oldStock = (int) ($product->stock_quantity ?? 0);
                $newStock = $oldStock + (int) $item['quantity'];

                $product->update([
                    'stock_quantity' => $newStock,
                ]);

                InventoryLog::create([
                    'product_id'      => $product->id,
                    'type'            => 'stock_in',
                    'quantity'        => $item['quantity'],
                    'stock_before'    => $oldStock,
                    'stock_after'     => $newStock,
                    'reference_type'  => StockReceipt::class,
                    'reference_id'    => $receipt->id,
                    'note'            => 'Nhập kho từ phiếu ' . $receipt->code,
                ]);
            }

            DB::commit();

            return $this->success(
                $receipt->load(['items', 'items.product']),
                'Stock receipt created successfully.',
                201
            );
        } catch (\Throwable $e) {
            DB::rollBack();
            return $this->error('Failed to create stock receipt.', $e->getMessage(), 500);
        }
    }

    public function show(StockReceipt $stockReceipt)
    {
        return $this->success(
            $stockReceipt->load(['items', 'items.product']),
            'Stock receipt detail fetched successfully.'
        );
    }
}