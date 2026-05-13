<?php

namespace App\Http\Controllers;

use App\Services\Auth\AuthService;
use App\Http\Requests\RegisterRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class RegisterController
{
    protected AuthService $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    public function showRegistrationForm(): View
    {
        return view('auth.register');
    }

    public function store(RegisterRequest $request): RedirectResponse
    {
        try {
            $user = $this->authService->register($request->validated());

            return redirect()->route('login')
                ->with('success', 'Registration successful!');

    } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', collect($e->errors())->flatten()->first() ?? 'Something went wrong with the validation.');
        }
    }
}