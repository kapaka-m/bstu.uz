<?php

namespace Tests\Feature;

use App\Models\Application;
use App\Models\HousingPayment;
use App\Models\HousingRequest;
use App\Models\User;
use App\Services\HousingWorkflowService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class HousingWorkflowTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->travelTo(now()->setDate(2026, 9, 19));
        Storage::fake('local');
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
        });
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('slug');
        });
        Schema::create('role_user', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('role_id');
        });
        Schema::create('student_profiles', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
        });
        Schema::create('applications', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('student_profile_id');
        });
        foreach (['admissions', 'enrollments', 'prikazes'] as $name) {
            Schema::create($name, function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('application_id');
                $table->string('status');
            });
        }
        Schema::create('student_visa_processes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('application_id');
            $table->string('visa_status');
        });
        Schema::create('contracts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('application_id');
            $table->decimal('amount', 12, 2);
            $table->string('currency');
        });
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('contract_id');
            $table->string('payment_type');
            $table->decimal('amount', 12, 2);
            $table->string('currency');
            $table->string('status');
            $table->string('receipt_path')->nullable();
        });
        Schema::create('housing_requests', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('application_id')->unique();
            $table->unsignedBigInteger('student_profile_id');
            $table->boolean('requested')->default(false);
            $table->string('status')->default('NOT_REQUESTED');
            $table->string('preferred_room_type')->nullable();
            $table->text('notes')->nullable();
            $table->text('admin_notes')->nullable();
            $table->unsignedBigInteger('reviewer_id')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();
        });
        (require database_path('migrations/2026_09_19_000003_add_housing_payment_requirements.php'))->up();
        Schema::create('translation_keys', function (Blueprint $table) {
            $table->id();
            $table->string('group');
            $table->string('key');
        });
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('title');
            $table->text('message');
            $table->string('type');
            $table->unsignedBigInteger('related_application_id');
            $table->string('action_url');
            $table->boolean('is_read');
            $table->timestamps();
        });
        DB::table('users')->insert([['id' => 1, 'name' => 'Student'], ['id' => 2, 'name' => 'Other student'], ['id' => 3, 'name' => 'Reviewer']]);
        DB::table('roles')->insert([['id' => 1, 'slug' => 'student'], ['id' => 2, 'slug' => 'apanel']]);
        DB::table('role_user')->insert([['user_id' => 1, 'role_id' => 1], ['user_id' => 2, 'role_id' => 1], ['user_id' => 3, 'role_id' => 2]]);
        DB::table('student_profiles')->insert(['id' => 1, 'user_id' => 1]);
        DB::table('applications')->insert(['id' => 1, 'student_profile_id' => 1]);
        foreach (['admissions' => 'ISSUED', 'enrollments' => 'active', 'prikazes' => 'ISSUED'] as $table => $status) {
            DB::table($table)->insert(['application_id' => 1, 'status' => $status]);
        }
        DB::table('student_visa_processes')->insert(['application_id' => 1, 'visa_status' => 'ISSUED']);
        DB::table('contracts')->insert(['id' => 1, 'application_id' => 1, 'amount' => 1000, 'currency' => 'USD']);
        DB::table('payments')->insert(['contract_id' => 1, 'payment_type' => 'contract_advance', 'amount' => 300, 'currency' => 'USD', 'status' => 'APPROVED', 'receipt_path' => 'contract.pdf']);
    }

    private function login(int $id = 1): void
    {
        Sanctum::actingAs(User::findOrFail($id));
    }

    private function submit(array $extra = [])
    {
        return $this->post('/api/v1/applications/1/housing/request', array_merge([
            'pinfl' => '12345678901234', 'month' => '2026-09',
            'file' => UploadedFile::fake()->create('receipt.pdf', 20, 'application/pdf'),
        ], $extra), ['Accept' => 'application/json']);
    }

    public function test_each_academic_requirement_is_enforced_and_exactly_thirty_percent_is_eligible(): void
    {
        $this->login();
        foreach (['admissions', 'enrollments', 'prikazes'] as $table) {
            $status = DB::table($table)->value('status');
            DB::table($table)->update(['status' => 'REVOKED']);
            $this->submit()->assertStatus(422);
            DB::table($table)->update(['status' => $status]);
        }
        DB::table('student_visa_processes')->update(['visa_status' => 'PENDING']);
        $this->submit()->assertStatus(422);
        DB::table('student_visa_processes')->update(['visa_status' => 'ISSUED']);
        foreach ([['amount' => 299.99], ['status' => 'UPLOADED'], ['currency' => 'UZS'], ['receipt_path' => null]] as $invalid) {
            DB::table('payments')->update($invalid);
            $this->submit()->assertStatus(422);
            DB::table('payments')->update(['amount' => 300, 'status' => 'APPROVED', 'currency' => 'USD', 'receipt_path' => 'contract.pdf']);
        }
        $this->assertSame(0, HousingRequest::count());
        $this->submit(['amount' => 0, 'currency' => 'UZS', 'status' => 'APPROVED', 'pinfl_verified' => true])
            ->assertCreated()->assertJsonPath('data.status', 'UNDER_REVIEW')
            ->assertJsonPath('data.start_month', '2026-09-01')
            ->assertJsonPath('data.payments.0.month', '2026-09-01')
            ->assertJsonPath('data.payments.0.amount', '40.00')
            ->assertJsonPath('data.payments.0.currency', 'USD')
            ->assertJsonPath('data.pinfl_verified_at', null);
    }

    public function test_pinfl_receipt_validation_ownership_and_roles(): void
    {
        $this->postJson('/api/v1/applications/1/housing/request', [])->assertUnauthorized();
        $this->login(2);
        $this->submit()->assertNotFound();
        $this->login();
        $this->submit(['pinfl' => '123'])->assertStatus(422);
        $this->submit(['file' => null])->assertStatus(422);
        $this->submit(['file' => UploadedFile::fake()->create('script.html', 1, 'text/html')])->assertStatus(422);
        $this->submit(['month' => '2026-10'])->assertStatus(422);
        $this->assertSame(0, HousingRequest::count());
        $this->submit()->assertCreated();
        $this->submit()->assertStatus(422);
        $payment = HousingPayment::firstOrFail();
        $this->assertSame('40.00', $payment->amount);
        $this->assertArrayNotHasKey('receipt_path', $payment->toArray());
        Storage::disk('local')->assertExists($payment->receipt_path);
        $this->get('/api/v1/applications/1/housing/receipts/'.$payment->id.'/download')->assertOk();
        $this->postJson('/api/v1/apanel/applications-workflow/1/housing/review', ['status' => 'APPROVED'])->assertForbidden();
        $this->login(2);
        $this->getJson('/api/v1/applications/1/housing/receipts/'.$payment->id.'/download')->assertNotFound();
        $this->getJson('/api/v1/apanel/applications-workflow/1/housing/receipts/'.$payment->id.'/download')->assertForbidden();
    }

    public function test_housing_approval_requires_verified_pinfl_and_approved_first_receipt_then_tracks_months(): void
    {
        $this->login();
        $this->submit()->assertCreated();
        $service = app(HousingWorkflowService::class);
        $this->assertFalse($service->snapshot(Application::find(1))['completed']);
        $this->login(3);
        $review = '/api/v1/apanel/applications-workflow/1/housing/review';
        $this->postJson($review, ['status' => 'APPROVED', 'pinfl_verified' => true])->assertStatus(422);
        $payment = HousingPayment::firstOrFail();
        $receiptReview = '/api/v1/apanel/applications-workflow/1/housing/receipts/'.$payment->id.'/review';
        $this->postJson($receiptReview, ['status' => 'APPROVED'])->assertOk();
        $this->postJson($receiptReview, ['status' => 'APPROVED'])->assertStatus(422);
        $this->postJson($review, ['status' => 'APPROVED', 'pinfl_verified' => false])->assertStatus(422);
        DB::table('enrollments')->update(['status' => 'REVOKED']);
        $this->postJson($review, ['status' => 'APPROVED', 'pinfl_verified' => true])->assertStatus(422);
        DB::table('enrollments')->update(['status' => 'active']);
        $this->postJson($review, ['status' => 'APPROVED', 'pinfl_verified' => true])->assertOk();
        $this->assertTrue($service->snapshot(Application::find(1))['completed']);
        $this->travelTo(now()->setDate(2026, 11, 2));
        $snapshot = $service->snapshot(Application::find(1));
        $this->assertTrue($snapshot['completed']);
        $this->assertSame(['2026-10', '2026-11'], $snapshot['due_months']);
        $this->login();
        $url = '/api/v1/applications/1/housing/receipts';
        $upload = fn ($month) => $this->post($url, ['month' => $month, 'file' => UploadedFile::fake()->create('monthly.pdf', 20, 'application/pdf')], ['Accept' => 'application/json']);
        $upload('2026-12')->assertStatus(422);
        $upload('2026-08')->assertStatus(422);
        $upload('2026-09')->assertStatus(422);
        $upload('2026-10')->assertCreated();
        $upload('2026-10')->assertStatus(422);
        $this->login(3);
        $id = HousingPayment::whereDate('month', '2026-10-01')->value('id');
        $this->postJson('/api/v1/apanel/applications-workflow/1/housing/receipts/'.$id.'/review', ['status' => 'REJECTED'])->assertStatus(422);
        $this->postJson('/api/v1/apanel/applications-workflow/1/housing/receipts/'.$id.'/review', ['status' => 'REJECTED', 'rejection_reason' => 'Unreadable'])->assertOk();
        $this->login();
        $upload('2026-10')->assertCreated()->assertJsonPath('data.version', 2);
        $this->assertSame(3, HousingPayment::count());
        $this->login(3);
        $replacement = HousingPayment::where('version', 2)->firstOrFail();
        $this->postJson('/api/v1/apanel/applications-workflow/1/housing/receipts/'.$replacement->id.'/review', ['status' => 'APPROVED'])->assertOk();
        $this->assertSame(['2026-11'], $service->snapshot(Application::find(1))['due_months']);
    }

    public function test_legacy_housing_without_evidence_is_not_completed_and_rejected_request_can_be_corrected(): void
    {
        HousingRequest::create(['application_id' => 1, 'student_profile_id' => 1, 'requested' => true, 'status' => 'APPROVED']);
        $this->assertFalse(app(HousingWorkflowService::class)->snapshot(Application::find(1))['completed']);
        $this->login();
        $this->submit()->assertCreated();
        $this->login(3);
        $id = HousingPayment::first()->id;
        $this->postJson('/api/v1/apanel/applications-workflow/1/housing/receipts/'.$id.'/review', ['status' => 'APPROVED'])->assertOk();
        $this->postJson('/api/v1/apanel/applications-workflow/1/housing/review', ['status' => 'REJECTED', 'admin_notes' => 'Correct PINFL'])->assertOk();
        $this->login();
        $this->submit(['pinfl' => '22345678901234', 'file' => null])->assertCreated();
        $this->assertSame(1, HousingPayment::count());
        $this->assertNull(HousingRequest::first()->pinfl_verified_at);
    }

    public function test_unapproved_housing_does_not_open_later_months_and_missing_receipt_cannot_be_approved(): void
    {
        $this->login();
        $this->submit()->assertCreated();
        $this->travelTo(now()->setDate(2026, 11, 2));
        $service = app(HousingWorkflowService::class);
        $this->assertSame(['2026-09'], $service->snapshot(Application::find(1))['due_months']);
        $this->post('/api/v1/applications/1/housing/receipts', [
            'month' => '2026-10', 'file' => UploadedFile::fake()->create('receipt.pdf', 20, 'application/pdf'),
        ], ['Accept' => 'application/json'])->assertStatus(422);
        $this->login(3);
        $payment = HousingPayment::firstOrFail();
        Storage::disk('local')->delete($payment->receipt_path);
        $this->postJson('/api/v1/apanel/applications-workflow/1/housing/receipts/'.$payment->id.'/review', ['status' => 'APPROVED'])->assertStatus(422);
        $this->postJson('/api/v1/apanel/applications-workflow/1/housing/review', ['status' => 'REJECTED'])->assertOk();
        $this->assertSame([], $service->snapshot(Application::find(1))['due_months']);
    }
}
