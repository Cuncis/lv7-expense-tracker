<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreEntryRequest extends FormRequest
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
            'type' => 'required|in:income,expense',
            'category' => 'required|string|max:100',
            'description' => 'nullable|string|max:255',
            'amount' => 'required|integer|min:0',
            'date' => 'required|date|before_or_equal:today',
            'note' => 'nullable|string|max:255',
        ];
    }

    public function messages(): array
    {
        return [
            'type.required' => 'The type field is required.',
            'type.in' => 'The type must be either income or expense.',
            'category.required' => 'The category field is required.',
            'category.string' => 'The category must be a string.',
            'category.max' => 'The category may not be greater than 100 characters.',
            'description.string' => 'The description must be a string.',
            'description.max' => 'The description may not be greater than 255 characters.',
            'amount.required' => 'The amount field is required.',
            'amount.integer' => 'The amount must be an integer.',
            'amount.min' => 'The amount must be at least 0.',
            'date.required' => 'The date field is required.',
            'date.date' => 'The date is not a valid date.',
            'date.before_or_equal' => 'The date may not be in the future.',
            'note.string' => 'The note must be a string.',
            'note.max' => 'The note may not be greater than 255 characters.',
        ];
    }
}
