<?php

use App\Http\Controllers\PaymentController;
use App\Http\Controllers\PaymentController1;
use Illuminate\Support\Facades\Route;

Route::get('', [PaymentController::class, 'index'])->name('payment.index');
Route::get('payment', [PaymentController::class, 'store'])->name('payment.store');
Route::get('payment/{orderNo}', [PaymentController::class, 'status'])->name('payment.status');
Route::any('success', [PaymentController::class, 'success'])->name('payment.success');
Route::any('failed', [PaymentController::class, 'failed'])->name('payment.failed');
Route::any('cancel', [PaymentController::class, 'cancel'])->name('payment.cancel');
Route::any('backend', [PaymentController::class, 'backend'])->name('payment.backend');


Route::get('payment1', [PaymentController1::class, 'store'])->name('payment1.store');
Route::any('success1', [PaymentController1::class, 'success'])->name('payment1.success');
Route::any('failed1', [PaymentController1::class, 'failed'])->name('payment1.failed');
Route::any('cancel1', [PaymentController1::class, 'cancel'])->name('payment1.cancel');
Route::any('backend1', [PaymentController1::class, 'backend'])->name('payment1.backend');
