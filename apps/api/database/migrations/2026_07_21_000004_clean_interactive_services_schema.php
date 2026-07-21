<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('services', function (Blueprint $table) {
            foreach (['image', 'service_type'] as $column) {
                if (Schema::hasColumn('services', $column)) {
                    $table->dropColumn($column);
                }
            }
        });

        Schema::table('service_translations', function (Blueprint $table) {
            if (Schema::hasColumn('service_translations', 'content')) {
                $table->dropColumn('content');
            }
        });
    }

    public function down(): void
    {
        Schema::table('services', function (Blueprint $table) {
            if (! Schema::hasColumn('services', 'service_type')) {
                $table->string('service_type')->default('interactive')->after('slug');
            }

            if (! Schema::hasColumn('services', 'image')) {
                $table->string('image')->nullable()->after('icon');
            }
        });

        Schema::table('service_translations', function (Blueprint $table) {
            if (! Schema::hasColumn('service_translations', 'content')) {
                $table->longText('content')->nullable()->after('additional_description_2');
            }
        });
    }
};
