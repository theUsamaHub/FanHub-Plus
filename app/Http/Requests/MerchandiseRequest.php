<?php

namespace App\Http\Requests;

use App\Models\CharacterProfile;
use App\Models\Content;
use Illuminate\Contracts\Validation\Validator;
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
            'content_id' => ['required', 'integer', Rule::exists('contents', 'id')],
            'character_id' => ['nullable', 'integer', Rule::exists('character_profiles', 'id')],
            'description' => ['nullable', 'string'],
            'image_media_id' => ['nullable', 'integer', Rule::exists('media', 'id')],
            'tag' => ['required', Rule::in(['limited_edition', 'pre_order', 'collectible', 'standard'])],
            'is_upcoming' => ['nullable', 'boolean'],
        ];
    }

    /**
     * Server-side cross-relation validation:
     *   - selected Content must belong to selected Category
     *   - if a Character is selected, it must belong to the selected
     *     Content through character_contents (same Content the merch
     *     belongs to)
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $categoryId = $this->input('category_id');
            $contentId = $this->input('content_id');
            $characterId = $this->input('character_id');
            if (! $categoryId || ! $contentId) {
                return;
            }
            $content = Content::find($contentId);
            if (! $content) {
                $validator->errors()->add('content_id', 'The selected content does not exist.');
                return;
            }
            if ((int) $content->category_id !== (int) $categoryId) {
                $validator->errors()->add('content_id', 'Selected content does not belong to the chosen category.');
            }
            if (! $characterId) {
                return;
            }
            $valid = CharacterProfile::whereKey($characterId)
                ->whereHas('contents', fn ($q) => $q->whereKey($contentId))
                ->exists();
            if (! $valid) {
                $validator->errors()->add('character_id', 'Selected character is not related to the chosen content.');
            }
        });
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Please enter a merchandise name.',
            'slug.unique' => 'This slug is already taken.',
            'category_id.required' => 'Please select a category.',
            'content_id.required' => 'Please select a content. Merchandise must belong to a content.',
            'tag.in' => 'Tag must be limited edition, pre-order, collectible, or standard.',
            'image_media_id.exists' => 'The selected image does not exist.',
        ];
    }
}