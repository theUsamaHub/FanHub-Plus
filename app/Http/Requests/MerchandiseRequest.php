<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class MerchandiseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $itemId = $this->route('merchandise')?->id ?? $this->route('item')?->id;

        return [
            'name' => ['required', 'string', 'max:200'],
            'slug' => [
                'nullable',
                'string',
                'max:220',
                'alpha_dash',
                Rule::unique('merchandise_items', 'slug')->ignore($itemId),
            ],
            'category_id' => ['required', 'integer', Rule::exists('categories', 'id')],
            'description' => ['nullable', 'string'],
            'image_media_id' => ['nullable', 'integer', Rule::exists('media', 'id')],
            'tag' => ['required', Rule::in(['limited_edition', 'pre_order', 'collectible', 'standard'])],
            'is_upcoming' => ['nullable', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Please enter a merchandise name.',
            'slug.unique' => 'This slug is already taken.',
            'category_id.required' => 'Please select a category.',
            'tag.in' => 'Tag must be limited edition, pre-order, collectible, or standard.',
            'image_media_id.exists' => 'The selected image does not exist.',
        ];
    }
}
