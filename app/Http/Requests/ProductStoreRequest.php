<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Rules\NoNegativeValue;

class ProductStoreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return false;
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
            'price' => ['required', 'decimal', new NoNegativeValue],
            'quantity' => ['required', 'integer', new NoNegativeValue],
            'condition' => ['required'],
            'discount' => ['decimal', new NoNegativeValue],
            'category_id' => ['required', new NoNegativeValue],
            'brand_id' => ['required', new NoNegativeValue],
            'size' => ['required']
        ];
    }

    public function messages()
    {
        return [
            'name.required' => "Product name can't be null",
            'name.max' => "Product name must be less than 256 characters",
            'description.required' => "Product description can't be null",
            'description.max' => "Product description must be less than 256 characters",
            'price.required' => "Product price can't be null"
        ];
    }
}
