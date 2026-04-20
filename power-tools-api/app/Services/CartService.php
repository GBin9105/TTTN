<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CartService
{
    public function __construct(
        protected PromotionService $promotionService
    ) {}

    public function show(?User $user, string $sessionId): array
    {
        $cart = $this->getOrCreateCart($user, $sessionId);

        return $this->formatCart($cart->fresh(['items.product.brand', 'items.product.category', 'items.product.images', 'items.product.promotions']));
    }

    public function addItem(array $data, ?User $user, string $sessionId): array
    {
        return DB::transaction(function () use ($data, $user, $sessionId) {
            $cart = $this->getOrCreateCart($user, $sessionId);
            $product = Product::query()
                ->with(['brand', 'category', 'images', 'promotions'])
                ->where('status', true)
                ->findOrFail($data['product_id']);

            $quantity = (int) ($data['quantity'] ?? 1);

            if ($quantity < 1) {
                throw ValidationException::withMessages([
                    'quantity' => ['Số lượng phải lớn hơn 0.'],
                ]);
            }

            if ($product->stock_qty < $quantity) {
                throw ValidationException::withMessages([
                    'quantity' => ['Số lượng vượt quá tồn kho hiện tại.'],
                ]);
            }

            $promotion = $this->promotionService->resolveActivePromotionForProduct($product);
            $pricing = $this->promotionService->calculatePricing((float) $product->price, $promotion);

            $item = CartItem::where('cart_id', $cart->id)
                ->where('product_id', $product->id)
                ->first();

            if ($item) {
                $newQty = $item->quantity + $quantity;

                if ($newQty > $product->stock_qty) {
                    throw ValidationException::withMessages([
                        'quantity' => ['Tổng số lượng trong giỏ vượt quá tồn kho hiện tại.'],
                    ]);
                }

                $item->update([
                    'quantity' => $newQty,
                    'unit_price' => $pricing['final_price'],
                ]);
            } else {
                CartItem::create([
                    'cart_id' => $cart->id,
                    'product_id' => $product->id,
                    'quantity' => $quantity,
                    'unit_price' => $pricing['final_price'],
                ]);
            }

            return $this->formatCart($cart->fresh(['items.product.brand', 'items.product.category', 'items.product.images', 'items.product.promotions']));
        });
    }

    public function updateItem(CartItem $cartItem, array $data, ?User $user, string $sessionId): array
    {
        return DB::transaction(function () use ($cartItem, $data, $user, $sessionId) {
            $cart = $this->getOrCreateCart($user, $sessionId);
            $this->ensureCartItemBelongsToCurrentCart($cartItem, $cart);

            $quantity = (int) $data['quantity'];

            if ($quantity < 1) {
                throw ValidationException::withMessages([
                    'quantity' => ['Số lượng phải lớn hơn 0.'],
                ]);
            }

            $product = Product::query()
                ->with(['brand', 'category', 'images', 'promotions'])
                ->where('status', true)
                ->findOrFail($cartItem->product_id);

            if ($quantity > $product->stock_qty) {
                throw ValidationException::withMessages([
                    'quantity' => ['Số lượng vượt quá tồn kho hiện tại.'],
                ]);
            }

            $promotion = $this->promotionService->resolveActivePromotionForProduct($product);
            $pricing = $this->promotionService->calculatePricing((float) $product->price, $promotion);

            $cartItem->update([
                'quantity' => $quantity,
                'unit_price' => $pricing['final_price'],
            ]);

            return $this->formatCart($cart->fresh(['items.product.brand', 'items.product.category', 'items.product.images', 'items.product.promotions']));
        });
    }

    public function removeItem(CartItem $cartItem, ?User $user, string $sessionId): array
    {
        return DB::transaction(function () use ($cartItem, $user, $sessionId) {
            $cart = $this->getOrCreateCart($user, $sessionId);
            $this->ensureCartItemBelongsToCurrentCart($cartItem, $cart);

            $cartItem->delete();

            return $this->formatCart($cart->fresh(['items.product.brand', 'items.product.category', 'items.product.images', 'items.product.promotions']));
        });
    }

    public function clear(?User $user, string $sessionId): void
    {
        DB::transaction(function () use ($user, $sessionId) {
            $cart = $this->getCurrentCart($user, $sessionId);

            if (! $cart) {
                return;
            }

            $cart->items()->delete();
        });
    }

    public function getCurrentCart(?User $user, string $sessionId): ?Cart
    {
        if ($user) {
            $this->mergeSessionCartToUserCart($user, $sessionId);

            return Cart::query()
                ->with(['items.product.brand', 'items.product.category', 'items.product.images', 'items.product.promotions'])
                ->where('user_id', $user->id)
                ->where('status', 'active')
                ->latest('id')
                ->first();
        }

        return Cart::query()
            ->with(['items.product.brand', 'items.product.category', 'items.product.images', 'items.product.promotions'])
            ->whereNull('user_id')
            ->where('session_id', $sessionId)
            ->where('status', 'active')
            ->latest('id')
            ->first();
    }

    protected function getOrCreateCart(?User $user, string $sessionId): Cart
    {
        if ($user) {
            $this->mergeSessionCartToUserCart($user, $sessionId);

            return Cart::firstOrCreate(
                [
                    'user_id' => $user->id,
                    'status' => 'active',
                ],
                [
                    'session_id' => $sessionId,
                ]
            );
        }

        return Cart::firstOrCreate(
            [
                'user_id' => null,
                'session_id' => $sessionId,
                'status' => 'active',
            ]
        );
    }

    protected function mergeSessionCartToUserCart(User $user, string $sessionId): void
    {
        $sessionCart = Cart::query()
            ->whereNull('user_id')
            ->where('session_id', $sessionId)
            ->where('status', 'active')
            ->with(['items'])
            ->first();

        if (! $sessionCart) {
            return;
        }

        $userCart = Cart::firstOrCreate(
            [
                'user_id' => $user->id,
                'status' => 'active',
            ],
            [
                'session_id' => $sessionId,
            ]
        );

        foreach ($sessionCart->items as $sessionItem) {
            $existing = CartItem::where('cart_id', $userCart->id)
                ->where('product_id', $sessionItem->product_id)
                ->first();

            if ($existing) {
                $existing->update([
                    'quantity' => $existing->quantity + $sessionItem->quantity,
                ]);
            } else {
                CartItem::create([
                    'cart_id' => $userCart->id,
                    'product_id' => $sessionItem->product_id,
                    'quantity' => $sessionItem->quantity,
                    'unit_price' => $sessionItem->unit_price,
                ]);
            }
        }

        $sessionCart->items()->delete();
        $sessionCart->update(['status' => 'converted']);
    }

    protected function ensureCartItemBelongsToCurrentCart(CartItem $cartItem, Cart $cart): void
    {
        if ($cartItem->cart_id !== $cart->id) {
            throw ValidationException::withMessages([
                'cart_item' => ['Mục giỏ hàng không hợp lệ.'],
            ]);
        }
    }

    protected function formatCart(Cart $cart): array
    {
        $items = collect($cart->items)->map(function (CartItem $item) {
            $product = $item->product;
            $promotion = $product ? $this->promotionService->resolveActivePromotionForProduct($product) : null;
            $pricing = $product
                ? $this->promotionService->calculatePricing((float) $product->price, $promotion)
                : ['original_price' => (float) $item->unit_price, 'final_price' => (float) $item->unit_price, 'discount_amount' => 0];

            return [
                'id' => $item->id,
                'product_id' => $item->product_id,
                'quantity' => $item->quantity,
                'unit_price' => $pricing['final_price'],
                'line_total' => round($pricing['final_price'] * $item->quantity, 2),
                'product' => $product ? [
                    'id' => $product->id,
                    'name' => $product->name,
                    'slug' => $product->slug,
                    'sku' => $product->sku,
                    'thumbnail' => $product->thumbnail,
                    'stock_qty' => $product->stock_qty,
                    'price' => $pricing['original_price'],
                    'final_price' => $pricing['final_price'],
                    'discount_amount' => $pricing['discount_amount'],
                    'brand' => $product->brand?->name,
                    'category' => $product->category?->name,
                ] : null,
            ];
        })->values();

        $subtotal = $items->sum(fn ($item) => $item['line_total']);

        return [
            'id' => $cart->id,
            'user_id' => $cart->user_id,
            'session_id' => $cart->session_id,
            'status' => $cart->status,
            'items_count' => $items->sum('quantity'),
            'subtotal' => round($subtotal, 2),
            'items' => $items->all(),
            'created_at' => $cart->created_at,
            'updated_at' => $cart->updated_at,
        ];
    }
}