<?php

use App\Http\Controllers\Vouchers\GetVouchersHandler;
use App\Http\Controllers\Vouchers\StoreVouchersHandler;
use App\Http\Controllers\Vouchers\Voucher\DeleteVoucherHandler;
use App\Http\Controllers\Vouchers\Voucher\GetVoucherHandler;
use App\Http\Controllers\Vouchers\CalculateTotalAmountByCurrencyHandler;

use Illuminate\Support\Facades\Route;

Route::prefix('vouchers')->group(
    function () {
        Route::get('/', GetVouchersHandler::class);
        Route::get('/calculate-amount', CalculateTotalAmountByCurrencyHandler::class);
        Route::get('/voucher', GetVoucherHandler::class);
        Route::post('/', StoreVouchersHandler::class);
        Route::delete('/{voucherId}', DeleteVoucherHandler::class);
    }
);
