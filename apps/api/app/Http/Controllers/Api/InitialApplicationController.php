<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ApplicationCountry;
use App\Models\ApplicationNationality;
use App\Models\Application;
use App\Models\ApplicationStatusHistory;
use App\Models\EducationBackground;
use App\Models\Program;
use App\Models\Role;
use App\Models\StudentProfile;
use App\Models\User;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Throwable;

class InitialApplicationController extends Controller
{
    use ApiResponse;

    private array $countries = [
        'Afghanistan', 'Albania', 'Algeria', 'Andorra', 'Angola', 'Argentina', 'Armenia', 'Australia', 'Austria', 'Azerbaijan',
        'Bahrain', 'Bangladesh', 'Belarus', 'Belgium', 'Brazil', 'Bulgaria', 'Canada', 'China', 'Egypt', 'France',
        'Georgia', 'Germany', 'India', 'Indonesia', 'Iran', 'Iraq', 'Italy', 'Japan', 'Jordan', 'Kazakhstan',
        'Kuwait', 'Kyrgyzstan', 'Malaysia', 'Morocco', 'Pakistan', 'Qatar', 'Russia', 'Saudi Arabia', 'South Korea', 'Tajikistan',
        'Turkey', 'Turkmenistan', 'United Arab Emirates', 'United Kingdom', 'United States', 'Uzbekistan',
    ];

    private array $nationalities = [
        'Afghan', 'Albanian', 'Algerian', 'Andorran', 'Angolan', 'Argentinian', 'Armenian', 'Australian', 'Austrian', 'Azerbaijani',
        'Bahraini', 'Bangladeshi', 'Belarusian', 'Belgian', 'Brazilian', 'Bulgarian', 'Canadian', 'Chinese', 'Egyptian', 'French',
        'Georgian', 'German', 'Indian', 'Indonesian', 'Iranian', 'Iraqi', 'Italian', 'Japanese', 'Jordanian', 'Kazakh',
        'Kuwaiti', 'Kyrgyz', 'Malaysian', 'Moroccan', 'Pakistani', 'Qatari', 'Russian', 'Saudi', 'South Korean', 'Tajik',
        'Turkish', 'Turkmen', 'Emirati', 'British', 'American', 'Uzbek',
    ];

    public function metadata(Request $request)
    {
        $locale = $this->getLocale($request);
        $countries = $this->availableCountries();
        $nationalities = $this->availableNationalities();
        $programs = Program::where('is_active', true)
            ->with(['translations', 'faculty.translations', 'department.translations'])
            ->orderBy('sort_order')
            ->get()
            ->map(function (Program $program) use ($locale) {
                $translation = $program->translations->firstWhere('locale', $locale)
                    ?: $program->translations->firstWhere('locale', 'en');
                $facultyTranslation = $program->faculty?->translations->firstWhere('locale', $locale)
                    ?: $program->faculty?->translations->firstWhere('locale', 'en');
                $departmentTranslation = $program->department?->translations->firstWhere('locale', $locale)
                    ?: $program->department?->translations->firstWhere('locale', 'en');

                $educationTypes = $this->parseOptionList($program->study_mode);
                $languages = $this->parseStudyLanguages($program->language_of_study);

                return [
                    'id' => $program->id,
                    'slug' => $program->slug,
                    'code' => $program->official_code ?: $program->code,
                    'name' => $translation?->name,
                    'degree' => strtolower((string) $program->degree),
                    'study_mode' => $program->study_mode,
                    'available_education_types' => $educationTypes,
                    'language_of_study' => $program->language_of_study,
                    'available_study_languages' => $languages,
                    'duration_years' => $program->duration_years,
                    'faculty' => [
                        'id' => $program->faculty_id,
                        'slug' => $program->faculty?->slug,
                        'name' => $facultyTranslation?->name,
                    ],
                    'department' => [
                        'id' => $program->department_id,
                        'slug' => $program->department?->slug,
                        'name' => $departmentTranslation?->name,
                    ],
                ];
            })
            ->values();

        $degrees = $programs->pluck('degree')->filter()->unique()->values()->all();
        $studyModes = $programs->pluck('available_education_types')->flatten()->filter()->unique()->values()->all();
        $studyLanguages = $programs->pluck('available_study_languages')->flatten()->filter()->unique()->values()->all();
        $intakeYear = (int) now()->format('Y');

        return $this->successResponse([
            'countries' => $countries,
            'nationalities' => $nationalities,
            'genders' => ['male', 'female'],
            'passport_types' => ['ordinary', 'diplomatic', 'service'],
            'student_types' => ['new', 'transfer'],
            'degrees' => $degrees,
            'education_types' => $studyModes,
            'study_languages' => $studyLanguages,
            'intakes' => [
                'fall-'.$intakeYear,
                'spring-'.($intakeYear + 1),
            ],
            'programs' => $programs,
            'password_min_length' => 8,
        ], 'Initial application metadata retrieved');
    }

    public function store(Request $request)
    {
        $this->normalizeInput($request);
        $countries = $this->availableCountries();
        $nationalities = $this->availableNationalities();

        $validated = $request->validate([
            'full_name_english' => ['required', 'string', 'max:255', "regex:/^[A-Z][A-Z\\s\\-']*$/"],
            'birth_date' => ['required', 'date', 'before_or_equal:today'],
            'country_of_birth' => ['required', 'string', 'max:120', Rule::in($countries)],
            'place_of_birth' => ['required', 'string', 'max:120', "regex:/^[A-Za-z][A-Za-z\\s\\-']*$/"],
            'nationality' => ['required', 'string', 'max:120', Rule::in($nationalities)],
            'gender' => ['required', Rule::in(['male', 'female'])],
            'passport_number' => ['required', 'string', 'max:50', 'regex:/^[A-Z0-9\\-]+$/', Rule::unique('student_profiles', 'passport_number')],
            'passport_type' => ['required', Rule::in(['ordinary', 'diplomatic', 'service'])],
            'passport_issue_date' => ['required', 'date', 'before_or_equal:today'],
            'passport_expiry_date' => ['required', 'date', 'after:passport_issue_date', 'after:today'],
            'passport_issuing_country' => ['required', 'string', 'max:120', Rule::in($countries)],
            'passport_place_of_issue' => ['required', 'string', 'max:160'],
            'primary_phone' => ['required', 'string', 'max:30', 'regex:/^\\+[1-9]\\d{7,14}$/'],
            'preferred_messenger' => ['required', Rule::in(['whatsapp', 'telegram', 'both'])],
            'telegram_username' => ['nullable', 'string', 'max:80', 'regex:/^@?[A-Za-z0-9_]{5,32}$/'],
            'alternative_phone' => ['nullable', 'string', 'max:30', 'regex:/^\\+[1-9]\\d{7,14}$/', 'different:primary_phone'],
            'degree_level' => ['required', Rule::in(['bachelor', 'master', 'doctorate', 'phd'])],
            'student_type' => ['required', Rule::in(['new', 'transfer'])],
            'education_type' => ['required', 'string', 'max:50'],
            'faculty_id' => ['required', 'integer', 'exists:faculties,id'],
            'program_id' => ['required', 'integer', 'exists:programs,id'],
            'study_language' => ['required', 'string', 'max:50'],
            'intended_intake' => ['required', 'string', 'max:50'],
            'email' => ['required', 'email:rfc,dns', 'max:255', Rule::unique('users', 'email')],
            'password' => ['required', 'string', 'min:8', 'confirmed', 'regex:/^(?=.*[A-Za-z])(?=.*\\d).+$/'],
            'terms_agreement' => ['accepted'],
            'information_confirmation' => ['accepted'],
        ]);

        $program = Program::where('is_active', true)->findOrFail($validated['program_id']);
        $requestedDegree = $validated['degree_level'] === 'doctorate' ? 'phd' : $validated['degree_level'];

        $availableEducationTypes = $this->parseOptionList($program->study_mode);
        $availableLanguages = $this->parseStudyLanguages($program->language_of_study);

        if ((int) $program->faculty_id !== (int) $validated['faculty_id']
            || strtolower((string) $program->degree) !== $requestedDegree
            || ! in_array(strtolower($validated['education_type']), $availableEducationTypes, true)
            || ! in_array(strtolower($validated['study_language']), $availableLanguages, true)) {
            throw ValidationException::withMessages([
                'program_id' => ['The selected program is not available for the chosen academic options.'],
            ]);
        }

        DB::beginTransaction();
        try {
            $user = User::create([
                'name' => $validated['full_name_english'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
            ]);

            $studentRole = Role::where('slug', 'student')->first();
            if ($studentRole) {
                $user->roles()->syncWithoutDetaching([$studentRole->id]);
            }

            $profile = StudentProfile::create([
                'user_id' => $user->id,
                'full_name_english' => $validated['full_name_english'],
                'phone' => $validated['primary_phone'],
                'alternative_phone' => $validated['alternative_phone'] ?? null,
                'preferred_messenger' => $validated['preferred_messenger'],
                'telegram_username' => $validated['telegram_username'] ?? null,
                'gender' => $validated['gender'],
                'birth_date' => $validated['birth_date'],
                'country_of_birth' => $validated['country_of_birth'],
                'place_of_birth' => $validated['place_of_birth'],
                'passport_number' => $validated['passport_number'],
                'passport_type' => $validated['passport_type'],
                'passport_issue_date' => $validated['passport_issue_date'],
                'passport_expiry_date' => $validated['passport_expiry_date'],
                'passport_issuing_country' => $validated['passport_issuing_country'],
                'passport_place_of_issue' => $validated['passport_place_of_issue'],
                'nationality' => $validated['nationality'],
                'address' => $validated['place_of_birth'].', '.$validated['country_of_birth'],
            ]);

            EducationBackground::create([
                'student_profile_id' => $profile->id,
                'institution_name' => 'To be completed in student dashboard',
                'degree_obtained' => $requestedDegree,
                'gpa' => 'To be completed',
                'graduation_year' => (int) now()->format('Y'),
            ]);

            $application = Application::create([
                'application_number' => $this->nextApplicationNumber(),
                'student_profile_id' => $profile->id,
                'program_id' => $program->id,
                'faculty_id' => $program->faculty_id,
                'department_id' => $program->department_id,
                'degree_level' => $requestedDegree,
                'student_type' => $validated['student_type'],
                'language_of_study' => $validated['study_language'],
                'study_mode' => $validated['education_type'],
                'intended_intake' => $validated['intended_intake'],
                'status' => 'profile_created',
                'terms_agreed_at' => now(),
                'information_confirmed_at' => now(),
            ]);

            ApplicationStatusHistory::create([
                'application_id' => $application->id,
                'old_status' => null,
                'new_status' => 'profile_created',
                'status' => 'profile_created',
                'comment' => 'Initial public application created',
                'note' => 'Initial public application created',
                'changed_by' => $user->id,
            ]);

            $token = $user->createToken('auth_token')->plainTextToken;
            DB::commit();

            $user->load('roles');

            return $this->successResponse([
                'user' => array_merge($user->toArray(), [
                    'roles' => $user->roles->pluck('slug')->values()->all(),
                ]),
                'student_profile' => $profile,
                'application' => $application,
                'application_number' => $application->application_number,
                'access_token' => $token,
                'token_type' => 'Bearer',
            ], 'Initial application created successfully', 201);
        } catch (Throwable $e) {
            DB::rollBack();
            report($e);

            return $this->errorResponse('Unable to create the application. Please try again.', 500);
        }
    }

    private function normalizeInput(Request $request): void
    {
        $request->merge([
            'full_name_english' => strtoupper(trim((string) $request->input('full_name_english'))),
            'place_of_birth' => trim((string) $request->input('place_of_birth')),
            'passport_number' => strtoupper(preg_replace('/\\s+/', '', (string) $request->input('passport_number'))),
            'primary_phone' => preg_replace('/[^+\\d]/', '', (string) $request->input('primary_phone')),
            'alternative_phone' => $request->filled('alternative_phone') ? preg_replace('/[^+\\d]/', '', (string) $request->input('alternative_phone')) : null,
            'email' => strtolower(trim((string) $request->input('email'))),
            'degree_level' => strtolower((string) $request->input('degree_level')),
            'education_type' => strtolower((string) $request->input('education_type')),
            'study_language' => strtolower((string) $request->input('study_language')),
        ]);
    }

    private function nextApplicationNumber(): string
    {
        $year = now()->format('Y');

        do {
            $number = 'APP-'.$year.'-'.strtoupper(str_pad(base_convert((string) random_int(0, 2176782335), 10, 36), 6, '0', STR_PAD_LEFT));
        } while (Application::where('application_number', $number)->exists());

        return $number;
    }

    private function getLocale(Request $request): string
    {
        $locale = $request->query('locale', $request->header('X-Locale', 'en'));

        return in_array($locale, ['en', 'uz', 'ru', 'ar'], true) ? $locale : 'en';
    }

    private function parseStudyLanguages(?string $value): array
    {
        return $this->parseOptionList($value);
    }

    private function parseOptionList(?string $value): array
    {
        $parts = preg_split('/[,;\\/|]+/', (string) $value) ?: [];

        return collect($parts)
            ->map(fn ($item) => strtolower(trim($item)))
            ->filter()
            ->map(fn ($item) => str_replace([' ', '-'], '_', $item))
            ->unique()
            ->values()
            ->all();
    }

    private function availableCountries(): array
    {
        if (Schema::hasTable('application_countries')) {
            $countries = ApplicationCountry::query()
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->orderBy('name')
                ->pluck('name')
                ->filter()
                ->values()
                ->all();

            if (! empty($countries)) {
                return $countries;
            }
        }

        return $this->countries;
    }

    private function availableNationalities(): array
    {
        if (Schema::hasTable('application_nationalities')) {
            $nationalities = ApplicationNationality::query()
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->orderBy('name')
                ->pluck('name')
                ->filter()
                ->values()
                ->all();

            if (! empty($nationalities)) {
                return $nationalities;
            }
        }

        return $this->nationalities;
    }
}
