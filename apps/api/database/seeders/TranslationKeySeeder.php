<?php

namespace Database\Seeders;

use App\Models\TranslationKey;
use Illuminate\Database\Seeder;

class TranslationKeySeeder extends Seeder
{
    public function run(): void
    {
        $filePath = database_path('data/translations.json');
        if (! file_exists($filePath)) {
            $this->command->error('translations.json not found!');

            return;
        }

        $translations = json_decode(file_get_contents($filePath), true);

        $flat = [];
        foreach ($translations as $localeData) {
            if (is_array($localeData)) {
                $flat += $this->flattenArray($localeData);
            }
        }

        // Group mapping mapping
        $groupMapping = [
            'nav' => 'nav',
            'common' => 'common',
            'about' => 'about',
            'home' => 'home',
            'auth' => 'auth',
            'validation' => 'validation',
            'status' => 'status',
            'student' => 'student',
            'apanel' => 'apanel',
            'application' => 'application',
            'notification' => 'notification',
            'menu' => 'menu',
            'error' => 'error',
            'success' => 'success',
            'button' => 'button',
            'form' => 'form',
            'section' => 'section',
            'breadcrumb' => 'breadcrumb',
            'page' => 'page',
        ];

        foreach ($flat as $flatKey => $value) {
            // We ignore large dynamic translations under news, announcements, faculties, departments, programs, centers, videos, greenCampus because they go into dynamic tables!
            $parts = explode('.', $flatKey);
            $rootGroup = $parts[0] ?? '';

            if (in_array($rootGroup, ['footer', 'news', 'announcements', 'faculties', 'departments', 'programs', 'centers', 'videos', 'greenCampus'])) {
                continue;
            }

            // Determine target group and key
            $group = $rootGroup;
            $key = implode('.', array_slice($parts, 1));

            // Apply special overrides for requested groups:
            if ($group === 'common' && (str_ends_with($key, 'Btn') || in_array($key, ['submit', 'cancel', 'login', 'register']))) {
                $group = 'button';
            }
            if ($group === 'common' && (str_contains($key, 'label') || str_contains($key, 'placeholder') || str_contains($key, 'field'))) {
                $group = 'form';
            }

            // Fallback group to 'common' if not in mapping
            if (! isset($groupMapping[$group])) {
                $group = 'common';
            }

            TranslationKey::firstOrCreate(
                ['group' => $group, 'key' => $key],
                [
                    'description' => 'System UI label: '.$flatKey,
                    'is_system' => true,
                ]
            );
        }

        // Add additional required groups from Task 12 if not already created
        $additionalKeys = [
            ['group' => 'button', 'key' => 'submit', 'description' => 'Submit button text'],
            ['group' => 'button', 'key' => 'cancel', 'description' => 'Cancel button text'],
            ['group' => 'button', 'key' => 'login', 'description' => 'Login button text'],
            ['group' => 'button', 'key' => 'register', 'description' => 'Register button text'],
            ['group' => 'auth', 'key' => 'login_title', 'description' => 'Login Page Title'],
            ['group' => 'auth', 'key' => 'register_title', 'description' => 'Register Page Title'],
            ['group' => 'auth', 'key' => 'logout_success', 'description' => 'Logout success message'],
            ['group' => 'validation', 'key' => 'required', 'description' => 'Field required validation error'],
            ['group' => 'validation', 'key' => 'email_invalid', 'description' => 'Invalid email validation error'],
            ['group' => 'status', 'key' => 'pending', 'description' => 'Pending status'],
            ['group' => 'status', 'key' => 'approved', 'description' => 'Approved status'],
            ['group' => 'status', 'key' => 'rejected', 'description' => 'Rejected status'],
            ['group' => 'student', 'key' => 'profile', 'description' => 'Student Profile Title'],
            ['group' => 'student', 'key' => 'documents', 'description' => 'Student Documents Section'],
            ['group' => 'application', 'key' => 'apply', 'description' => 'Start Application Link'],
            ['group' => 'application', 'key' => 'my_applications', 'description' => 'My Applications Header'],
            ['group' => 'notification', 'key' => 'unread', 'description' => 'Unread Notifications Count Text'],
            ['group' => 'notification', 'key' => 'all', 'description' => 'All Notifications Link'],
        ];

        foreach ($additionalKeys as $ak) {
            TranslationKey::firstOrCreate(
                ['group' => $ak['group'], 'key' => $ak['key']],
                ['description' => $ak['description'], 'is_system' => true]
            );
        }
    }

    private function flattenArray(array $array, string $prefix = ''): array
    {
        $result = [];
        foreach ($array as $key => $value) {
            if (is_array($value)) {
                $result = array_merge($result, $this->flattenArray($value, $prefix.$key.'.'));
            } else {
                $result[$prefix.$key] = $value;
            }
        }

        return $result;
    }
}
