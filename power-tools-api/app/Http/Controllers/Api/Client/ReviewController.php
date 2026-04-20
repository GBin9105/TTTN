<?php

namespace App\Http\Controllers\Api\Client;

use App\Http\Controllers\Controller;
use App\Http\Requests\Client\StoreReviewRequest;
use App\Services\OrderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Throwable;

class ReviewController extends Controller
{
    public function __construct(
        protected OrderService $orderService
    ) {}

    /**
     * Gửi đánh giá sản phẩm
     * Có thể để logic thật nằm ở ReviewService sau,
     * nhưng hiện tại giữ controller mỏng bằng cách gọi service liên quan.
     */
    public function store(StoreReviewRequest $request): JsonResponse
    {
        try {
            $user = $request->user();

            if (! $user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Chưa đăng nhập.',
                ], 401);
            }

            $review = $this->orderService->storeReview(
                $user,
                $request->validated()
            );

            return response()->json([
                'success' => true,
                'message' => 'Gửi đánh giá thành công. Đánh giá sẽ được kiểm duyệt trước khi hiển thị.',
                'data' => [
                    'review' => $review,
                ],
            ], 201);
        } catch (Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gửi đánh giá thất bại.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}