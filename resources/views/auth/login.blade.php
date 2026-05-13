@extends('layouts.auth')

@section('content')
<div class="card auth-card mt-4">
    <div class="auth-header">
        <h3 class="mb-0 fw-bold">Login</h3>
        <p class="mb-0 opacity-75">Log in to your existing account</p>
    </div>

    <div class="card-body p-4 p-md-5">

        @if (session('status'))
            <div class="alert alert-success text-center">{{ session('status') }}</div>
        @endif

        @if (auth()->check() && !auth()->user()->hasVerifiedEmail())
            <div class="alert alert-warning text-center">
                <strong>Email Anda belum diverifikasi!</strong><br>
                Silakan cek inbox email atau 
                <a href="{{ route('verification.notice') }}" class="alert-link">klik di sini</a>.
            </div>
        @endif

        <form method="POST" action="{{ route('login.store') }}" id="loginForm">
            @csrf

            <div class="mb-4">
                <label class="form-label fw-semibold">Email Address <span class="text-danger">*</span></label>
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                    <input type="email" name="email" 
                           class="form-control @error('email') is-invalid @enderror" 
                           value="{{ old('email') }}" 
                           placeholder="Email" required autofocus>
                </div>
                @error('email')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-4">
                <label class="form-label fw-semibold">Password <span class="text-danger">*</span></label>
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-lock"></i></span>
                    <input type="password" name="password" 
                           class="form-control @error('password') is-invalid @enderror" 
                           placeholder="Password" required>
                </div>
                @error('password')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="d-flex justify-content-between align-items-center mb-4">
                <div class="form-check">
                    <input type="checkbox" name="remember" id="remember" class="form-check-input">
                    <label class="form-check-label" for="remember">Remember me</label>
                </div>
                <a href="#" class="text-decoration-none small">Forgot Password?</a>
            </div>

            <button type="submit" id="submitBtn" class="btn btn-primary w-100 mb-3">
                <i class="fas fa-sign-in-alt me-2"></i> Login
            </button>
        </form>

        {{-- <div class="text-center mt-4">
            <p class="mb-0">Don't have an account yet?
                <a href="{{ route('register') }}" class="text-primary fw-semibold">Register Now</a>
            </p>
        </div> --}}
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('loginForm');
    const submitBtn = document.getElementById('submitBtn');

    form.addEventListener('submit', function () {
        submitBtn.disabled = true;
        submitBtn.innerHTML = `
            <span class="spinner-border spinner-border-sm me-2" role="status"></span>
            Logging in...
        `;
    });

    @if (session('error'))
        Swal.fire({
            icon: 'error',
            title: 'Login Gagal',
            text: '{{ session("error") }}',
            confirmButtonColor: '#d33'
        });
    @endif

    @if (session('success'))
        Swal.fire({
            icon: 'success',
            title: 'Berhasil',
            text: '{{ session("success") }}',
            confirmButtonColor: '#3085d6'
        });
    @endif
});
</script>
@endpush