<?php
namespace App\Http\Controllers\Vouchers;

use App\Services\VoucherService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
class CalculateTotalAmountByCurrencyHandler
{

    public function __construct()
    {
    }

    public function __invoke(Request $request): Response
    {
        try {
            $currency = $request->query('currency') ?? 'PEN';
            $total = VoucherService::calculateTotalAmountByCurrency($currency);
    
            return response(['message' => "el total acumulado por la divisa $currency", 'data' => intval($total)], 200);
        } catch (\Exception $exception) {
            return response(["message" => $exception->getMessage()], 400);
        }

        
    }
}
