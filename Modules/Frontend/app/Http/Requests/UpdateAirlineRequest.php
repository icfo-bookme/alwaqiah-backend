<?php

namespace Modules\Frontend\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAirlineRequest extends FormRequest
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
     */
    public function rules(): array
    {
        return [
            'name'      => 'required|string|max:255',
            'code'      => 'nullable|string|max:10',
            'logo'      => 'nullable|image|mimes:jpg,jpeg,png,webp,svg|max:2048',
            'is_active' => 'nullable|boolean',
        ];
    }

    /**
     * Get custom messages for validation errors.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Airline name is required.',
            'name.string'   => 'Airline name must be a valid text.',
            'name.max'      => 'Airline name may not be greater than 255 characters.',

            'code.string'   => 'Airline code must be a valid text.',
            'code.max'      => 'Airline code may not be greater than 10 characters.',

            'logo.image'    => 'Logo must be a valid image file.',
            'logo.mimes'    => 'Logo must be a JPG, JPEG, PNG, WEBP or SVG image.',
            'logo.max'      => 'Logo may not be larger than 2 MB.',

            'is_active.boolean' => 'The status must be either active or inactive.',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            // Prevent empty-string fields from storing "" instead of NULL.
            'code' => ($this->input('code') === '') ? null : $this->input('code'),
        ]);
    }
}
