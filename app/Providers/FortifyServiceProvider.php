<?php

namespace App\Providers;

use App\Services\Auth\AuthService;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Laravel\Fortify\Contracts\LoginResponse;
use Laravel\Fortify\Fortify;
use Illuminate\Support\ServiceProvider;

class FortifyServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->configureLogin();
        $this->configurePasswordReset();
    }

    private function configureLogin(): void
    {
        // $authService = $this->app->make(AuthService::class);

        // Fortify::authenticateUsing(function (Request $request) use ($authService) {
        //     $user = User::where('email', $request->email)->first();

        //     if (!$user) {
        //         return null;
        //     }

        //     // Cek verifikasi email
        //     if (!$user->hasVerifiedEmail()) {
        //         throw ValidationException::withMessages([
        //             Fortify::username() => 'Please verify your email first before login.',
        //         ]);
        //     }

        //     if (!$authService->isEmailAllowed($request->email)) {
        //         throw ValidationException::withMessages([
        //             Fortify::username() => 'Email ini tidak diizinkan untuk login',
        //         ]);
        //     }

        //     if ($user->lockout_end && $user->lockout_end->isFuture()) {
        //         throw ValidationException::withMessages([
        //             Fortify::username() => 'Percobaan terlalu banyak, silahkan menunggu beberapa saat',
        //         ]);
        //     }

        //     if (Hash::check($request->password, $user->password)) {
        //         $authService->resetFailedAttempts($user);
        //         return $user;
        //     }

        //     $authService->incrementFailedAttempts($user);
        //     return null;
        // });

        // $this->app->singleton(LoginResponse::class, function () {
        //     return new class implements LoginResponse {
        //         public function toResponse($request)
        //         {
        //             return redirect()->intended('/reports');
        //         }
        //     };
        // });
    }

    private function configurePasswordReset(): void
    {
        Fortify::requestPasswordResetLinkView(fn() => view('auth.forgot-password'));
        Fortify::resetPasswordView(fn($request) => view('auth.reset-password', ['request' => $request]));
    }
}