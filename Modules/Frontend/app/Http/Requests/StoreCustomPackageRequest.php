<?php

namespace Modules\Frontend\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCustomPackageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'airline_id' => 'nullable|integer|exists:airlines,id',
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'email' => 'nullable|email|max:255',
            'travel_date' => 'nullable|date',
            'makkah_hotel' => 'nullable|string|max:255',
            'madinah_hotel' => 'nullable|string|max:255',
            'preferred_transport' => 'nullable|string|max:255',
            'adults' => 'nullable|integer|min:1',
            'children' => 'nullable|integer|min:0',
            'male' => 'nullable|integer|min:0',
            'female' => 'nullable|integer|min:0',
            'food_preference' => 'nullable|string|max:255',
            'additional_note' => 'nullable|string',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Name is required.',
            'phone.required' => 'Phone number is required.',
            'email.email' => 'Email must be a valid email address.',
            'travel_date.date' => 'Travel date must be a valid date.',
            'airline_id.exists' => 'The selected airline is invalid.',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            // Prevent empty-string fields from storing "" instead of NULL.
            'email' => $this->normalize($this->input('email')),
            'travel_date' => $this->normalize($this->input('travel_date')),
            'makkah_hotel' => $this->normalize($this->input('makkah_hotel')),
            'madinah_hotel' => $this->normalize($this->input('madinah_hotel')),
            'preferred_transport' => $this->normalize($this->input('preferred_transport')),
            'food_preference' => $this->normalize($this->input('food_preference')),
            'additional_note' => $this->normalize($this->input('additional_note')),
            'adults' => $this->input('adults') ?: 1,
            'children' => $this->input('children') ?: 0,
            'male' => $this->input('male') ?: 0,
            'female' => $this->input('female') ?: 0,
        ]);
    }

    protected function normalize(mixed $value): mixed
    {
        return ($value === '') ? null : $value;
    }
}
