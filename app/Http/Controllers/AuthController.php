<?php

namespace App\Http\Controllers;

use App\Http\Requests\Auth\ForgotPasswordRequest;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\ResetPasswordRequest;
use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    private const RESET_LINK_SENT_MESSAGE = 'If an active employee account matches that login, a reset link has been sent.';

    private const RESET_THROTTLED_MESSAGE = 'Please wait before requesting another password reset link.';

    private const PASSWORD_RESET_MESSAGE = 'Your password has been reset. You can sign in now.';

    private const PASSWORD_RESET_FAILED_MESSAGE = 'This reset link is invalid or has expired.';

    public function index(): View
    {
        return view('auth.login');
    }

    public function login(LoginRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $user = $this->findActiveEmployeeByLogin($validated['login']);

        if (! $user || ! Hash::check($validated['password'], $user->password)) {
            throw ValidationException::withMessages([
                'login' => 'These credentials do not match an active employee account.',
            ]);
        }

        Auth::login($user, $request->boolean('remember'));

        $request->session()->regenerate();

        return redirect()->intended(route('dashboard'));
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    public function forgotPassword(): View
    {
        return view('auth.forgot-password');
    }

    public function sendResetLink(ForgotPasswordRequest $request): RedirectResponse
    {
        $user = $this->findActiveEmployeeByLogin($request->validated('login'));

        if (! $user || blank($user->email)) {
            return back()->with('status', self::RESET_LINK_SENT_MESSAGE);
        }

        $status = Password::sendResetLink([
            'email' => $user->email,
            'is_active' => true,
            'status' => 'verified',
        ]);

        if ($status === Password::RESET_THROTTLED) {
            return back()
                ->withErrors(['login' => self::RESET_THROTTLED_MESSAGE])
                ->withInput($request->only('login'));
        }

        return back()->with('status', self::RESET_LINK_SENT_MESSAGE);
    }

    public function resetPassword(Request $request, string $token): View
    {
        return view('auth.reset-password', [
            'token' => $token,
            'email' => $request->query('email'),
        ]);
    }

    public function updatePassword(ResetPasswordRequest $request): RedirectResponse
    {
        $credentials = [
            ...$request->validated(),
            'is_active' => true,
            'status' => 'verified',
        ];

        $status = Password::reset(
            $credentials,
            function (User $user, string $password): void {
                $user->forceFill([
                    'password' => $password,
                    'remember_token' => Str::random(60),
                ])->save();

                event(new PasswordReset($user));
            }
        );

        if ($status === Password::PASSWORD_RESET) {
            return redirect()
                ->route('login')
                ->with('status', self::PASSWORD_RESET_MESSAGE);
        }

        return back()
            ->withErrors(['email' => self::PASSWORD_RESET_FAILED_MESSAGE])
            ->withInput($request->only('email'));
    }

    private function findActiveEmployeeByLogin(string $login): ?User
    {
        return User::query()
            ->activeEmployee()
            ->where(function ($query) use ($login): void {
                $query
                    ->where('email', $login)
                    ->orWhere('work_email', $login)
                    ->orWhere('username', $login);
            })
            ->first();
    }
}
