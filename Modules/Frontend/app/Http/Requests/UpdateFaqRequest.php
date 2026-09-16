<?php

namespace Modules\Frontend\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateFaqRequest extends FormRequest
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
            'question' => ['required', 'string', 'max:255'],
            'answer' => ['required', 'string', 'max:65535'],
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
            'question.required' => 'The question is required.',
            'question.string' => 'The question must be a valid text.',
            'question.max' => 'The question may not be greater than 255 characters.',

            'answer.required' => 'The answer is required.',
            'answer.string' => 'The answer must be a valid text.',
            'answer.max' => 'The answer is too long.',

            'is_active.boolean' => 'The status must be either active or inactive.',
        ];
    }
}
