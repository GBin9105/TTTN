<?php

namespace App\Http\Controllers\Api\Client;

use App\Http\Controllers\Controller;
use App\Http\Requests\Client\StoreContactRequest;
use App\Services\ContactService;
use Illuminate\Http\JsonResponse;
use Throwable;

class ContactController extends Controller
{
    public function __construct(
        protected ContactService $contactService
    ) {}

    /**
     * Gửi liên hệ từ form contact
     */
    public function store(StoreContactRequest $request): JsonResponse
    {
        try {
            $contact = $this->contactService->store($request->validated());

            return response()->json([
                'success' => true,
                'message' => 'Gửi liên hệ thành công.',
                'data' => [
                    'contact' => $contact,
                ],
            ], 201);    
        } catch (Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gửi liên hệ thất bại.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}