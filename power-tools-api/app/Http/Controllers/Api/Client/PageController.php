<?php

namespace App\Http\Controllers\Api\Client;

use App\Http\Controllers\Controller;
use App\Services\PageService;
use Illuminate\Http\JsonResponse;
use Throwable;

class PageController extends Controller
{
    public function __construct(
        protected PageService $pageService
    ) {}

    /**
     * Hiển thị chi tiết trang đơn theo slug
     */
    public function show(string $slug): JsonResponse
    {
        try {
            $data = $this->pageService->show($slug);

            if (! $data) {
                return response()->json([
                    'success' => false,
                    'message' => 'Không tìm thấy trang.',
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Lấy nội dung trang thành công.',
                'data' => $data,
            ]);
        } catch (Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Không thể lấy nội dung trang.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
