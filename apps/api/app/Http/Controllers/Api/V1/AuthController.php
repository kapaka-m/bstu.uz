<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    use ApiResponse;

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
                && (! $roles->contains('student') || $roles->contains('apanel'));
            $isWrongApanelPortal = $intendedRole === 'apanel' && ! $roles->contains('apanel');

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
        return array_merge($user->toArray(), [
            'roles' => $user->roles()->pluck('slug')->values()->all(),
        ]);
    }

    public function forgotPassword(Request $request)
    {
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
}
