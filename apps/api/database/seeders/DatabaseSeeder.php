<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            FullDatabaseSnapshotSeeder::class,
            HomeCmsTranslationSeeder::class,
            AuthCmsSeeder::class,
            WebFooterSeeder::class,
            StudentSystemTranslationSeeder::class,
        ]);
    }
}
