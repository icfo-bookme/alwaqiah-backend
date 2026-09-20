<?php

namespace Modules\Frontend\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class UpdatePackageRequest extends FormRequest
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
        $packageId = $this->route('package');

        return [
            'title' => 'required|string|max:255',
            'slug' => [
                'nullable',
                'string',
                'max:255',
                Rule::unique('packages', 'slug')->ignore($packageId),
            ],
            'duration_days' => 'required|integer|min:1|max:365',
            'price' => 'required|numeric|min:0|max:9999999999.99',
            'price_label' => 'nullable|string|max:255',
            'short_description' => 'nullable|string|max:1000',
            'description' => 'nullable|string|max:65535',
            'thumbnail' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'package_type' => 'required|in:hajj,umrah',
            'is_featured' => 'nullable|boolean',
            'is_active' => 'nullable|boolean',
        ];
    }

    /**
     * Get custom messages for validation errors.
     */
    public function messages(): array
    {
        return [
            'title.required' => 'Package title is required.',
            'title.max' => 'Package title may not be greater than 255 characters.',
            'slug.unique' => 'This slug is already taken. Please choose another one.',
            'duration_days.required' => 'Duration (days) is required.',
            'duration_days.integer' => 'Duration must be a whole number of days.',
            'duration_days.min' => 'Duration must be at least 1 day.',
            'duration_days.max' => 'Duration may not be greater than 365 days.',
            'price.required' => 'Price is required.',
            'price.numeric' => 'Price must be a valid number.',
            'price.min' => 'Price cannot be negative.',
            'price.max' => 'Price is too large.',
            'price_label.max' => 'Price label may not be greater than 255 characters.',
            'short_description.max' => 'Short description may not be greater than 1000 characters.',
            'description.max' => 'Description is too long.',
            'thumbnail.image' => 'Thumbnail must be a valid image file.',
            'thumbnail.mimes' => 'Thumbnail must be a JPG, JPEG, PNG or WEBP image.',
            'thumbnail.max' => 'Thumbnail may not be larger than 2 MB.',
            'package_type.required' => 'Package type is required.',
            'package_type.in' => 'Package type must be either Hajj or Umrah.',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        $slug = $this->input('slug');

        // Auto-generate slug from title when not provided.
        if (empty($slug) && $this->filled('title')) {
            $slug = Str::slug($this->input('title'));
        }

        $this->merge([
            // Prevent empty-string slug from failing the unique rule (null = not provided).
            'slug' => ($slug === '') ? null : $slug,
        ]);
    }
}
