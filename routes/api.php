<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ProductApiController;
use App\Http\Controllers\Api\ReportApiController;
use App\Http\Controllers\Api\SaleApiController;
use Illuminate\Support\Facades\Route;

// Public authentication routes
Route::post('login', [AuthController::class, 'login']);
Route::middleware('auth:sanctum')->group(function () {
    Route::post('logout', [AuthController::class, 'logout']);

    // Product lookup (still public but token can be used if desired)
    Route::get('products/{sku}', [ProductApiController::class, 'show']);
    Route::get('products/barcode/{barcode}', [ProductApiController::class, 'show']);

    // Sales
    Route::post('sales', [SaleApiController::class, 'store']);
    Route::get('sales/{invoiceNo}', [SaleApiController::class, 'show']);
    Route::get('sales/{invoiceNo}/receipt', [SaleApiController::class, 'receipt']);

    // Reports
    Route::get('reports/summary', [ReportApiController::class, 'summary']);
    Route::get('reports/expiring', [ReportApiController::class, 'expiring']);
});
