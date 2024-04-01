<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreShippingAddressRequest extends FormRequest
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
            'full_name' => 'required|string|max:255',
            'phone' => 'required|string|max:255',
            'address' => 'required|string|max:255',
            'district' => 'required|string|max:255',
            'city' => 'required|string|max:255',
            'province' => 'required|string|max:255',
            'country' => 'required|string|max:255',
            'zip_code' => 'required|string|max:255',
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'full_name.required' => 'Full name is required',
            'full_name.string' => 'Full name must be a string',
            'full_name.max' => 'Full name must not be greater than 255 characters',
            'phone.required' => 'Phone number is required',
            'phone.string' => 'Phone number must be a string',
            'phone.max' => 'Phone number must not be greater than 255 characters',
            'address.required' => 'Address is required',
            'address.string' => 'Address must be a string',
            'address.max' => 'Address must not be greater than 255 characters',
            'district.required' => 'District is required',
            'district.string' => 'District must be a string',
            'district.max' => 'District must not be greater than 255 characters',
            'city.required' => 'City is required',
            'city.string' => 'City must be a string',
            'city.max' => 'City must not be greater than 255 characters',
            'province.required' => 'Province is required',
            'province.string' => 'Province must be a string',
            'province.max' => 'Province must not be greater than 255 characters',
            'country.required' => 'Country is required',
            'country.string' => 'Country must be a string',
            'country.max' => 'Country must not be greater than 255 characters',
            'zip_code.required' => 'Zip code is required',
            'zip_code.string' => 'Zip code must be a string',
            'zip_code.max' => 'Zip code must not be greater than 255 characters',
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array<string, string>
     */
    public function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'success' => false,
            'message' => $validator->errors()->first()
        ], 422));
    }
}
