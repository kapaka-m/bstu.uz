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
            'file' => 'required|file|mimes:pdf,doc,docx,xls,xlsx,csv,txt,jpg,jpeg,png,webp,svg,gif,avif,ico,mp4,avi,mov,webm,mkv,m4v|mimetypes:application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document,application/vnd.ms-excel,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet,text/plain,text/csv,application/csv,image/jpeg,image/png,image/webp,image/svg+xml,image/gif,image/avif,image/x-icon,image/vnd.microsoft.icon,video/mp4,video/x-msvideo,video/quicktime,video/webm,video/x-matroska,video/x-m4v|max:204800',
            'alt_key' => 'nullable|string|max:255',
        ];
    }
}
