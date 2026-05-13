<?php

namespace App\Http\Controllers;

use App\Services\Auth\AuthService;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\LoginRequest;
use Illuminate\Validation\ValidationException;

class LoginController
{
    protected AuthService $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function login(LoginRequest $request): RedirectResponse
    {
        try {
            $user = Auth::getProvider()->retrieveByCredentials([
                'email' => $request->email,
            ]);

            if (!$user || !Auth::getProvider()->validateCredentials($user, ['password' => $request->password])) {
                if ($user) {
                    $this->authService->incrementFailedAttempts($user);
                }

                throw ValidationException::withMessages([
                    'email' => ['Email atau password salah.'],
                ]);
            }

            // if (!$user->hasVerifiedEmail()) {
            //     throw ValidationException::withMessages([
            //         'email' => ['Silakan verifikasi email Anda terlebih dahulu sebelum login.'],
            //     ]);
            // }

            Auth::login($user, $request->filled('remember'));

            $this->authService->resetFailedAttempts($user);

            return redirect()->route('report.index')
                ->with('success', 'Login berhasil.');

        } catch (ValidationException $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', collect($e->errors())->flatten()->first());
        }
    }
}