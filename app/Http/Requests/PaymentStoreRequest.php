<?php

namespace App\Http\Requests;

use App\Rules\NoNegativeValue;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;

class PaymentStoreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'method_id' => ['required', 'gte:1'],
            'order_id' => ['required', 'gte:1'],
            'amount' => ['required', 'decimal:2', new NoNegativeValue]
        ];
    }

    public function messages()
    {
        return [
            'method_id.required' => "Must select one payment method",
            'method_id.min' => "Select only available payment methods",
            'order_id.required' => "Must select an order to pay",
            'order_id.min' => "select one order to complete payment",
            'amount.required' => "amount to pay must be equals to or greater than 0",
            'amount.decimal' => "amount of payment must be in decimal value"
        ];
    }

    public function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'success' => false,
            'message' => $validator->errors()->first()
        ], 422));
    }
}
