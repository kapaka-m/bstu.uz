<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('staff_profile_translations')) {
            return;
        }

        $names = [
            'Dr. Khojiyev Aziz Kholmurodovich' => 'د. خوجييف عزيز خولمورودوفيتش',
            'Xojiyev Aziz Xolmurodovich' => 'خوجييف عزيز خولمورودوفيتش',
            'Rustamov Bobir Ismatovich' => 'رستاموف بوبير إسماتوفيتش',
            'Ashurov Asrorjon Komilovich' => 'أشوروف أسرورجون كوميلوفيتش',
            'Latipov Saidmurod Tuygunovich' => 'لاتيبوف سعيد مراد تويغونوفيتش',
            'Mirzayev Shamsiddin Rajabovich' => 'ميرزاييف شمس الدين رجبوفيتش',
            "Tojiyev In'omjon Ilhomovich" => 'توجييف إنعام جون إلهوموفيتش',
            'Qazoqov Farxod Farmonovich' => 'قازوقوف فرخود فرمانوفيتش',
            'Xabibov Faxriddin Yusupovich' => 'خبيبوف فخر الدين يوسفوفيتش',
            'O‘rinov Uyg‘un Abdullayevich' => 'أورينوف أوغون عبد اللهيفيتش',
            "O'rinov Uyg'un Abdullayevich" => 'أورينوف أوغون عبد اللهيفيتش',
        ];

        foreach ($names as $english => $arabic) {
            $profileIds = DB::table('staff_profile_translations')
                ->where('locale', 'en')
                ->where('full_name', $english)
                ->pluck('staff_profile_id');

            foreach ($profileIds as $profileId) {
                DB::table('staff_profile_translations')
                    ->where('staff_profile_id', $profileId)
                    ->where('locale', 'ar')
                    ->update([
                        'full_name' => $arabic,
                        'updated_at' => now(),
                    ]);
            }
        }

        Cache::forever('public_content_cache_version', (string) now()->getTimestamp());
    }

    public function down(): void
    {
        // Intentionally not reversible: this migration refines Arabic localized names.
    }
};
