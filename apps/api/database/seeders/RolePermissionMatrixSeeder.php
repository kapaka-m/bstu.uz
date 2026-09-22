<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;

class RolePermissionMatrixSeeder extends Seeder
{
    public function run(): void
    {
        if (
            ! Schema::hasTable('roles')
            || ! Schema::hasTable('permissions')
            || ! Schema::hasTable('permission_role')
        ) {
            return;
        }

        $allPermissions = Permission::query()->pluck('id', 'slug');

        $matrix = [
            'apanel' => $allPermissions->keys()->all(),
            'super_admin' => $allPermissions->keys()->all(),
            'admin' => [
                'manage-settings',
                'manage-locales',
                'manage-translations',
                'manage-menus',
                'manage-pages',
                'manage-faculties',
                'manage-departments',
                'manage-programs',
                'manage-courses',
                'manage-news',
                'manage-announcements',
                'manage-staff',
                'manage-services',
                'manage-media',
                'manage-users',
                'manage-roles',
                'manage-students',
                'manage-applications',
                'manage-documents',
                'manage-contracts',
                'manage-payments',
                'manage-inquiries',
                'manage-support-tickets',
                'manage-comments',
                'manage-notifications',
                'view-audit-logs',
            ],
            'admission_officer' => [
                'manage-students',
                'manage-applications',
                'manage-documents',
                'manage-contracts',
                'manage-inquiries',
                'manage-support-tickets',
                'manage-notifications',
            ],
            'international_office_staff' => [
                'manage-students',
                'manage-applications',
                'manage-documents',
                'manage-contracts',
                'manage-inquiries',
                'manage-support-tickets',
                'manage-notifications',
            ],
            'call_center_staff' => [
                'manage-inquiries',
                'manage-support-tickets',
                'manage-notifications',
            ],
            'faculty_staff' => [
                'manage-faculties',
                'manage-departments',
                'manage-programs',
                'manage-courses',
                'manage-staff',
            ],
            'department_staff' => [
                'manage-departments',
                'manage-programs',
                'manage-courses',
                'manage-staff',
            ],
            'registrar_office_staff' => [
                'manage-students',
                'manage-applications',
                'manage-documents',
                'manage-contracts',
            ],
            'dormitory_manager' => [
                'manage-students',
                'manage-applications',
                'manage-payments',
                'manage-documents',
            ],
            'teacher' => [
                'manage-courses',
                'manage-staff',
            ],
            'finance_staff' => [
                'manage-contracts',
                'manage-payments',
                'manage-documents',
            ],
            'document_officer' => [
                'manage-documents',
                'manage-applications',
            ],
            'content_manager' => [
                'manage-locales',
                'manage-translations',
                'manage-menus',
                'manage-pages',
                'manage-news',
                'manage-announcements',
                'manage-services',
                'manage-media',
                'manage-comments',
            ],
        ];

        foreach ($matrix as $roleSlug => $permissionSlugs) {
            $role = Role::query()->where('slug', $roleSlug)->first();

            if (! $role) {
                continue;
            }

            $permissionIds = collect($permissionSlugs)
                ->map(fn (string $slug) => $allPermissions[$slug] ?? null)
                ->filter()
                ->values()
                ->all();

            $role->permissions()->syncWithoutDetaching($permissionIds);
        }
    }
}
