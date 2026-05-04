<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTransactionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'type'             => ['required', 'in:entrada,saida'],
            'amount'           => ['required', 'numeric', 'min:0.01'],
            'description'      => ['required', 'string', 'max:255'],
            'payment_method'   => ['nullable', 'string', 'max:50'],
            'receipt'          => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:10240'],
            'competencia_date' => ['nullable', 'date'],
            'notes'            => ['nullable', 'string', 'max:2000'],
        ];
    }

    public function messages(): array
    {
        return [
            'amount.min'   => 'O valor deve ser maior que zero.',
            'receipt.mimes'=> 'O comprovante deve ser PNG, JPG ou PDF.',
            'receipt.max'  => 'O comprovante não pode ultrapassar 10 MB.',
        ];
    }
}
