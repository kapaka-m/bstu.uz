<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('translation_keys')->where('group', 'footer')->delete();
    }

    public function down(): void
    {
        // Footer content is managed by web_footers and web_footer_translations.
    }
};
