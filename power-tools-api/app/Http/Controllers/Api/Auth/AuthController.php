<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Services\AuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Throwable;

class AuthController extends Controller
{
    public function __construct(
        protected AuthService $authService
    ) {}

    /**
     * Đăng ký thành viên
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        try {
            $result = $this->authService->register($request->validated());

            return response()->json([
                'success' => true,
                'message' => 'Đăng ký tài khoản thành công.',
                'data' => [
                    'user' => $result['user'],
                    'token' => $result['token'],
                ],
            ], 201);
        } catch (ValidationException $e) {
            throw $e;
        } catch (Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Đăng ký tài khoản thất bại.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Đăng nhập
     */
    public function login(LoginRequest $request): JsonResponse
    {
        try {
            $result = $this->authService->login($request->validated());

            return response()->json([
                'success' => true,
                'message' => 'Đăng nhập thành công.',
                'data' => [
                    'user' => $result['user'],
                    'token' => $result['token'],
                ],
            ]);
        } catch (ValidationException $e) {
            throw $e;
        } catch (Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Đăng nhập thất bại.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Lấy thông tin người dùng hiện tại
     */
    public function me(Request $request): JsonResponse
    {
        try {
            $user = $this->authService->me();

            if (! $user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Chưa đăng nhập.',
                ], 401);
            }

            return response()->json([
                'success' => true,
                'message' => 'Lấy thông tin tài khoản thành công.',
                'data' => [
                    'user' => $user,
                ],
            ]);
        } catch (Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Không thể lấy thông tin tài khoản.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Đăng xuất
     */
    public function logout(Request $request): JsonResponse
    {
        try {
            if (! $request->user()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Bạn chưa đăng nhập.',
                ], 401);
            }

            $this->authService->logout();

            return response()->json([
                'success' => true,
                'message' => 'Đăng xuất thành công.',
            ]);
        } catch (Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Đăng xuất thất bại.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}