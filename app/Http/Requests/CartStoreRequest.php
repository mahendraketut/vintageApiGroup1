<?php

namespace App\Http\Requests;

use App\Rules\CheckProductQuantity;
use App\Rules\MaxUniqueProductsInCart;
use Illuminate\Foundation\Http\FormRequest;
// use app\Rules\NoNegativeValue;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;

class CartStoreRequest extends FormRequest
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
            'product_id' => ['required', 'min:1'],
            'quantity' => ['required', 'integer', 'min:1', new CheckProductQuantity($_REQUEST['product_id'])]
        ];
    }

    public function messages()
    {
        return [
            'product_id.required' => "Must select at least one product to add to cart",
            'product_id.min' => "Product id must be greater than 0",
            'quantity.required' => "Quantity can not be null",
            'quantity.integer' => "Quantity must be integer value",
            'quantity.min' => "Quantity must be greater than 0",
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
