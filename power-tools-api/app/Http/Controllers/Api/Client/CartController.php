<?php

namespace App\Http\Controllers\Api\Client;

use App\Http\Controllers\Controller;
use App\Http\Requests\Client\AddToCartRequest;
use App\Http\Requests\Client\UpdateCartItemRequest;
use App\Models\CartItem;
use App\Services\CartService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Throwable;

class CartController extends Controller
{
    public function __construct(
        protected CartService $cartService
    ) {}

    /**
     * Xem giỏ hàng hiện tại theo user hoặc session
     */
    public function show(Request $request): JsonResponse
    {
        try {
            $sessionId = $this->getSessionId($request);

            $data = $this->cartService->show(
                $request->user(),
                $sessionId
            );

            return response()->json([
                'success' => true,
                'message' => 'Lấy giỏ hàng thành công.',
                'data' => $data,
            ]);
        } catch (Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Không thể lấy giỏ hàng.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Thêm sản phẩm vào giỏ
     */
    public function addItem(AddToCartRequest $request): JsonResponse
    {
        try {
            $sessionId = $this->getSessionId($request);

            $data = $this->cartService->addItem(
                $request->validated(),
                $request->user(),
                $sessionId
            );

            return response()->json([
                'success' => true,
                'message' => 'Thêm sản phẩm vào giỏ hàng thành công.',
                'data' => $data,
            ], 201);
        } catch (Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Thêm sản phẩm vào giỏ hàng thất bại.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Cập nhật số lượng sản phẩm trong giỏ
     */
    public function updateItem(UpdateCartItemRequest $request, CartItem $cartItem): JsonResponse
    {
        try {
            $sessionId = $this->getSessionId($request);

            $data = $this->cartService->updateItem(
                $cartItem,
                $request->validated(),
                $request->user(),
                $sessionId
            );

            return response()->json([
                'success' => true,
                'message' => 'Cập nhật giỏ hàng thành công.',
                'data' => $data,
            ]);
        } catch (Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Cập nhật giỏ hàng thất bại.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Xóa một sản phẩm khỏi giỏ
     */
    public function removeItem(Request $request, CartItem $cartItem): JsonResponse
    {
        try {
            $sessionId = $this->getSessionId($request);

            $data = $this->cartService->removeItem(
                $cartItem,
                $request->user(),
                $sessionId
            );

            return response()->json([
                'success' => true,
                'message' => 'Xóa sản phẩm khỏi giỏ hàng thành công.',
                'data' => $data,
            ]);
        } catch (Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Xóa sản phẩm khỏi giỏ hàng thất bại.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Xóa toàn bộ giỏ hàng
     */
    public function clear(Request $request): JsonResponse
    {
        try {
            $sessionId = $this->getSessionId($request);

            $this->cartService->clear(
                $request->user(),
                $sessionId
            );

            return response()->json([
                'success' => true,
                'message' => 'Đã xóa toàn bộ giỏ hàng.',
            ]);
        } catch (Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Không thể xóa giỏ hàng.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    protected function getSessionId(Request $request): string
    {
        if (! $request->hasSession()) {
            $request->setLaravelSession(app('session')->driver());
        }

        if (! $request->session()->isStarted()) {
            $request->session()->start();
        }

        return $request->session()->getId();
    }
}