<?php

namespace App\Services;

use App\Models\InventoryLog;
use App\Models\Product;
use App\Models\StockReceipt;
use App\Models\StockReceiptItem;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class StockService
{
    public function indexReceipts(array $filters = []): LengthAwarePaginator
    {
        $perPage = (int) ($filters['per_page'] ?? 15);

        $query = StockReceipt::query()
            ->with('creator:id,name');

        if (! empty($filters['q'])) {
            $q = trim($filters['q']);
            $query->where(function ($subQuery) use ($q) {
                $subQuery->where('code', 'like', "%{$q}%")
                    ->orWhere('supplier_name', 'like', "%{$q}%")
                    ->orWhere('supplier_phone', 'like', "%{$q}%")
                    ->orWhere('supplier_email', 'like', "%{$q}%");
            });
        }

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        return $query
            ->orderByDesc('imported_at')
            ->orderByDesc('id')
            ->paginate($perPage);
    }

    public function showReceipt(StockReceipt $stockReceipt): StockReceipt
    {
        return $stockReceipt->load([
            'creator:id,name',
            'items.product:id,name,slug,sku,thumbnail',
        ]);
    }

    public function storeReceipt(array $data, ?User $admin = null): StockReceipt
    {
        return DB::transaction(function () use ($data, $admin) {
            if (empty($data['items']) || ! is_array($data['items'])) {
                throw ValidationException::withMessages([
                    'items' => ['Phiếu nhập kho phải có ít nhất một sản phẩm.'],
                ]);
            }

            $receipt = StockReceipt::create([
                'code' => $data['code'] ?? $this->generateReceiptCode(),
                'supplier_name' => $data['supplier_name'] ?? null,
                'supplier_phone' => $data['supplier_phone'] ?? null,
                'supplier_email' => $data['supplier_email'] ?? null,
                'supplier_address' => $data['supplier_address'] ?? null,
                'total_quantity' => 0,
                'total_amount' => 0,
                'note' => $data['note'] ?? null,
                'status' => $data['status'] ?? 'completed',
                'created_by' => $admin?->id,
                'imported_at' => $data['imported_at'] ?? now(),
            ]);

            $totalQuantity = 0;
            $totalAmount = 0;

            foreach ($data['items'] as $item) {
                $product = Product::findOrFail($item['product_id']);
                $quantity = (int) $item['quantity'];
                $importPrice = (float) $item['import_price'];
                $lineTotal = $quantity * $importPrice;

                StockReceiptItem::create([
                    'stock_receipt_id' => $receipt->id,
                    'product_id' => $product->id,
                    'quantity' => $quantity,
                    'import_price' => $importPrice,
                    'line_total' => round($lineTotal, 2),
                ]);

                $qtyBefore = $product->stock_qty;
                $qtyAfter = $qtyBefore + $quantity;

                $product->update([
                    'stock_qty' => $qtyAfter,
                ]);

                InventoryLog::create([
                    'product_id' => $product->id,
                    'action' => 'import',
                    'qty_before' => $qtyBefore,
                    'qty_change' => $quantity,
                    'qty_after' => $qtyAfter,
                    'reference_type' => 'stock_receipt',
                    'reference_id' => $receipt->id,
                    'note' => 'Nhập kho từ phiếu ' . $receipt->code,
                    'created_by' => $admin?->id,
                ]);

                $totalQuantity += $quantity;
                $totalAmount += $lineTotal;
            }

            $receipt->update([
                'total_quantity' => $totalQuantity,
                'total_amount' => round($totalAmount, 2),
            ]);

            return $receipt->fresh(['creator:id,name', 'items.product:id,name,slug,sku,thumbnail']);
        });
    }

    public function inventoryLogs(array $filters = []): LengthAwarePaginator
    {
        $perPage = (int) ($filters['per_page'] ?? 20);

        $query = InventoryLog::query()
            ->with(['product:id,name,slug,sku', 'creator:id,name']);

        if (! empty($filters['action'])) {
            $query->where('action', $filters['action']);
        }

        if (! empty($filters['product_id'])) {
            $query->where('product_id', $filters['product_id']);
        }

        if (! empty($filters['reference_type'])) {
            $query->where('reference_type', $filters['reference_type']);
        }

        return $query
            ->orderByDesc('id')
            ->paginate($perPage);
    }

    protected function generateReceiptCode(): string
    {
        do {
            $code = 'PN' . now()->format('Ymd') . random_int(1000, 9999);
        } while (StockReceipt::where('code', $code)->exists());

        return $code;
    }
}