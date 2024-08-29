<?php

namespace App\Http\Requests\Vouchers;

use Illuminate\Foundation\Http\FormRequest;

class GetVoucherRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'serie' => 'sometimes|string|regex:/^F/',
            'number' => 'sometimes|string',
            'start_date' => 'sometimes|date_format:Y-m-d|before:end_date',
            'end_date' => 'sometimes|date_format:Y-m-d|after:start_date',
        ];
    }
}
