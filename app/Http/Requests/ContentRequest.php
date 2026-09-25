<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ContentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $contentId = $this->route('content')?->id;

        return [
            'title' => ['required', 'string', 'max:255'],
            'slug' => [
                'nullable',
                'string',
                'max:280',
                'alpha_dash',
                Rule::unique('contents', 'slug')->ignore($contentId),
            ],
            'category_id' => ['required', 'integer', Rule::exists('categories', 'id')],
            'type' => ['required', Rule::in(['article', 'video', 'audio', 'image'])],
            'excerpt' => ['nullable', 'string', 'max:500'],
            'body' => ['nullable', 'string'],
            'release_date' => ['nullable', 'date'],
            'release_label' => ['nullable', 'string', 'max:60'],
            'status' => ['required', Rule::in(['draft', 'pending_review', 'published', 'rejected'])],
            'is_featured' => ['nullable', 'boolean'],
            'published_at' => ['nullable', 'date'],
            'tags' => ['nullable', 'array'],
            'tags.*' => ['integer', Rule::exists('tags', 'id')],
            'cover_media_id' => ['nullable', 'integer', Rule::exists('media', 'id')],
            'gallery_media_ids' => ['nullable', 'array'],
            'gallery_media_ids.*' => ['integer', Rule::exists('media', 'id')],
            'trailer_media_id' => ['nullable', 'integer', Rule::exists('media', 'id')],
            'audio_clip_media_id' => ['nullable', 'integer', Rule::exists('media', 'id')],
            'attachment_media_ids' => ['nullable', 'array'],
            'attachment_media_ids.*' => ['integer', Rule::exists('media', 'id')],
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'Please enter a title.',
            'slug.unique' => 'This slug is already taken.',
            'category_id.required' => 'Please select a category.',
            'category_id.exists' => 'The selected category does not exist.',
            'type.in' => 'Type must be article, video, audio, or image.',
            'status.in' => 'Status must be draft, pending review, published, or rejected.',
            'cover_media_id.exists' => 'The selected cover media does not exist.',
            'tags.*.exists' => 'One or more selected tags do not exist.',
        ];
    }
}
