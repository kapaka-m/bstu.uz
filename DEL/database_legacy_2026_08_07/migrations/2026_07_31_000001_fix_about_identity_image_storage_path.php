<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('about_pages')) {
            return;
        }

        DB::table('about_pages')
            ->where('identity_image', 'about-page/bstu-about-identity.jpg')
            ->update(['identity_image' => 'cms/about-page/bstu-about-identity.jpg']);
    }

    public function down(): void
    {
        //
    }
};
