<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Rules\NoNegativeValue;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Http;
use Illuminate\Contracts\Validation\Validator;

class ProductStoreRequest extends FormRequest
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
            'name' => ['required', 'max:255'],
            'description' => ['required', 'max:255'],
            'price' => ['required', 'decimal:2', new NoNegativeValue],
            'quantity' => ['required', 'integer', new NoNegativeValue],
            'condition' => ['required'],
            'discount' => ['decimal:2', new NoNegativeValue],
            'category_id' => ['required', new NoNegativeValue],
            'brand_id' => ['required', new NoNegativeValue],
            'size' => ['required'],
            'images.*' => ['image', 'mimes:png,jpg,jpeg', 'max:1024']
        ];
    }

    public function messages()
    {
        return [
            'name.required' => "Product name can't be null",
            'name.max' => "Product name must be less than 256 characters",
            'description.required' => "Product description can't be null",
            'description.max' => "Product description must be less than 256 characters",
            'price.required' => "Product price can't be null",
            'price.decimal' => "Product price must be in decimal value",
            'quantity.required' => "Product quantity can't be null",
            'quantity.integer' => "Product quantity must be in integer value",
            'condition.required' => "Please provide product's condition",
            'discount.decimal' => "Please input discount in decimal value",
            'category_id.required' => "Category id can't be null",
            'brand_id.required' => "Brand id can't be null",
            'size.required' => "Product size can't be null",
            'image.*.max' => "Image size should be less than 2mb",
            'image.*.mimes' => "Only png, jpg, and jpeg files are allowed"
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
