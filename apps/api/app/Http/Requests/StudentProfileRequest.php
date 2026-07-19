<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StudentProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'phone' => 'required|string|max:30',
            'gender' => 'required|string|max:10',
            'birth_date' => 'required|date',
            'passport_number' => 'required|string|max:50',
            'passport_expiry_date' => 'nullable|date|after:today',
            'nationality' => 'required|string|max:100',
            'address' => 'required|string',

            'guardian_name' => 'nullable|string|max:255',
            'guardian_relation' => 'nullable|string|max:100',
            'guardian_phone' => 'nullable|string|max:30',
            'guardian_email' => 'nullable|email|max:255',

            'education_institution_name' => 'nullable|string|max:255',
            'education_degree_obtained' => 'nullable|string|max:255',
            'education_gpa' => 'nullable|string|max:20',
            'education_graduation_year' => 'nullable|integer',
        ];
    }
}
