<?php

namespace App\Http\Requests\Post;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StorePostRequest extends FormRequest
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
            'title_en' => ['required', 'string', 'min:5', 'max:255'],
            'title_km' => ['nullable', 'string', 'max:255'],
            'content_en' => ['required', 'string', 'min:50'],
            'content_km' => ['nullable', 'string'],
            'category_id' => ['required', 'uuid', 'exists:categories,id'],
            'main_image' => ['required', 'image', 'mimes:jpg,jpeg,png', 'max:2048'],
            'gallery_images' => ['nullable', 'array', 'max:5'],
            'gallery_images.*' => ['image', 'mimes:jpg,jpeg,png', 'max:2048'],
        ];
    }

    public function messages(): array
    {
        return [
            'title_en.required' => 'English title is required.',
            'title_en.min' => 'Title must be at least 5 characters.',
            'content_en.required' => 'English content is required.',
            'content_en.min' => 'Content must be at least 50 characters.',
            'category_id.required' => 'Please select a category.',
            'category_id.exists' => 'Selected category does not exist.',
            'main_image.required' => 'Main image is required.',
            'main_image.max' => 'Main image must not exceed 2MB.',
            'main_image.mimes' => 'Main image must be jpg or png.',
            'gallery_images.max' => 'You can upload a maximum of 5 gallery images.',
            'gallery_images.*.max' => 'Each gallery image must not exceed 2MB.',
            'gallery_images.*.mimes' => 'Gallery images must be jpg or png.',
        ];
    }
}
