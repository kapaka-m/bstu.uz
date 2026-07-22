<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('inquiries', function (Blueprint $table) {
            $table->timestamp('read_at')->nullable()->after('status');
            $table->text('reply_message')->nullable()->after('read_at');
            $table->timestamp('replied_at')->nullable()->after('reply_message');
            $table->text('admin_notes')->nullable()->after('replied_at');
        });
    }

    public function down(): void
    {
        Schema::table('inquiries', function (Blueprint $table) {
            $table->dropColumn(['read_at', 'reply_message', 'replied_at', 'admin_notes']);
        });
    }
};
