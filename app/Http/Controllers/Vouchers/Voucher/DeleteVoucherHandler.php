<?php

namespace App\Http\Controllers\Vouchers\Voucher;
use App\Http\Resources\Vouchers\VoucherResource;
use App\Services\VoucherService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;


class DeleteVoucherHandler
{
    public function __construct(private readonly VoucherService $voucherService)
    {
    }

    public function __invoke(Request $request): Response
    {
        try {
            $voucher = $this->voucherService->deleteVoucher($request->route('voucherId'));

            return response([
                'message' => "voucher $voucher->id eliminado correctamente",
                'data' => new VoucherResource($voucher),
            ], 201);
        } catch (\Exception $exception) {
            return response([
                'message' => $exception->getMessage(),
            ], 400);
        }
    }
}
