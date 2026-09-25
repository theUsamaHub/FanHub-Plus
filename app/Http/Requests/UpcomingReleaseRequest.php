<?php

namespace App\Http\Requests;

use App\Models\UpcomingRelease;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpcomingReleaseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $releaseId = $this->route('upcoming_release')?->id;

        return [
            'title' => ['required', 'string', 'max:200'],
            'slug' => [
                'nullable',
                'string',
                'max:220',
                'alpha_dash',
                Rule::unique('upcoming_releases', 'slug')->ignore($releaseId),
            ],
            'category_id' => ['nullable', 'integer', Rule::exists('categories', 'id')],
            'kind' => ['required', Rule::in(UpcomingRelease::KINDS)],
            'release_date' => ['nullable', 'date'],
            'release_label' => ['nullable', 'string', 'max:60'],
            'description' => ['nullable', 'string', 'max:2000'],
            'image_media_id' => ['nullable', 'integer', Rule::exists('media', 'id')],
            'is_published' => ['nullable', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:9999'],
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'Please enter a title.',
            'slug.unique' => 'This slug is already taken.',
            'kind.in' => 'Kind must be anime, event, movie, series, game, or merchandise.',
            'image_media_id.exists' => 'The selected image media does not exist.',
        ];
    }
}
