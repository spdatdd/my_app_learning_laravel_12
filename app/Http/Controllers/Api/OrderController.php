<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ConfirmOrderRequest;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    public function confirm(
        ConfirmOrderRequest $request,
        int $id
    ): JsonResponse {
        return DB::transaction(function () use ($id) {

            $order = Order::query()
                ->lockForUpdate()
                ->find($id);

            if (!$order) {
                return response()->json([
                    'message' => 'Không tìm thấy đơn hàng',
                    'code' => 'ORDER_NOT_FOUND',
                ], 404);
            }

            if ($order->status !== 'pending') {
                return response()->json([
                    'message' => 'Đơn hàng không thể xác nhận',
                    'code' => 'INVALID_ORDER_STATE',
                    'details' => [
                        'current_status' => $order->status,
                    ],
                ], 409);
            }

            $items = $order->items()
                ->orderBy('product_id')
                ->get();

            if ($items->isEmpty()) {
                return response()->json([
                    'message' => 'Đơn hàng chưa có sản phẩm',
                    'code' => 'EMPTY_ORDER',
                    'details' => [],
                ], 409);
            }

            $lockedProducts = [];

            foreach ($items as $item) {
                $product = Product::query()
                    ->whereKey($item->product_id)
                    ->lockForUpdate()
                    ->firstOrFail();

                if ($product->stock < $item->quantity) {
                    return response()->json([
                        'message' => 'Sản phẩm không đủ tồn kho',
                        'code' => 'INSUFFICIENT_STOCK',
                        'details' => [
                            'product_id' => $product->id,
                            'requested' => $item->quantity,
                            'available' => $product->stock,
                        ],
                    ], 409);
                }

                $lockedProducts[$product->id] = $product;
            }

            // Chỉ trừ tồn kho sau khi tất cả sản phẩm hợp lệ
            foreach ($items as $item) {
                $lockedProducts[$item->product_id]
                    ->decrement('stock', $item->quantity);
            }
          
            $order->update([
                'status' => 'confirmed',
            ]);

            return response()->json([
                'message' => 'Xác nhận đơn hàng thành công',
                'data' => [
                    'id' => $order->id,
                    'status' => $order->status,
                ],
            ], 200);

        }, 3);
    }
}
