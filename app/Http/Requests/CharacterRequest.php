<?php

namespace App\Http\Requests;

use App\Models\Content;
use Illuminate\Contracts\Validation\Validator;
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
            'content_ids' => ['required', 'array', 'min:1'],
            'content_ids.*' => ['integer', Rule::exists('contents', 'id')],
        ];
    }

    /**
     * Server-side cross-category validation:
     *   - every selected Content must exist
     *   - every selected Content must belong to the chosen Category
     * A Character MUST belong to at least one specific Content record.
     *
     * Strict comparison (===) is unreliable because form-submitted
     * checkbox values come in as strings while DB ids are integers.
     * We normalise both sides to integers and rebuild a position map
     * so the error key lines up with the field that actually failed.
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $categoryId = (int) $this->input('category_id');
            $rawContentIds = $this->input('content_ids', []);

            if (! $categoryId || empty($rawContentIds)) {
                return;
            }

            $contentIds = array_map('intval', (array) $rawContentIds);
            $positionMap = [];
            foreach ($contentIds as $position => $id) {
                $positionMap[$id] = $position;
            }

            $invalid = Content::whereIn('id', $contentIds)
                ->where('category_id', '!=', $categoryId)
                ->pluck('id')
                ->map(fn ($id) => (int) $id)
                ->all();

            foreach ($invalid as $id) {
                $key = $positionMap[$id] ?? null;
                if ($key === null) {
                    continue;
                }
                $validator->errors()->add(
                    'content_ids.'.$key,
                    'Selected content does not belong to the chosen category.'
                );
            }
        });
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Please enter a character name.',
            'slug.unique' => 'This slug is already taken.',
            'category_id.required' => 'Please select a category.',
            'image_media_id.exists' => 'The selected image does not exist.',
            'content_ids.required' => 'Please select at least one related content.',
            'content_ids.min' => 'A character must belong to at least one content.',
            'content_ids.*.exists' => 'One or more selected contents do not exist.',
        ];
    }
}