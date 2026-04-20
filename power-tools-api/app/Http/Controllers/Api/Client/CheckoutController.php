<?php

namespace App\Http\Controllers\Api\Client;

use App\Http\Controllers\Controller;
use App\Http\Requests\Client\CheckoutRequest;
use App\Services\CheckoutService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Throwable;

class CheckoutController extends Controller
{
    public function __construct(
        protected CheckoutService $checkoutService
    ) {}

    /**
     * Thanh toán / tạo đơn hàng
     */
    public function store(CheckoutRequest $request): JsonResponse
    {
        try {
            $sessionId = $this->getSessionId($request);

            $order = $this->checkoutService->store(
                $request->validated(),
                $request->user(),
                $sessionId
            );

            return response()->json([
                'success' => true,
                'message' => 'Đặt hàng thành công.',
                'data' => [
                    'order' => $order,
                ],
            ], 201);
        } catch (Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Đặt hàng thất bại.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    protected function getSessionId(Request $request): string
    {
        if (! $request->hasSession()) {
            $request->setLaravelSession(app('session')->driver());
        }

        if (! $request->session()->isStarted()) {
            $request->session()->start();
        }

        return $request->session()->getId();
    }
}