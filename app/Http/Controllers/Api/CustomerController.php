<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCustomerRequest;
use App\Http\Requests\UpdateCustomerRequest;
use Illuminate\Http\JsonResponse;

class CustomerController extends Controller
{
    public function store(StoreCustomerRequest $request): JsonResponse
    {
        $validated = $request->validated();

        return response()->json([
            'message' => 'Du lieu hop le. Khach hang chua duoc luu vao database.',
            'data' => $validated,
        ], 201);
    }

    public function update(
        UpdateCustomerRequest $request,
        string $customer
    ): JsonResponse {
        $validated = $request->validated();

        return response()->json([
            'message' => 'Du lieu cap nhat hop le. Chua ghi vao database.',
            'customer_id' => $customer,
            'data' => $validated,
        ]);
    }
}
