<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ApplicationRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $normalizers = [
            'degree_level' => fn ($value) => $value ? strtolower(trim($value)) : $value,
            'study_mode' => fn ($value) => $value ? strtolower(str_replace('_', '-', trim($value))) : $value,
            'language_of_study' => fn ($value) => $value ? strtolower(trim($value)) : $value,
        ];

        $normalized = [];
        foreach ($normalizers as $field => $normalizer) {
            if ($this->has($field)) {
                $normalized[$field] = $normalizer($this->input($field));
            }
        }

        if (! empty($normalized)) {
            $this->merge($normalized);
        }
    }

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'program_id' => 'required|integer|exists:programs,id',
            'faculty_id' => 'nullable|integer|exists:faculties,id',
            'department_id' => 'nullable|integer|exists:departments,id',
            'degree_level' => 'nullable|string|in:bachelor,master,phd',
            'language_of_study' => 'nullable|string|max:30',
            'study_mode' => 'nullable|string|max:50',
            'status' => 'sometimes|string|in:draft,submitted,under_review,missing_documents,accepted,rejected,contract_pending,payment_pending,enrolled,active_student,graduated',
        ];
    }
}
