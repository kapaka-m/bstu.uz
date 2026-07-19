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
                'views_label',
                'channel_name',
                'comments_label',
                'reply_label',
                'form_title',
                'form_comment_label',
                'form_submit_label',
                'sign_in_title',
                'sign_in_text',
                'sign_in_action',
                'signed_in_as_label',
            ] as $column) {
                if (! Schema::hasColumn('video_gallery_setting_translations', $column)) {
                    $table->string($column)->nullable();
                }
            }
        });

        if (! Schema::hasTable('video_comments')) {
            Schema::create('video_comments', function (Blueprint $table) {
                $table->id();
                $table->foreignId('video_id')->constrained('videos')->cascadeOnDelete();
                $table->foreignId('parent_id')->nullable()->constrained('video_comments')->cascadeOnDelete();
                $table->string('author_name');
                $table->string('email')->nullable();
                $table->text('content');
                $table->boolean('is_approved')->default(true);
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('video_comments');

        Schema::table('video_gallery_setting_translations', function (Blueprint $table) {
            foreach ([
                'views_label',
                'channel_name',
                'comments_label',
                'reply_label',
                'form_title',
                'form_comment_label',
                'form_submit_label',
                'sign_in_title',
                'sign_in_text',
                'sign_in_action',
                'signed_in_as_label',
            ] as $column) {
                if (Schema::hasColumn('video_gallery_setting_translations', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
