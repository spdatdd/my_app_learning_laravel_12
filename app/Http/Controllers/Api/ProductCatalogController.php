<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

class ProductCatalogController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json([
            'data' => [
                ['id' => 1, 'name' => 'Keyboard', 'price' => 350000],
                ['id' => 2, 'name' => 'Mouse', 'price' => 150000],
                ['id' => 3, 'name' => 'Monitor', 'price' => 2500000],
            ],
        ]);
    }
}
