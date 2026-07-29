<?php

use Illuminate\Database\Migrations\Migration;
return new class extends Migration
{
    public function up(): void
    {
        // Legacy footer keys are intentionally preserved so rerunning migrations
        // cannot delete translations that may have been edited from /apanel/.
    }

    public function down(): void
    {
    }
};
