<?php

use App\Http\Controllers\Api\HealthController;
use App\Http\Controllers\Api\ProductCatalogController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function (): void {
    Route::get('/health', [HealthController::class, 'index'])->name('api.v1.health');
    Route::get('/products', [ProductCatalogController::class, 'index'])->name('api.v1.products.index');
});

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');
