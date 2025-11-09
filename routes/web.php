<?php

use App\Http\Controllers\PaymentController;
use Illuminate\Support\Facades\Route;

Route::controller(PaymentController::class)->as('payment.')->group(function () {
    Route::get('',   'index')->name('index');
    Route::get('payment', 'store')->name('store');
    Route::get('payment/{orderNo}', 'status')->name('status');
    Route::any('success', 'success')->name('success');
    Route::any('failed', 'failed')->name('failed');
    Route::any('cancel', 'cancel')->name('cancel');
    Route::any('backend', 'backend')->name('backend');
    Route::get('delete/{orderNo}', 'delete')->name('delete');
    Route::get('refund', 'refund')->name('refund');
    Route::get('void', 'void')->name('void');
    Route::get('settlement', 'settlement')->name('settlement');
    Route::get('inquiry', 'inquiry')->name('inquiry');
});
