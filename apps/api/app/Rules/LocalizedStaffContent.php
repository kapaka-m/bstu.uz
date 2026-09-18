<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class LocalizedStaffContent implements ValidationRule
{
    public function __construct(private string $locale) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_string($value) || $this->locale === 'en') {
            return;
        }
        $dictionary = json_decode(file_get_contents(database_path('data/localization_repairs.json')), true, flags: JSON_THROW_ON_ERROR);
        $index = array_search($this->locale, ['uz', 'ru', 'ar'], true);
        if ($index === false) {
            return;
        }
        $text = trim($value);
        $expected = $dictionary[$text][$index] ?? $text;
        if ($expected !== $text || preg_match('/ serves as |Standard course covering topics in |^(?:Monday-Friday|Monday-Saturday|Daily) \d/', $text)) {
            $fail(match ($this->locale) {
                'uz' => 'Ushbu maydonni o‘zbek tilida kiriting.',
                'ru' => 'Заполните это поле на русском языке.',
                'ar' => 'أدخل هذا الحقل باللغة العربية.',
            });
        }
    }
}
