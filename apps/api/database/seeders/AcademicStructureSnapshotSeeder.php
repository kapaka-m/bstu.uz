<?php

namespace Database\Seeders;

use Database\Seeders\Concerns\RunsSqlSeedFile;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AcademicStructureSnapshotSeeder extends Seeder
{
    use RunsSqlSeedFile;

    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        try {
            $this->runSqlSeedFile('data/seed_groups/004_academic_structure.sql');
        } finally {
            DB::statement('SET FOREIGN_KEY_CHECKS=1');
        }
    }
}
