<?php

use App\Http\Controllers\PaymentController;
use Illuminate\Support\Facades\Route;

Route::get('', [PaymentController::class, 'index'])->name('payment.index');
Route::get('payment', [PaymentController::class, 'store'])->name('payment.store');
Route::get('payment/{orderNo}', [PaymentController::class, 'status'])->name('payment.status');
// redirect urls for hbl
Route::any('success', [PaymentController::class, 'success'])->name('payment.confirmation');
Route::any('failed', [PaymentController::class, 'failed'])->name('payment.failed');
Route::any('cancel', [PaymentController::class, 'cancel'])->name('payment.cancel');
Route::any('backend', [PaymentController::class, 'backend'])->name('payment.backend');

Route::get('delete/{orderNo}', [PaymentController::class, 'delete'])->name('payment.delete');
Route::get('refund', [PaymentController::class, 'refund'])->name('payment.refund');
Route::get('void', [PaymentController::class, 'void'])->name('payment.void');
Route::get('settlement', [PaymentController::class, 'settlement'])->name('payment.settlement');
Route::get('inquiry', [PaymentController::class, 'inquiry'])->name('payment.inquiry');
