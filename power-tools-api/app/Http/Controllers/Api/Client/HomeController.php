<?php

namespace App\Http\Controllers\Api\Client;

use App\Http\Controllers\Controller;
use App\Services\HomeService;
use Illuminate\Http\JsonResponse;
use Throwable;

class HomeController extends Controller
{
    public function __construct(
        protected HomeService $homeService
    ) {}

    /**
     * Trang chủ:
     * - banner
     * - sản phẩm khuyến mãi
     * - sản phẩm mới
     * - sản phẩm nổi bật / theo danh mục
     * - bài viết mới nhất
     * - settings public
     */
    public function index(): JsonResponse
    {
        try {
            $data = $this->homeService->index();

            return response()->json([
                'success' => true,
                'message' => 'Lấy dữ liệu trang chủ thành công.',
                'data' => $data,
            ]);
        } catch (Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Không thể lấy dữ liệu trang chủ.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}