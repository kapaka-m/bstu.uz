<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('application_countries', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('code', 3)->nullable()->unique();
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('application_nationalities', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('country_name')->nullable();
            $table->string('code', 3)->nullable()->unique();
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        $pairs = [
            ['Afghanistan', 'Afghan'], ['Albania', 'Albanian'], ['Algeria', 'Algerian'], ['Andorra', 'Andorran'],
            ['Angola', 'Angolan'], ['Argentina', 'Argentinian'], ['Armenia', 'Armenian'], ['Australia', 'Australian'],
            ['Austria', 'Austrian'], ['Azerbaijan', 'Azerbaijani'], ['Bahrain', 'Bahraini'], ['Bangladesh', 'Bangladeshi'],
            ['Belarus', 'Belarusian'], ['Belgium', 'Belgian'], ['Brazil', 'Brazilian'], ['Bulgaria', 'Bulgarian'],
            ['Canada', 'Canadian'], ['China', 'Chinese'], ['Egypt', 'Egyptian'], ['France', 'French'],
            ['Georgia', 'Georgian'], ['Germany', 'German'], ['India', 'Indian'], ['Indonesia', 'Indonesian'],
            ['Iran', 'Iranian'], ['Iraq', 'Iraqi'], ['Italy', 'Italian'], ['Japan', 'Japanese'],
            ['Jordan', 'Jordanian'], ['Kazakhstan', 'Kazakh'], ['Kuwait', 'Kuwaiti'], ['Kyrgyzstan', 'Kyrgyz'],
            ['Malaysia', 'Malaysian'], ['Morocco', 'Moroccan'], ['Pakistan', 'Pakistani'], ['Qatar', 'Qatari'],
            ['Russia', 'Russian'], ['Saudi Arabia', 'Saudi'], ['South Korea', 'South Korean'], ['Tajikistan', 'Tajik'],
            ['Turkey', 'Turkish'], ['Turkmenistan', 'Turkmen'], ['United Arab Emirates', 'Emirati'],
            ['United Kingdom', 'British'], ['United States', 'American'], ['Uzbekistan', 'Uzbek'],
        ];

        foreach ($pairs as $index => [$country, $nationality]) {
            DB::table('application_countries')->insertOrIgnore([
                'name' => $country,
                'code' => null,
                'is_active' => true,
                'sort_order' => $index + 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            DB::table('application_nationalities')->insertOrIgnore([
                'name' => $nationality,
                'country_name' => $country,
                'code' => null,
                'is_active' => true,
                'sort_order' => $index + 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('application_nationalities');
        Schema::dropIfExists('application_countries');
    }
};
