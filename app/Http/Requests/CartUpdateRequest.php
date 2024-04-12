<?php

namespace App\Http\Requests;

use App\Rules\CheckProductQuantity;
use App\Rules\NoNegativeValue;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;

class CartUpdateRequest extends FormRequest
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
            'product_id' => ['required', 'integer'],
            'quantity' => [
                'required',
                'integer',
                new NoNegativeValue,
                new CheckProductQuantity($_REQUEST['product_id'])]
        ];
    }

    public function messages()
    {
        return [
            'product_id.required' => "Product id is required",
            'product_id.integer' => "Product id must be integer value",
            'quantity.required' => "Quantity can not be null",
            'quantity.integer' => "Quantity must be integer value"
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
