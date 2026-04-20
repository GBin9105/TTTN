<?php

namespace App\Http\Controllers\Api\Client;

use App\Http\Controllers\Controller;
use App\Services\ProductService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Throwable;

class ProductController extends Controller
{
    public function __construct(
        protected ProductService $productService
    ) {}

    /**
     * Danh sách sản phẩm:
     * - filter
     * - sorting
     * - phân trang
     * - list / grid do frontend xử lý
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $data = $this->productService->index($request->all());

            return response()->json([
                'success' => true,
                'message' => 'Lấy danh sách sản phẩm thành công.',
                'data' => $data,
            ]);
        } catch (Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Không thể lấy danh sách sản phẩm.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Chi tiết sản phẩm + sản phẩm liên quan
     */
    public function show(string $slug): JsonResponse
    {
        try {
            $data = $this->productService->show($slug);

            if (! $data) {
                return response()->json([
                    'success' => false,
                    'message' => 'Không tìm thấy sản phẩm.',
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Lấy chi tiết sản phẩm thành công.',
                'data' => $data,
            ]);
        } catch (Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Không thể lấy chi tiết sản phẩm.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}