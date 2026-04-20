<?php

namespace Database\Seeders;

use App\Models\InventoryLog;
use App\Models\Product;
use App\Models\StockReceipt;
use App\Models\StockReceiptItem;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class StockReceiptSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $admin = User::where('role', 'admin')->first();

        $receipt = StockReceipt::firstOrCreate(
            ['code' => 'PN000001'],
            [
                'supplier_name' => 'Công ty TNHH Thiết bị Cơ khí Việt',
                'supplier_phone' => '02838889999',
                'supplier_email' => 'sales@thietbico-khi.vn',
                'supplier_address' => 'Quận Bình Tân, TP. Hồ Chí Minh',
                'total_quantity' => 0,
                'total_amount' => 0,
                'note' => 'Phiếu nhập kho mẫu ban đầu',
                'status' => 'completed',
                'created_by' => $admin?->id,
                'imported_at' => now()->subDays(7),
            ]
        );

        if ($receipt->items()->count() > 0) {
            return;
        }

        $items = [
            [
                'product_slug' => 'may-khoan-be-tong-bosch-gbh-2-26-dre',
                'quantity' => 5,
                'import_price' => 2500000,
            ],
            [
                'product_slug' => 'may-khoan-pin-makita-hp333dsye',
                'quantity' => 7,
                'import_price' => 2200000,
            ],
            [
                'product_slug' => 'may-mai-goc-stanley-stgs8100',
                'quantity' => 10,
                'import_price' => 700000,
            ],
        ];

        DB::transaction(function () use ($items, $receipt, $admin) {
            $totalQty = 0;
            $totalAmount = 0;

            foreach ($items as $item) {
                $product = Product::where('slug', $item['product_slug'])->first();

                if (! $product) {
                    continue;
                }

                $lineTotal = $item['quantity'] * $item['import_price'];

                StockReceiptItem::create([
                    'stock_receipt_id' => $receipt->id,
                    'product_id' => $product->id,
                    'quantity' => $item['quantity'],
                    'import_price' => $item['import_price'],
                    'line_total' => $lineTotal,
                ]);

                $qtyBefore = $product->stock_qty;
                $qtyAfter = $qtyBefore + $item['quantity'];

                $product->update([
                    'stock_qty' => $qtyAfter,
                ]);

                InventoryLog::create([
                    'product_id' => $product->id,
                    'action' => 'import',
                    'qty_before' => $qtyBefore,
                    'qty_change' => $item['quantity'],
                    'qty_after' => $qtyAfter,
                    'reference_type' => 'stock_receipt',
                    'reference_id' => $receipt->id,
                    'note' => 'Nhập kho từ phiếu ' . $receipt->code,
                    'created_by' => $admin?->id,
                ]);

                $totalQty += $item['quantity'];
                $totalAmount += $lineTotal;
            }

            $receipt->update([
                'total_quantity' => $totalQty,
                'total_amount' => $totalAmount,
            ]);
        });
    }
}