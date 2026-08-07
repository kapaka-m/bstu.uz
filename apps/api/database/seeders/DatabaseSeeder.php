<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0');

        try {
            $this->call([
                CoreSystemSnapshotSeeder::class,
                LocalizationSnapshotSeeder::class,
                NavigationPageSnapshotSeeder::class,
                AcademicStructureSnapshotSeeder::class,
                PublicCmsSnapshotSeeder::class,
                AdmissionsWorkflowSnapshotSeeder::class,
                CommunicationSnapshotSeeder::class,
            ]);
        } finally {
            DB::statement('SET FOREIGN_KEY_CHECKS=1');
        }
    }
}