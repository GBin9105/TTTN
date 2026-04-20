<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\UpdateProfileRequest;
use App\Services\AuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Throwable;

class ProfileController extends Controller
{
    public function __construct(
        protected AuthService $authService
    ) {}

    /**
     * Lấy hồ sơ thành viên hiện tại
     */
    public function show(Request $request): JsonResponse
    {
        try {
            $user = $request->user();

            if (! $user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Chưa đăng nhập.',
                ], 401);
            }

            return response()->json([
                'success' => true,
                'message' => 'Lấy thông tin hồ sơ thành công.',
                'data' => [
                    'user' => $user,
                ],
            ]);
        } catch (Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Không thể lấy thông tin hồ sơ.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Cập nhật hồ sơ thành viên
     */
    public function update(UpdateProfileRequest $request): JsonResponse
    {
        try {
            $user = $request->user();

            if (! $user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Chưa đăng nhập.',
                ], 401);
            }

            $updatedUser = $this->authService->updateProfile(
                $user,
                $request->validated()
            );

            return response()->json([
                'success' => true,
                'message' => 'Cập nhật hồ sơ thành công.',
                'data' => [
                    'user' => $updatedUser,
                ],
            ]);
        } catch (Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Cập nhật hồ sơ thất bại.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}