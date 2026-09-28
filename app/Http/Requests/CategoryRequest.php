<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class CategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $name = $this->input('name');
        $slug = $this->input('slug');

        if (filled($name) && ! filled($slug)) {
            $this->merge(['slug' => Str::slug($name)]);
        }
    }

    public function rules(): array
    {
        $categoryId = $this->route('category')?->id;

        return [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('categories', 'name')->ignore($categoryId),
            ],
            'slug' => [
                'nullable',
                'string',
                'max:255',
                'min:1',
                'alpha_dash',
                Rule::unique('categories', 'slug')->ignore($categoryId),
            ],
            'description' => ['nullable', 'string', 'max:500'],
            'icon' => ['nullable', 'image', 'mimes:jpg,jpeg,png,gif,webp,svg', 'max:2048'],
            'icon_media_id' => ['nullable', 'integer', Rule::exists('media', 'id')],
            'remove_icon' => ['nullable', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Please enter a category name.',
            'name.max' => 'Category name cannot exceed 255 characters.',
            'name.unique' => 'A category with this name already exists.',
            'slug.unique' => 'A category with this slug already exists.',
            'description.max' => 'Description cannot exceed 500 characters.',
            'icon.image' => 'The file must be an image.',
            'icon.mimes' => 'The icon must be a JPG, PNG, GIF, WebP, or SVG file.',
            'icon.max' => 'The icon must not be larger than 2MB.',
            'icon_media_id.exists' => 'The selected media file does not exist.',
        ];
    }
}
