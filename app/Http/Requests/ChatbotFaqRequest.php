<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ChatbotFaqRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'category_id' => ['nullable', 'integer', Rule::exists('categories', 'id')],
            'question' => ['required', 'string', 'max:500'],
            'answer' => ['required', 'string', 'max:50000'],
        ];
    }

    public function messages(): array
    {
        return [
            'category_id.exists' => 'The selected category does not exist.',
            'question.required' => 'Please enter a question.',
            'question.max' => 'The question may not be greater than 500 characters.',
            'answer.required' => 'Please enter an answer.',
            'answer.max' => 'The answer may not be greater than 50,000 characters.',
        ];
    }
}
