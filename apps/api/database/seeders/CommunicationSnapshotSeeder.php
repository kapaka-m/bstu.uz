<?php

namespace Database\Seeders;

use Database\Seeders\Concerns\RunsSqlSeedFile;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CommunicationSnapshotSeeder extends Seeder
{
    use RunsSqlSeedFile;

    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        try {
            $this->runSqlSeedFile('data/seed_groups/007_communications.sql');
        } finally {
            DB::statement('SET FOREIGN_KEY_CHECKS=1');
        }
    }
}
