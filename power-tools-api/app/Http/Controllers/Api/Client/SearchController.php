<?php

namespace App\Http\Controllers\Api\Client;

use App\Http\Controllers\Controller;
use App\Services\SearchService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Throwable;

class SearchController extends Controller
{
    public function __construct(
        protected SearchService $searchService
    ) {}

    /**
     * Tìm kiếm tổng hợp public:
     * - products
     * - categories
     * - posts
     * - pages
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $data = $this->searchService->index($request->all());

            return response()->json([
                'success' => true,
                'message' => 'Tìm kiếm thành công.',
                'data' => $data,
            ]);
        } catch (Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Tìm kiếm thất bại.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}