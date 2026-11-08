<?php

use App\Http\Controllers\Api\CustomerController;
use App\Http\Controllers\Api\HealthController;
use App\Http\Controllers\Api\ProductCatalogController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function (): void {
    Route::get('/health', [HealthController::class, 'index'])
        ->name('api.v1.health');
    Route::get('/products', [ProductCatalogController::class, 'index'])
        ->name('api.v1.products.index');
    Route::post('/customers', [CustomerController::class, 'store'])
        ->name('api.v1.customers.store');
    Route::patch('/customers/{customer}', [CustomerController::class, 'update'])
        ->whereNumber('customer')
        ->name('api.v1.customers.update');
    Route::get('/debug-error', function (): never {
        throw new RuntimeException('Local error handling exercise.');
    })->name('api.v1.debug-error');
});

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');
