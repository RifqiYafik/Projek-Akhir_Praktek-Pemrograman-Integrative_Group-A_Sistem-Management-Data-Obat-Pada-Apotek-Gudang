<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MedicineController;
use App\Http\Controllers\WarehouseStockController;
use App\Http\Controllers\PharmacyStockController;
use App\Http\Controllers\TransactionController;


Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


Route::resource('medicines', MedicineController::class);
Route::get('medicines/{id}/status', [MedicineController::class, 'checkExpiryStatus']);

Route::resource('warehouse-stocks', WarehouseStockController::class);
Route::resource('pharmacy-stocks', PharmacyStockController::class);
Route::resource('transactions', TransactionController::class);
Route::get('/transactions', [TransactionController::class, 'index']);
