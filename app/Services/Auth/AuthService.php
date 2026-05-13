<?php

namespace App\Services\Auth;

use App\Models\AllowedEmail;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthService
{
    public function isEmailAllowed(string $email): bool
    {
        $domain = substr(strrchr($email, "@"), 1);
        return AllowedEmail::where('email', $email)
            ->orWhere('email', 'like', '%@' . $domain)
            ->exists();
    }

    public function register(array $data): User
    {
        if (!$this->isEmailAllowed($data['email'])) {
            throw ValidationException::withMessages([
                'email' => ['Email is invalid'],
            ]);
        }

        $user = User::create([
            'name'      => $data['name'],
            'email'     => $data['email'],
            'password'  => Hash::make($data['password']),
            'role_id'   => Role::where('name', 'user')->first()?->id,
        ]);

        $user->sendEmailVerificationNotification();

        return $user;
    }

    public function incrementFailedAttempts(User $user): void
    {
        $user->increment('access_failed_count');

        if ($user->access_failed_count >= 3) {
            $user->update([
                'lockout_end' => now()->addMinutes(5),
            ]);
        }
    }

    public function resetFailedAttempts(User $user): void
    {
        $user->update([
            'access_failed_count' => 0,
            'lockout_end'         => null,
        ]);
    }
}