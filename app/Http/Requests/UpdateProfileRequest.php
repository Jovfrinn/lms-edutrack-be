<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProfileRequest extends FormRequest
{
    

    public function rules(): array
    {
        return [
            'name'          => ['required', 'string', 'max:255'],
            'phone'         => ['nullable', 'string', 'max:20'],
            'address'       => ['nullable', 'string', 'max:500'],
            'position'      => ['nullable', 'string', 'max:100'],
            'profile_photo' => ['nullable', 'image', 'mimes:jpeg,jpg,png,webp', 'max:5120'], // max 5MB
        ];
    }

    public function messages(): array
    {
        return [
            'name.required'          => 'Full name is required.',
            'name.max'               => 'Full name may not exceed 255 characters.',
            'phone.max'              => 'Phone number may not exceed 20 characters.',
            'address.max'            => 'Address may not exceed 500 characters.',
            'position.max'           => 'Position may not exceed 100 characters.',
            'profile_photo.image'    => 'Profile photo must be an image file.',
            'profile_photo.mimes'    => 'Profile photo must be a jpeg, jpg, png, or webp file.',
            'profile_photo.max'      => 'Profile photo size may not exceed 5MB.',
        ];
    }
}