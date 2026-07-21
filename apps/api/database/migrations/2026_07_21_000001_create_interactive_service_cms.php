<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('services', function (Blueprint $table) {
            if (! Schema::hasColumn('services', 'service_type')) {
                $table->string('service_type')->default('interactive')->after('slug');
            }
            if (! Schema::hasColumn('services', 'url')) {
                $table->string('url')->nullable()->after('image');
            }
            if (! Schema::hasColumn('services', 'color')) {
                $table->string('color')->nullable()->after('url');
            }
            if (! Schema::hasColumn('services', 'home_visible')) {
                $table->boolean('home_visible')->default(true)->after('color');
            }
            if (! Schema::hasColumn('services', 'opens_new_tab')) {
                $table->boolean('opens_new_tab')->default(true)->after('home_visible');
            }
        });

        Schema::table('service_translations', function (Blueprint $table) {
            if (! Schema::hasColumn('service_translations', 'action_label')) {
                $table->string('action_label')->nullable()->after('description');
            }
        });

        if (! Schema::hasTable('interactive_service_settings')) {
            Schema::create('interactive_service_settings', function (Blueprint $table) {
                $table->id();
                $table->string('key')->unique()->default('main');
                $table->integer('home_limit')->default(4);
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('interactive_service_setting_translations')) {
            Schema::create('interactive_service_setting_translations', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('interactive_service_setting_id');
                $table->string('locale');
                $table->string('home_tag')->nullable();
                $table->string('home_title')->nullable();
                $table->string('view_all_label')->nullable();
                $table->string('loading_label')->nullable();
                $table->string('no_results_label')->nullable();
                $table->timestamps();

                $table->foreign('interactive_service_setting_id', 'int_service_setting_tr_setting_fk')
                    ->references('id')
                    ->on('interactive_service_settings')
                    ->onDelete('cascade');
                $table->unique(['interactive_service_setting_id', 'locale'], 'interactive_service_setting_locale_unique');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('interactive_service_setting_translations');
        Schema::dropIfExists('interactive_service_settings');

        Schema::table('service_translations', function (Blueprint $table) {
            if (Schema::hasColumn('service_translations', 'action_label')) {
                $table->dropColumn('action_label');
            }
        });

        Schema::table('services', function (Blueprint $table) {
            foreach (['service_type', 'url', 'color', 'home_visible', 'opens_new_tab'] as $column) {
                if (Schema::hasColumn('services', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
