<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('service_translations', function (Blueprint $table) {
            if (! Schema::hasColumn('service_translations', 'detail_heading')) {
                $table->string('detail_heading')->nullable()->after('action_label');
            }
            if (! Schema::hasColumn('service_translations', 'detailed_description')) {
                $table->longText('detailed_description')->nullable()->after('detail_heading');
            }
            if (! Schema::hasColumn('service_translations', 'benefits')) {
                $table->json('benefits')->nullable()->after('detailed_description');
            }
            if (! Schema::hasColumn('service_translations', 'additional_description_1')) {
                $table->longText('additional_description_1')->nullable()->after('benefits');
            }
            if (! Schema::hasColumn('service_translations', 'additional_description_2')) {
                $table->longText('additional_description_2')->nullable()->after('additional_description_1');
            }
        });
    }

    public function down(): void
    {
        Schema::table('service_translations', function (Blueprint $table) {
            foreach (['detail_heading', 'detailed_description', 'benefits', 'additional_description_1', 'additional_description_2'] as $column) {
                if (Schema::hasColumn('service_translations', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
