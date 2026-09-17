<?php

namespace Modules\Frontend\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreSliderImageRequest extends FormRequest
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
     * Multiple images can be selected and uploaded at once — each file
     * becomes its own slider record.
     */
    public function rules(): array
    {
        return [
            'images'     => [
                'required',
                'array',
                'min:1',
            ],
            'images.*'   => [
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:20048',
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
            'images.required' => 'Please select at least one slider image.',
            'images.array'    => 'Slider images must be an array of files.',
            'images.min'      => 'Please select at least one slider image.',
            'images.*.image'  => 'Each uploaded file must be a valid image.',
            'images.*.mimes'  => 'Each slider image must be a file of type: jpg, jpeg, png, webp.',
            'images.*.max'    => 'Each slider image may not be greater than 20MB.',
        ];
    }
}
