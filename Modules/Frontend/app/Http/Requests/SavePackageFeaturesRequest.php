<?php

namespace Modules\Frontend\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class SavePackageFeaturesRequest extends FormRequest
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
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'features' => ['nullable', 'array'],
            'features.*.id' => ['nullable', 'integer'],
            'features.*.icon' => ['nullable', 'string', 'max:100'],
            'features.*.title' => ['required', 'string', 'max:255'],
        ];
    }

    /**
     * Get the custom validation messages.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'features.array' => 'The features must be sent as a list.',
            'features.*.id.integer' => 'The feature id must be a valid number.',
            'features.*.icon.max' => 'The feature icon may not be greater than 100 characters.',
            'features.*.title.required' => 'Every feature row must have a title.',
            'features.*.title.string' => 'The feature title must be a valid text.',
            'features.*.title.max' => 'The feature title may not be greater than 255 characters.',
        ];
    }
}
