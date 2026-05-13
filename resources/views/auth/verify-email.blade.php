@extends('layouts.auth')

@section('content')
<div class="card auth-card mt-4">
    <div class="auth-header">
        <h3 class="mb-0 fw-bold">Verify Email</h3>
        <p class="mb-0 opacity-75">We have sent a verification link to your email.</p>
    </div>

    <div class="card-body p-5 text-center">

        <div class="mb-4">
            <i class="fas fa-envelope-open-text fa-5x text-primary mb-3"></i>
            <h5 class="fw-semibold">Check Your Email Inbox</h5>
            <p class="text-muted">Click the verification link we sent to <strong>{{ auth()->user()->email }}</strong></p>
        </div>

        @if (session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif

        <div class="mt-4">
            <p class="small text-muted mb-3">Didn't receive the email?</p>
            
            <form method="POST" action="{{ route('verification.send') }}">
                @csrf
                <button type="submit" class="btn btn-outline-primary btn-sm px-4">
                    Resend Verification Link
                </button>
            </form>
        </div>

        <div class="mt-5">
            <a href="{{ route('logout') }}" class="text-muted small">
                <i class="fas fa-sign-out-alt"></i> Logout
            </a>
        </div>
    </div>
</div>
@endsection