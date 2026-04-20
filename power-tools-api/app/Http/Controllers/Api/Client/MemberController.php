<?php

namespace App\Http\Controllers\Api\Client;

use App\Http\Controllers\Controller;
use App\Services\MemberService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Throwable;

class MemberController extends Controller
{
    public function __construct(
        protected MemberService $memberService
    ) {}

    /**
     * Tổng quan khu vực thành viên
     * - thông tin user
     * - thống kê đơn hàng cơ bản
     * - dữ liệu dashboard member
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

            $data = $this->memberService->index($user);

            return response()->json([
                'success' => true,
                'message' => 'Lấy thông tin thành viên thành công.',
                'data' => $data,
            ]);
        } catch (Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Không thể lấy thông tin thành viên.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}