<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTransferRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'provider' => ['required', 'string', 'in:ORANGE_MONEY,MTN_MOMO'],
            'amount_cad' => ['required', 'numeric', 'min:0.01'],
            'fee_cad' => ['nullable', 'numeric', 'min:0'],
            'rate' => ['nullable', 'numeric', 'min:0'],
            'amount_xaf' => ['nullable', 'numeric', 'min:0'],
            'recipient_name' => ['required', 'string', 'max:120'],
            'recipient_phone' => ['required', 'string', 'max:30'],
        ];
    }

    public function messages(): array
    {
        return [
            'provider.in' => 'Provider must be ORANGE_MONEY or MTN_MOMO.',
            'amount_cad.min' => 'Amount must be greater than zero.',
            'recipient_name.max' => 'Recipient name must not exceed 120 characters.',
            'recipient_phone.max' => 'Recipient phone must not exceed 30 characters.',
        ];
    }
}
