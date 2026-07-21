<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('services', 'service_type')) {
            return;
        }

        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE services MODIFY service_type VARCHAR(255) NOT NULL DEFAULT 'interactive'");
        }
    }

    public function down(): void
    {
        if (! Schema::hasColumn('services', 'service_type')) {
            return;
        }

        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE services MODIFY service_type VARCHAR(255) NOT NULL DEFAULT 'academic'");
        }
    }
};
