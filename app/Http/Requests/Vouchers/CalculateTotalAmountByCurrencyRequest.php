<?php

namespace App\Http\Requests\Vouchers;

use Illuminate\Foundation\Http\FormRequest;

class CalculateTotalAmountByCurrencyRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'currency' => 'nullable|in:PEN,USD'
        ];
    }

    public function withValidator($validator) {
        $validator->after(function () {
            if (!$this->has('currency')) {
                $this->merge([
                    'currency' => 'PEN'
                ]);
            }
        });
    }
}
