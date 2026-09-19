<?php

namespace Modules\Frontend\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreFlightRequest extends FormRequest
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
            'airline_id'               => 'required|integer|exists:airlines,id',
            'flight_number'            => 'nullable|string|max:255',

            'departure_airport'        => 'nullable|string|max:255',
            'arrival_airport'          => 'nullable|string|max:255',
            'departure_at'             => 'nullable|date',

            'return_departure_airport' => 'nullable|string|max:255',
            'return_arrival_airport'   => 'nullable|string|max:255',
            'return_at'                => 'nullable|date|after_or_equal:departure_at',

            'is_active'                => 'nullable|boolean',
        ];
    }

    /**
     * Get custom messages for validation errors.
     */
    public function messages(): array
    {
        return [
            'airline_id.required' => 'Airline is required.',
            'airline_id.integer'  => 'Airline must be a valid selection.',
            'airline_id.exists'   => 'The selected airline is invalid.',

            'flight_number.string'  => 'Flight number must be a valid text.',
            'flight_number.max'     => 'Flight number may not be greater than 255 characters.',

            'departure_airport.string'        => 'Departure airport must be a valid text.',
            'departure_airport.max'           => 'Departure airport may not be greater than 255 characters.',
            'arrival_airport.string'          => 'Arrival airport must be a valid text.',
            'arrival_airport.max'             => 'Arrival airport may not be greater than 255 characters.',

            'departure_at.date'               => 'Departure time must be a valid date & time.',
            'return_at.date'                  => 'Return time must be a valid date & time.',
            'return_at.after_or_equal'        => 'Return time must be after or equal to the departure time.',

            'return_departure_airport.string' => 'Return departure airport must be a valid text.',
            'return_departure_airport.max'    => 'Return departure airport may not be greater than 255 characters.',
            'return_arrival_airport.string'   => 'Return arrival airport must be a valid text.',
            'return_arrival_airport.max'      => 'Return arrival airport may not be greater than 255 characters.',

            'is_active.boolean'               => 'The status must be either active or inactive.',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            // Prevent empty-string fields from storing "" instead of NULL.
            'flight_number'            => $this->normalize($this->input('flight_number')),
            'departure_airport'        => $this->normalize($this->input('departure_airport')),
            'arrival_airport'          => $this->normalize($this->input('arrival_airport')),
            'departure_at'             => $this->normalize($this->input('departure_at')),
            'return_departure_airport' => $this->normalize($this->input('return_departure_airport')),
            'return_arrival_airport'   => $this->normalize($this->input('return_arrival_airport')),
            'return_at'                => $this->normalize($this->input('return_at')),
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