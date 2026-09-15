<?php

namespace Modules\Frontend\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSliderImageRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * Image is optional on update — when not re-uploaded, the existing image is kept.
     */
    public function rules(): array
    {
        return [
            'image'      => [
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:2048',
            ],
            'is_active'  => 'nullable|boolean',
        ];
    }

    /**
     * Get custom messages for validation errors.
     */
    public function messages(): array
    {
        return [
            'image.image'     => 'The uploaded file must be a valid image.',
            'image.mimes'     => 'Slider image must be a file of type: jpg, jpeg, png, webp.',
            'image.max'       => 'Slider image may not be greater than 2MB.',
        ];
    }
}
