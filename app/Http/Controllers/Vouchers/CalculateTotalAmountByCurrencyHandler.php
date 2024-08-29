<?php
namespace App\Http\Controllers\Vouchers;

use App\Http\Requests\Vouchers\CalculateTotalAmountByCurrencyRequest;
use App\Services\VoucherService;
use Illuminate\Http\Response;
class CalculateTotalAmountByCurrencyHandler
{

    public function __construct()
    {
    }

    public function __invoke(CalculateTotalAmountByCurrencyRequest $request): Response
    {
        try {
            $request->validated();
            $currency = $request->input('currency');
            $total = VoucherService::calculateTotalAmountByCurrency($currency);
    
            return response(['message' => "el total acumulado por la divisa $currency", 'data' => intval($total)], 200);
        } catch (\Exception $exception) {
            return response(["message" => $exception->getMessage()], 400);
        }

        
    }
}
