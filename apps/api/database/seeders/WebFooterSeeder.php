<?php

namespace Database\Seeders;

use App\Models\WebFooter;
use Illuminate\Database\Seeder;

class WebFooterSeeder extends Seeder
{
    public function run(): void
    {
        WebFooter::firstOrCreate(
            ['key' => 'main'],
            ['is_active' => true]
        );
    }
}
