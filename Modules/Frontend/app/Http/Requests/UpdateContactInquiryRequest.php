<?php

namespace Modules\Frontend\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Modules\Frontend\Models\ContactInquiry;

class UpdateContactInquiryRequest extends FormRequest
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
     * Used by the admin panel where the follow up status is changed after
     * contacting the visitor.
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:150'],
            'phone' => ['required', 'string', 'max:20', 'regex:/^\+?[0-9\s\-()]{6,20}$/'],
            'email' => ['nullable', 'email', 'max:150'],
            'message' => ['nullable', 'string', 'max:65535'],
            'status' => [
                'required',
                Rule::in([
                    ContactInquiry::STATUS_NEW,
                    ContactInquiry::STATUS_CONTACTED,
                    ContactInquiry::STATUS_RESOLVED,
                ]),
            ],
        ];
    }

    /**
     * Get custom messages for validation errors.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Name is required.',
            'name.string' => 'Name must be a valid text.',
            'name.max' => 'Name may not be greater than 150 characters.',

            'phone.required' => 'Phone number is required.',
            'phone.string' => 'Phone number must be a valid text.',
            'phone.max' => 'Phone number may not be greater than 20 characters.',
            'phone.regex' => 'Phone number may only contain digits, spaces, +, - and ( )',

            'email.email' => 'Please provide a valid email address.',
            'email.max' => 'Email may not be greater than 150 characters.',

            'message.string' => 'Message must be a valid text.',
            'message.max' => 'Message is too long.',

            'status.required' => 'Status is required.',
            'status.in' => 'Status must be one of: new, contacted or resolved.',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            // Prevent empty-string fields from storing "" instead of NULL.
            'email' => $this->normalize($this->input('email')),
            'message' => $this->normalize($this->input('message')),
        ]);
    }

    /**
     * Convert empty strings to NULL so nullable columns stay clean.
     */
    protected function normalize(mixed $value): mixed
    {
        return ($value === '') ? null : $value;
    }
}
