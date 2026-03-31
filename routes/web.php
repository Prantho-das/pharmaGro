<?php

use App\Http\Controllers\ReceiptController;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')->name('home');

// Receipt PDF (for printing)
Route::get('receipt/{invoice}', [ReceiptController::class, 'show'])
    ->name('receipt.show')
    ->middleware('auth');
