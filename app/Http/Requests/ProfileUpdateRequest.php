<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProfileUpdateRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'lowercase',
                'email',
                'max:255',
                Rule::unique(User::class)->ignore($this->user()->id),
            ],
            'avatar' => ['nullable', 'image', 'mimes:jpg,jpeg,png,gif,webp', 'max:2048'],
        ];

        // Admin: basic + avatar upload only.
        if ($this->user()->hasRole('admin')) {
            $rules['remove_avatar'] = ['nullable', 'boolean'];

            return $rules;
        }

        // Registered-user profile extras.
        $rules += [
            'display_name' => ['nullable', 'string', 'max:100'],
            'bio' => ['nullable', 'string'],
            'avatar_media_id' => ['nullable', 'integer', Rule::exists('media', 'id')],
            'theme_preference' => ['nullable', Rule::in(['light', 'dark', 'system'])],
            'font_size_preference' => ['nullable', Rule::in(['small', 'medium', 'large'])],
        ];

        return $rules;
    }
}
