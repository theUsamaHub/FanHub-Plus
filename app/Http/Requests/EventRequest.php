<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class EventRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:200'],
            'description' => ['nullable', 'string'],
            'short_description' => ['nullable', 'string', 'max:400'],
            'event_type' => ['nullable', Rule::in(array_keys(config('events.types')))],
            'is_featured' => ['sometimes', 'boolean'],
            'popularity_score' => ['sometimes', 'integer', 'min:0', 'max:1000000'],
            'gallery_present' => ['sometimes', 'boolean'],
            'gallery_media_ids' => ['nullable', 'array', 'max:12'],
            'gallery_media_ids.*' => ['integer', 'distinct', Rule::exists('media', 'id')->where('media_type', 'image')],
            'category_id' => ['nullable', 'integer', Rule::exists('categories', 'id')],
            'city' => ['required', 'string', 'max:100'],
            'venue' => ['nullable', 'string', 'max:255'],
            'address' => ['nullable', 'string', 'max:255'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'start_at' => ['required', 'date'],
            'end_at' => ['nullable', 'date', 'after_or_equal:start_at'],
            'ticket_url' => ['nullable', 'url:http,https', 'max:500'],
            'cover_media_id' => ['nullable', 'integer', Rule::exists('media', 'id')],
            'status' => ['required', Rule::in(['draft', 'published', 'cancelled'])],
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'Please enter an event title.',
            'city.required' => 'Please enter a city.',
            'start_at.required' => 'Please enter a start date and time.',
            'end_at.after_or_equal' => 'End date and time cannot be before the start.',
            'latitude.between' => 'Latitude must be between -90 and 90.',
            'longitude.between' => 'Longitude must be between -180 and 180.',
            'ticket_url.url' => 'Ticket URL must be a valid URL.',
            'status.in' => 'Status must be draft, published, or cancelled.',
            'cover_media_id.exists' => 'The selected cover media does not exist.',
        ];
    }
}
