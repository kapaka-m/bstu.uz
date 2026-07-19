<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class CoreSystemSeeder extends Seeder
{
    /**
     * Seed only the records required for system access and administration.
     */
    public function run(): void
    {
        $this->call([
            LocaleSeeder::class,
            PermissionSeeder::class,
            RoleSeeder::class,
            ApanelUserSeeder::class,
        ]);
    }
}
