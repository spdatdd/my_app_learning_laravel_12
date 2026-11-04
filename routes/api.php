<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\CustomerController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\OrderController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/customers', [
        CustomerController::class,
        'store'
    ]);

    Route::get('/products/{id}', [
        ProductController::class,
        'show'
    ])->whereNumber('id');

    Route::post('/orders/{id}/confirm', [
        OrderController::class,
        'confirm'
    ])->whereNumber('id');
});

