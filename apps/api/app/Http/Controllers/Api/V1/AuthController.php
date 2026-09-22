<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\AuthCmsController;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    use ApiResponse;

    protected array $controlPanelRoleSlugs = [
        'apanel',
        'super_admin',
        'admin',
        'admission_officer',
        'international_office_staff',
        'call_center_staff',
        'faculty_staff',
        'department_staff',
        'registrar_office_staff',
        'dormitory_manager',
        'teacher',
        'finance_staff',
        'document_officer',
        'content_manager',
    ];

    public function register()
    {
        return $this->errorResponse('Direct account registration is disabled. Use the application form.', 410);
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string',
            'intended_role' => 'nullable|string|in:student,apanel',
        ]);

        $user = User::where('email', $request->email)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            return $this->errorResponse('Invalid login credentials.', 401, [
                'email' => ['Invalid credentials.'],
            ]);
        }

        $intendedRole = $request->string('intended_role')->toString();
        if ($intendedRole !== '') {
            $roles = $user->roles()->pluck('slug');
            $isWrongStudentPortal = $intendedRole === 'student'
                && (! $roles->contains('student') || $this->hasControlPanelAccess($roles->all()));
            $isWrongApanelPortal = $intendedRole === 'apanel'
                && ! $this->hasControlPanelAccess($roles->all());

            if ($isWrongStudentPortal || $isWrongApanelPortal) {
                return $this->errorResponse('This account is not allowed to use this login portal.', 403);
            }
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return $this->successResponse([
            'user' => $this->userPayload($user),
            'access_token' => $token,
            'token_type' => 'Bearer',
        ], 'Login successful');
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return $this->successResponse(null, 'Logged out successfully');
    }

    public function user(Request $request)
    {
        return $this->successResponse($this->userPayload($request->user()), 'User details fetched successfully');
    }

    protected function userPayload(User $user): array
    {
        $permissionsAvailable = Schema::hasTable('permissions')
            && Schema::hasTable('permission_role');
        $roles = $permissionsAvailable
            ? $user->roles()->with('permissions')->get()
            : $user->roles()->get();

        return array_merge($user->toArray(), [
            'roles' => $roles->pluck('slug')->values()->all(),
            'role_names' => $roles->pluck('name')->values()->all(),
            'permissions' => $permissionsAvailable
                ? $roles
                    ->flatMap(fn ($role) => $role->permissions->pluck('slug'))
                    ->unique()
                    ->values()
                    ->all()
                : [],
        ]);
    }

    protected function hasControlPanelAccess(array $roles): bool
    {
        return count(array_intersect($roles, $this->controlPanelRoleSlugs)) > 0;
    }

    public function forgotPassword(Request $request)
    {
        $this->setRequestLocale($request);
        $this->setPasswordResetExpiryFromCms();

        $validated = $request->validate([
            'email' => 'required|email',
        ]);

        if (! User::where('email', $validated['email'])->exists()) {
            return $this->errorResponse('auth.emailMustApplyFirst', 404, [
                'email' => ['auth.emailMustApplyFirst'],
            ]);
        }

        Password::sendResetLink($validated);

        return $this->successResponse(null, 'Password reset link sent to your email address.');
    }

    public function resetPassword(Request $request)
    {
        $this->setRequestLocale($request);
        $this->setPasswordResetExpiryFromCms();

        $validated = $request->validate([
            'email' => 'required|email|exists:users,email',
            'password' => 'required|string|min:8|confirmed',
            'token' => 'required|string',
        ]);

        $status = Password::reset($validated, function (User $user, string $password): void {
            $user->forceFill([
                'password' => Hash::make($password),
                'remember_token' => Str::random(60),
            ])->save();
        });

        if ($status !== Password::PASSWORD_RESET) {
            return $this->errorResponse('Invalid or expired password reset token.', 422, [
                'token' => ['Invalid or expired password reset token.'],
            ]);
        }

        return $this->successResponse(null, 'Password reset successfully.');
    }

    protected function setRequestLocale(Request $request): void
    {
        $locale = $request->query('locale') ?: $request->header('Accept-Language');

        if ($locale) {
            $locale = strtolower(trim(explode(',', $locale)[0]));
            if (strlen($locale) > 2 && $locale[2] === '-') {
                $locale = substr($locale, 0, 2);
            }

            app()->setLocale($locale);
        }
    }

    protected function setPasswordResetExpiryFromCms(): void
    {
        $template = AuthCmsController::localizedEmailTemplate('password_reset', app()->getLocale());
        $minutes = (int) ($template['settings']['expire_minutes'] ?? 0);

        if ($minutes <= 0) {
            return;
        }

        config(['auth.passwords.users.expire' => $minutes]);

        if (method_exists(app('auth.password'), 'forgetDrivers')) {
            app('auth.password')->forgetDrivers();
        }
    }
}
