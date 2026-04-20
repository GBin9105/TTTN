<?php

namespace App\Http\Controllers\Api\Client;

use App\Http\Controllers\Controller;
use App\Services\PostService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Throwable;

class PostController extends Controller
{
    public function __construct(
        protected PostService $postService
    ) {}

    /**
     * Danh sách bài viết:
     * - lọc theo chủ đề
     * - tìm kiếm
     * - phân trang
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $data = $this->postService->index($request->all());

            return response()->json([
                'success' => true,
                'message' => 'Lấy danh sách bài viết thành công.',
                'data' => $data,
            ]);
        } catch (Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Không thể lấy danh sách bài viết.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Chi tiết bài viết + bài viết liên quan/random
     */
    public function show(string $slug): JsonResponse
    {
        try {
            $data = $this->postService->show($slug);

            if (! $data) {
                return response()->json([
                    'success' => false,
                    'message' => 'Không tìm thấy bài viết.',
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Lấy chi tiết bài viết thành công.',
                'data' => $data,
            ]);
        } catch (Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Không thể lấy chi tiết bài viết.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}