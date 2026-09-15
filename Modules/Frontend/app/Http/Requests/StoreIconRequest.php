<?php

namespace Modules\Frontend\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreIconRequest extends FormRequest
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
            'name'      => ['required', 'string', 'max:255'],
            'class'     => ['required', 'string', 'max:100', 'regex:/^fa[a-z-]* fa-[a-z0-9-]+$/', 'unique:icons,class'],
            'keywords'  => ['nullable', 'string', 'max:255'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required'  => 'The icon name is required.',
            'name.string'    => 'The icon name must be a valid text.',
            'name.max'       => 'The icon name may not be greater than 255 characters.',

            'class.required' => 'The icon class is required.',
            'class.string'   => 'The icon class must be a valid text.',
            'class.max'      => 'The icon class may not be greater than 100 characters.',
            'class.regex'    => 'The icon class must be a valid Font Awesome class (e.g. fa-solid fa-hotel).',
            'class.unique'   => 'This icon class already exists.',

            'keywords.string' => 'The keywords must be a valid text.',
            'keywords.max'    => 'The keywords may not be greater than 255 characters.',

            'is_active.boolean' => 'The status must be either active or inactive.',
        ];
    }
}