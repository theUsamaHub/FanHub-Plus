<?php

namespace App\Http\Requests;

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
            'email' => ['sometimes', 'required', 'string', 'lowercase', 'email', 'max:255', Rule::unique('users')->ignore($this->user()->id)],
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
            'bio' => ['nullable', 'string', 'max:2000'],
            'remove_avatar' => ['nullable', 'boolean'],
            'avatar_media_id' => ['nullable', 'integer', Rule::exists('media', 'id')->where('uploaded_by', $this->user()->id)->where('media_type', 'image')],
            'favorites_present' => ['nullable', 'boolean'],
            'favorites' => ['nullable', 'array', 'max:100'],
            'favorites.*' => ['integer', 'distinct', Rule::exists('categories', 'id')->whereNull('deleted_at')],
            'theme_preference' => ['nullable', Rule::in(['light', 'dark', 'system'])],
            'font_size_preference' => ['nullable', Rule::in(['small', 'medium', 'large'])],
        ];

        return $rules;
    }
}
