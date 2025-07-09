<?php

use App\Http\Controllers\PaymentController;
use Illuminate\Support\Facades\Route;

Route::get('', [PaymentController::class, 'store'])->name('front.payment.get');
Route::any('/success', [PaymentController::class, 'success'])->name('front.payment.success');
Route::any('/failed', [PaymentController::class, 'failed'])->name('front.payment.failed');
Route::any('/cancel', [PaymentController::class, 'cancel'])->name('front.payment.cancel');
Route::any('/backend', [PaymentController::class, 'backend'])->name('front.payment.backend');
