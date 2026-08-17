<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $keys = [
            'signUp',
            'successRegister',
            'createAccount',
            'registerPrompt',
            'fullNameLabel',
            'creatingAccount',
            'alreadyHaveAccount',
            'signIn',
            'successLogin',
            'fullNamePlaceholder',
            'loginSubtitle',
            'noAccount',
            'signUpNow',
            'registerTitle',
            'registerSubtitle',
            'loginNow',
            'register',
            'registrationFailed',
            'portalNotAllowed',
        ];

        $ids = DB::table('translation_keys')
            ->where('group', 'auth')
            ->whereIn('key', $keys)
            ->pluck('id');

        if ($ids->isNotEmpty()) {
            DB::table('translation_values')->whereIn('translation_key_id', $ids)->delete();
            DB::table('translation_keys')->whereIn('id', $ids)->delete();
        }

        Cache::forever('public_content_cache_version', (string) now()->getTimestamp());
    }

    public function down(): void
    {
        // Removed keys belonged to disabled legacy direct-registration screens.
    }
};
