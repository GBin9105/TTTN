<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\ChangePasswordRequest;
use App\Services\AuthService;
use Illuminate\Http\JsonResponse;
use Throwable;

class PasswordController extends Controller
{
    public function __construct(
        protected AuthService $authService
    ) {}

    /**
     * Đổi mật khẩu tài khoản hiện tại
     */
    public function update(ChangePasswordRequest $request): JsonResponse
    {
        try {
            $user = $request->user();

            if (! $user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Chưa đăng nhập.',
                ], 401);
            }

            $updatedUser = $this->authService->changePassword(
                $user,
                $request->validated()
            );

            return response()->json([
                'success' => true,
                'message' => 'Đổi mật khẩu thành công.',
                'data' => [
                    'user' => $updatedUser,
                ],
            ]);
        } catch (Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Đổi mật khẩu thất bại.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}