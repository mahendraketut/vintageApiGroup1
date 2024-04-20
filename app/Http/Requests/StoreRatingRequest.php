<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;

class StoreRatingRequest extends FormRequest
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
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string',
            'user_id' => 'required|exists:users,id',
            'product_id' => 'required|exists:products,id',
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     */
    public function messages(): array
    {
        return [
            'rating.required' => 'Rating is required',
            'rating.integer' => 'Rating must be an integer',
            'rating.min' => 'Rating must be at least 1',
            'rating.max' => 'Rating must not be greater than 5',
            'comment.string' => 'Comment must be a string',
            'user_id.required' => 'User ID is required',
            'user_id.exists' => 'User ID does not exist',
            'product_id.required' => 'Product ID is required',
            'product_id.exists' => 'Product ID does not exist',
        ];
    }

    /**
     * Handle a failed validation attempt.
     */
    public function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'success' => false,
            'message' => $validator->errors()->first()
        ], 422));
    }
}
