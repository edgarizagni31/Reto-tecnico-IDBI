<?php

namespace App\Http\Controllers\Vouchers\Voucher;
use App\Http\Requests\Vouchers\GetVoucherRequest;
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

    public function __invoke(GetVoucherRequest $request): Response
    {
        try {
            $vouchers = $this->voucherService->getVoucher([
                'serie' => $request->input('serie'),
                'number' => $request->input('number'),
                'start_date' => $request->input('start_date'),
                'end_date' => $request->input('end_date'),
            ]);


            return response([
                'data' => VoucherResource::collection($vouchers),
                'message' => 'list vouchers'
            ], 200);
        } catch (\Exception $exception) {
            return response([
                'message' => $exception->getMessage()
            ], 400);
        }

    }
}
