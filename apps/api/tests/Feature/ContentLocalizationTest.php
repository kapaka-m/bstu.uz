<?php

namespace Tests\Feature;

use App\Http\Controllers\Api\AdminCrudController;
use App\Http\Resources\LocalizedResource;
use App\Models\StaffProfile;
use Database\Seeders\CompleteContentLocalizationSeeder;
use Database\Seeders\InterfaceTranslationSeeder;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class ContentLocalizationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->assertSame('sqlite', DB::connection()->getDriverName());
        $this->assertSame(':memory:', DB::connection()->getDatabaseName());
        Schema::create('staff_profiles', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->nullable();
            $table->unsignedInteger('department_id')->nullable();
        });
        foreach (['staff_profile' => ['full_name', 'position', 'bio', 'office'], 'department' => ['name'], 'course' => ['name', 'description'], 'translation_key' => ['value']] as $resource => $fields) {
            $tableName = $resource === 'translation_key' ? 'translation_values' : $resource.'_translations';
            Schema::create($tableName, function (Blueprint $table) use ($resource, $fields) {
                $table->id();
                $table->unsignedInteger($resource.'_id');
                $table->string('locale');
                foreach ($fields as $field) {
                    $table->text($field)->nullable();
                }
                $table->timestamps();
            });
        }
    }

    private function seedBrokenContent(): void
    {
        DB::table('staff_profiles')->insert(['id' => 1, 'department_id' => 1]);
        foreach (['en', 'uz', 'ru', 'ar'] as $locale) {
            DB::table('department_translations')->insert(['department_id' => 1, 'locale' => $locale, 'name' => match ($locale) {
                'uz' => 'Texnologik mashinalar va jihozlar', 'ru' => 'Технологические машины и оборудование', 'ar' => 'الآلات والمعدات التكنولوجية', default => 'Technological Machines and Equipment',
            }]);
            DB::table('staff_profile_translations')->insert([
                'staff_profile_id' => 1, 'locale' => $locale, 'full_name' => 'Test Person', 'position' => 'Head of Department',
                'bio' => 'Test Person serves as Head of Department in Technological Machines and Equipment, contributing to academic, methodological, and research development.',
                'office' => 'Monday-Friday 14:00-16:00',
            ]);
            DB::table('course_translations')->insert(['course_id' => 1, 'locale' => $locale, 'name' => 'Control Theory', 'description' => 'Standard course covering topics in Control Theory.']);
            DB::table('translation_values')->insert(['translation_key_id' => 1, 'locale' => $locale, 'value' => 'Search announcements...']);
        }
    }

    public function test_repairs_all_locales_and_serializes_the_requested_staff_language(): void
    {
        $this->seedBrokenContent();
        (new CompleteContentLocalizationSeeder)->run();
        $profile = StaffProfile::with('translations')->findOrFail(1);
        foreach (['en' => 'Head of Department', 'uz' => 'Kafedra mudiri', 'ru' => 'Заведующий кафедрой', 'ar' => 'رئيس القسم'] as $locale => $position) {
            $payload = (new LocalizedResource($profile, $locale))->toArray(request());
            $this->assertSame($locale, $payload['locale']);
            $this->assertSame($position, $payload['data']['position']);
            $this->assertSame($locale === 'ar' ? 'rtl' : 'ltr', $payload['direction']);
            if ($locale !== 'en') {
                $this->assertStringNotContainsString(' serves as ', $payload['data']['bio']);
                $this->assertStringNotContainsString('Monday', $payload['data']['office']);
                $course = DB::table('course_translations')->where('locale', $locale)->first();
                $this->assertStringNotContainsString('Standard course', $course->description);
                $this->assertStringContainsString($course->name, $course->description);
                $this->assertNotSame('Search announcements...', DB::table('translation_values')->where('locale', $locale)->value('value'));
            }
        }
        $this->artisan('content:audit-translations')->assertSuccessful();
    }

    public function test_repair_is_idempotent_and_preserves_editorial_content(): void
    {
        $this->seedBrokenContent();
        DB::table('staff_profile_translations')->where('locale', 'uz')->update(['position' => 'Kafedra mudiri, professor', 'bio' => 'Tahrirlangan tarjimai hol.']);
        $seeder = new CompleteContentLocalizationSeeder;
        $seeder->run();
        $before = DB::table('staff_profile_translations')->get()->toJson();
        $this->travel(2)->seconds();
        $seeder->run();
        $this->assertSame($before, DB::table('staff_profile_translations')->get()->toJson());
        $this->assertSame('Tahrirlangan tarjimai hol.', DB::table('staff_profile_translations')->where('locale', 'uz')->value('bio'));
    }

    public function test_audit_reports_missing_locales_and_known_english_without_writing(): void
    {
        DB::table('translation_values')->insert(['translation_key_id' => 1, 'locale' => 'en', 'value' => 'Search announcements...']);
        DB::table('translation_values')->insert(['translation_key_id' => 1, 'locale' => 'uz', 'value' => 'Search announcements...']);
        $before = DB::table('translation_values')->get()->toJson();
        $this->artisan('content:audit-translations')->assertFailed();
        $this->assertSame($before, DB::table('translation_values')->get()->toJson());
    }

    public function test_staff_cms_rejects_missing_languages_and_copied_english(): void
    {
        $controller = new class extends AdminCrudController
        {
            public function rules(): array
            {
                return $this->getValidationRules('staff');
            }
        };
        $payload = ['translations' => ['en' => ['full_name' => 'Test', 'position' => 'Head of Department']]];
        $this->assertTrue(Validator::make($payload, $controller->rules())->fails());
        foreach (['uz' => 'Kafedra mudiri', 'ru' => 'Заведующий кафедрой', 'ar' => 'رئيس القسم'] as $locale => $position) {
            $payload['translations'][$locale] = ['full_name' => 'Test', 'position' => $position];
        }
        $this->assertFalse(Validator::make($payload, $controller->rules())->fails());
        $payload['translations']['uz']['position'] = 'Head of Department';
        $validator = Validator::make($payload, $controller->rules());
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('translations.uz.position', $validator->errors()->toArray());
    }

    public function test_interface_seed_has_four_languages_and_preserves_editorial_changes(): void
    {
        Schema::create('translation_keys', function (Blueprint $table) {
            $table->id();
            $table->string('group');
            $table->string('key');
            $table->text('description')->nullable();
            $table->boolean('is_system')->default(false);
            $table->timestamps();
        });
        $seeder = new InterfaceTranslationSeeder;
        $seeder->run();
        $keys = DB::table('translation_keys')->count();
        $this->assertGreaterThan(0, $keys);
        foreach (['en', 'uz', 'ru', 'ar'] as $locale) {
            $this->assertSame($keys, DB::table('translation_values')->where('locale', $locale)->count());
        }
        $id = DB::table('translation_values')->where('locale', 'uz')->value('id');
        DB::table('translation_values')->where('id', $id)->update(['value' => 'Tahrirlangan matn']);
        $before = DB::table('translation_values')->orderBy('id')->get()->toJson();
        $this->travel(2)->seconds();
        $seeder->run();
        $this->assertSame($keys, DB::table('translation_keys')->count());
        $this->assertSame($before, DB::table('translation_values')->orderBy('id')->get()->toJson());
    }
}
