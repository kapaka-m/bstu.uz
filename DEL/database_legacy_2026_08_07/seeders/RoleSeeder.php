<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            'apanel' => 'Admin Panel Full Control',
            'super_admin' => 'Super Administrator',
            'admin' => 'Administrator',
            'admission_officer' => 'Admission Officer',
            'international_office_staff' => 'International Office Staff',
            'call_center_staff' => 'Call Center Staff',
            'faculty_staff' => 'Faculty Staff',
            'department_staff' => 'Department Staff',
            'registrar_office_staff' => 'Registrar Office Staff',
            'dormitory_manager' => 'Dormitory Manager',
            'teacher' => 'University Teacher',
            'student' => 'University Student',
            'finance_staff' => 'Finance Staff',
            'document_officer' => 'Document Officer',
        ];

        $seededRoles = [];
        foreach ($roles as $slug => $name) {
            $seededRoles[$slug] = Role::firstOrCreate(['slug' => $slug], [
                'name' => $name,
                'slug' => $slug,
                'description' => "Role for {$name}",
            ]);
        }

        // Attach all permissions to apanel
        $allPermissions = Permission::all();
        $seededRoles['apanel']->permissions()->syncWithoutDetaching($allPermissions->pluck('id')->all());
    }
}
