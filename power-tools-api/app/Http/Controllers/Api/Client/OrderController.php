<?php

namespace App\Http\Controllers\Api\Client;

use App\Http\Controllers\Controller;
use App\Http\Requests\Client\CancelOrderRequest;
use App\Services\OrderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Throwable;

class OrderController extends Controller
{
    public function __construct(
        protected OrderService $orderService
    ) {}

    /**
     * Danh sách đơn hàng của thành viên
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $user = $request->user();

            if (! $user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Chưa đăng nhập.',
                ], 401);
            }

            $data = $this->orderService->index($user, $request->all());

            return response()->json([
                'success' => true,
                'message' => 'Lấy danh sách đơn hàng thành công.',
                'data' => $data,
            ]);
        } catch (Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Không thể lấy danh sách đơn hàng.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Chi tiết đơn hàng theo mã đơn
     */
    public function show(Request $request, string $code): JsonResponse
    {
        try {
            $user = $request->user();

            if (! $user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Chưa đăng nhập.',
                ], 401);
            }

            $data = $this->orderService->showByCode($user, $code);

            if (! $data) {
                return response()->json([
                    'success' => false,
                    'message' => 'Không tìm thấy đơn hàng.',
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Lấy chi tiết đơn hàng thành công.',
                'data' => $data,
            ]);
        } catch (Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Không thể lấy chi tiết đơn hàng.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Hủy đơn hàng chưa xác thực / chưa xử lý
     */
    public function cancel(CancelOrderRequest $request, string $code): JsonResponse
    {
        try {
            $user = $request->user();

            if (! $user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Chưa đăng nhập.',
                ], 401);
            }

            $order = $this->orderService->cancelByCode(
                $user,
                $code,
                $request->validated()
            );

            return response()->json([
                'success' => true,
                'message' => 'Hủy đơn hàng thành công.',
                'data' => [
                    'order' => $order,
                ],
            ]);
        } catch (Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Hủy đơn hàng thất bại.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}