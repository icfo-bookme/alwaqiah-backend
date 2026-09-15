<?php

namespace Modules\Frontend\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;

class StoreYoutubeVideoRequest extends FormRequest
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
            'title'             => 'required|string|max:255',
            'slug'              => 'nullable|string|max:255|unique:youtube_videos,slug',
            'youtube_url'       => [
                'required',
                'string',
                'max:500',
                'regex:#^(https?://)?(www\.)?(youtube\.com/(watch\?v=|embed/|shorts/|live/)|youtu\.be/)[A-Za-z0-9_\-]+#i',
            ],
            'youtube_video_id'  => 'nullable|string|max:100|regex:/^[A-Za-z0-9_\-]+$/',
            'thumbnail'         => 'nullable|string|max:500',
            'description'       => 'nullable|string|max:1000',
            'is_active'         => 'nullable|boolean',
        ];
    }

    /**
     * Get custom messages for validation errors.
     */
    public function messages(): array
    {
        return [
            'title.required'             => 'Video title is required.',
            'title.max'                  => 'Video title may not be greater than 255 characters.',
            'slug.unique'                => 'This slug is already taken. Please choose another one.',
            'youtube_url.required'       => 'YouTube URL is required.',
            'youtube_url.max'            => 'YouTube URL may not be greater than 500 characters.',
            'youtube_url.regex'          => 'Please provide a valid YouTube video URL (e.g. https://www.youtube.com/watch?v=xxxx or https://youtu.be/xxxx).',
            'youtube_video_id.max'       => 'YouTube video ID may not be greater than 100 characters.',
            'youtube_video_id.regex'     => 'YouTube video ID may only contain letters, numbers, dashes and underscores.',
            'thumbnail.max'              => 'Thumbnail URL may not be greater than 500 characters.',
            'description.max'            => 'Description may not be greater than 1000 characters.',
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

        // Prevent empty-string slug from failing the unique rule (null = not provided).
        $this->merge([
            'slug' => ($slug === '') ? null : $slug,
        ]);
    }
}