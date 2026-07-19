<?php

namespace Database\Seeders;

use App\Models\Permission;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            'manage-locales' => 'Manage system locales and active languages',
            'manage-translations' => 'Manage dynamic key/value translations',
            'manage-settings' => 'Manage global site configurations',
            'manage-menus' => 'Manage main site navigation menus and items',
            'manage-pages' => 'Manage public website pages and content translations',
            'manage-faculties' => 'Manage university faculties',
            'manage-departments' => 'Manage faculty departments',
            'manage-programs' => 'Manage study programs and degrees',
            'manage-courses' => 'Manage courses and curriculum allocations',
            'manage-news' => 'Manage news posts and dynamic press releases',
            'manage-announcements' => 'Manage campus announcements and priority notices',
            'manage-staff' => 'Manage professor and staff profiles',
            'manage-services' => 'Manage university services and facilities information',
            'manage-media' => 'Manage media library and file uploads',
            'manage-users' => 'Manage user accounts and details',
            'manage-roles' => 'Manage user security roles',
            'manage-permissions' => 'Manage user security permissions',
            'manage-students' => 'Manage student profile details and status',
            'manage-applications' => 'Manage admission applications',
            'manage-documents' => 'Manage student uploaded files and verification',
            'manage-contracts' => 'Manage tuition contracts',
            'manage-payments' => 'Manage payment transaction histories',
            'manage-inquiries' => 'Manage general public inquiries and contact forms',
            'manage-support-tickets' => 'Manage support tickets and ticketing messages',
            'manage-comments' => 'Manage comments on pages or student applications',
            'manage-notifications' => 'Manage and send system notifications',
            'view-audit-logs' => 'View system audit logs and action trails',
        ];

        foreach ($permissions as $slug => $description) {
            Permission::updateOrCreate(['slug' => $slug], [
                'name' => Str::title(str_replace('-', ' ', $slug)),
                'slug' => $slug,
                'description' => $description,
            ]);
        }
    }
}
