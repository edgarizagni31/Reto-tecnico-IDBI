<?php

namespace App\Http\Controllers\Vouchers\Voucher;
use App\Http\Resources\Vouchers\VoucherResource;
use App\Models\Voucher;
use App\Services\VoucherService;
use Illuminate\Http\Response;
use Illuminate\Http\Request;
use Log;


class GetVoucherHandler
{
    public function __construct(private readonly VoucherService $voucherService)
    {
    }

    public function __invoke(Request $request): Response
    {
        $vouchers = $this->voucherService->getVoucher([
            'serie' => $request->query('serie'),
            'number' => $request->query('number'),
            'startDate' => $request->query('start_date'),
            'endDate' => $request->query('end_date'),
        ]);


        return response([
            'data' => VoucherResource::collection($vouchers),
            'message' => 'list vouchers'
        ], 200);
    }
}
