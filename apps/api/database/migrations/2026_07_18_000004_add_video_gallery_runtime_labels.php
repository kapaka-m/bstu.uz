<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('video_gallery_setting_translations', function (Blueprint $table) {
            foreach ([
                'playing_label',
                'verified_channel_label',
            ] as $column) {
                if (! Schema::hasColumn('video_gallery_setting_translations', $column)) {
                    $table->string($column)->nullable();
                }
            }
        });
    }

    public function down(): void
    {
        Schema::table('video_gallery_setting_translations', function (Blueprint $table) {
            foreach ([
                'playing_label',
                'verified_channel_label',
            ] as $column) {
                if (Schema::hasColumn('video_gallery_setting_translations', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
