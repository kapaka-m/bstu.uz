<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class ApanelUserSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::firstOrCreate(['email' => 'apanel@bstu.uz'], [
            'name' => 'Admin Panel User',
            'email' => 'apanel@bstu.uz',
            'password' => Hash::make('password'),
        ]);

        $apanelRole = Role::where('slug', 'apanel')->first();
        if ($apanelRole) {
            $user->roles()->syncWithoutDetaching([$apanelRole->id]);
        }
    }
}
