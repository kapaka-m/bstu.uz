<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class MediaUploadRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'file' => 'required|file|mimes:pdf,jpg,jpeg,png,webp,mp4,avi,mov|mimetypes:application/pdf,image/jpeg,image/png,image/webp,video/mp4,video/x-msvideo,video/quicktime|max:204800',
            'alt_key' => 'nullable|string|max:255',
        ];
    }
}
