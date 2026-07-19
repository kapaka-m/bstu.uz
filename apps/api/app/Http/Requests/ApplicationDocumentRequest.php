<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ApplicationDocumentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'document_type' => 'required|string|in:passport,photo,education_certificate,transcript,medical_certificate,language_certificate,payment_receipt,other',
            'document_name' => 'nullable|string|max:255',
            'note' => 'nullable|string|max:1000',
            'file' => 'required|file|mimes:pdf,jpg,jpeg,png,webp|mimetypes:application/pdf,image/jpeg,image/png,image/webp|max:10240',
        ];
    }
}
