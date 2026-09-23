<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CharacterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $characterId = $this->route('character')?->id;

        return [
            'name' => ['required', 'string', 'max:150'],
            'slug' => [
                'nullable',
                'string',
                'max:180',
                'alpha_dash',
                Rule::unique('character_profiles', 'slug')->ignore($characterId),
            ],
            'category_id' => ['required', 'integer', Rule::exists('categories', 'id')],
            'bio' => ['nullable', 'string'],
            'image_media_id' => ['nullable', 'integer', Rule::exists('media', 'id')],
            'content_ids' => ['nullable', 'array'],
            'content_ids.*' => ['integer', Rule::exists('contents', 'id')],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Please enter a character name.',
            'slug.unique' => 'This slug is already taken.',
            'category_id.required' => 'Please select a category.',
            'image_media_id.exists' => 'The selected image does not exist.',
            'content_ids.*.exists' => 'One or more selected contents do not exist.',
        ];
    }
}
