<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreAttributeRequest;
use App\Http\Requests\Admin\SyncProductAttributesRequest;
use App\Http\Requests\Admin\UpdateAttributeRequest;
use App\Http\Resources\AttributeResource;
use App\Http\Resources\ProductAttributeValueResource;
use App\Models\Attribute;
use App\Models\Product;
use App\Services\AttributeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Throwable;

class AttributeController extends Controller
{
    public function __construct(
        protected AttributeService $attributeService
    ) {}

    /**
     * Danh sách thuộc tính
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $attributes = $this->attributeService->paginate($request->all());

            return response()->json([
                'success' => true,
                'message' => 'Lấy danh sách thuộc tính thành công.',
                'data' => AttributeResource::collection($attributes->items()),
                'meta' => [
                    'current_page' => $attributes->currentPage(),
                    'last_page' => $attributes->lastPage(),
                    'per_page' => $attributes->perPage(),
                    'total' => $attributes->total(),
                    'from' => $attributes->firstItem(),
                    'to' => $attributes->lastItem(),
                ],
            ]);
        } catch (Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lấy danh sách thuộc tính thất bại.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Tạo thuộc tính
     */
    public function store(StoreAttributeRequest $request): JsonResponse
    {
        try {
            $attribute = $this->attributeService->create($request->validated());

            return response()->json([
                'success' => true,
                'message' => 'Tạo thuộc tính thành công.',
                'data' => new AttributeResource($attribute),
            ], 201);
        } catch (Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Tạo thuộc tính thất bại.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Chi tiết thuộc tính
     */
    public function show(Attribute $attribute): JsonResponse
    {
        try {
            return response()->json([
                'success' => true,
                'message' => 'Lấy chi tiết thuộc tính thành công.',
                'data' => new AttributeResource($attribute),
            ]);
        } catch (Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lấy chi tiết thuộc tính thất bại.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Cập nhật thuộc tính
     */
    public function update(UpdateAttributeRequest $request, Attribute $attribute): JsonResponse
    {
        try {
            $attribute = $this->attributeService->update($attribute, $request->validated());

            return response()->json([
                'success' => true,
                'message' => 'Cập nhật thuộc tính thành công.',
                'data' => new AttributeResource($attribute),
            ]);
        } catch (Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Cập nhật thuộc tính thất bại.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Xóa thuộc tính
     */
    public function destroy(Attribute $attribute): JsonResponse
    {
        try {
            $this->attributeService->delete($attribute);

            return response()->json([
                'success' => true,
                'message' => 'Xóa thuộc tính thành công.',
            ]);
        } catch (Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Xóa thuộc tính thất bại.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Gán / đồng bộ thuộc tính cho sản phẩm
     */
    public function syncProductAttributes(
        SyncProductAttributesRequest $request,
        Product $product
    ): JsonResponse {
        try {
            $product = $this->attributeService->syncProductAttributes(
                $product,
                $request->validated()['attributes']
            );

            return response()->json([
                'success' => true,
                'message' => 'Cập nhật thuộc tính cho sản phẩm thành công.',
                'data' => ProductAttributeValueResource::collection($product->attributeValues),
            ]);
        } catch (Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Cập nhật thuộc tính cho sản phẩm thất bại.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Lấy danh sách thuộc tính của 1 sản phẩm
     */
    public function productAttributes(Product $product): JsonResponse
    {
        try {
            $product = $this->attributeService->getProductAttributes($product);

            return response()->json([
                'success' => true,
                'message' => 'Lấy thuộc tính sản phẩm thành công.',
                'data' => ProductAttributeValueResource::collection($product->attributeValues),
            ]);
        } catch (Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lấy thuộc tính sản phẩm thất bại.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}