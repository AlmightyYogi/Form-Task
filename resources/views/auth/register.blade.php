@extends('layouts.auth')

@section('content')
<div class="card auth-card mt-4">
    <div class="auth-header">
        <h3 class="mb-0 fw-bold">Register New Account</h3>
        <p class="mb-0 opacity-75">Register for enter the application</p>
    </div>

    <div class="card-body p-4 p-md-5">

        <form method="POST" action="{{ route('register') }}" id="registerForm">
            @csrf

            <div class="mb-4">
                <label class="form-label fw-semibold">Full Name <span class="text-danger">*</span></label>
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-user"></i></span>
                    <input type="text" 
                           name="name" 
                           class="form-control @error('name') is-invalid @enderror" 
                           value="{{ old('name') }}" 
                           placeholder="Full Name"
                           required>
                </div>
                @error('name')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-4">
                <label class="form-label fw-semibold">Email Address <span class="text-danger">*</span></label>
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                    <input type="email" 
                           name="email" 
                           class="form-control @error('email') is-invalid @enderror" 
                           value="{{ old('email') }}" 
                           placeholder="Email"
                           required>
                </div>
                @error('email')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-4">
                <label class="form-label fw-semibold">Password <span class="text-danger">*</span></label>
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-lock"></i></span>
                    <input type="password" 
                           name="password" 
                           class="form-control @error('password') is-invalid @enderror" 
                           placeholder="Password"
                           required>
                </div>
                @error('password')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-4">
                <label class="form-label fw-semibold">Confirmation Password <span class="text-danger">*</span></label>
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-lock"></i></span>
                    <input type="password" 
                           name="password_confirmation" 
                           class="form-control @error('password_confirmation') is-invalid @enderror" 
                           placeholder="Confirmation Password"
                           required>
                </div>
                @error('password_confirmation')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <button type="submit" id="submitBtn" class="btn btn-primary w-100 mb-3">
                <i class="fas fa-user-plus me-2"></i> Register
            </button>
        </form>

        {{-- <div class="text-center mt-4">
            <p class="mb-0">Already have an account? 
                <a href="{{ route('login') }}" class="text-primary fw-semibold">Login Now</a>
            </p>
        </div> --}}
        
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('registerForm');
    const submitBtn = document.getElementById('submitBtn');

    form.addEventListener('submit', function () {
        submitBtn.disabled = true;
        submitBtn.innerHTML = `
            <span class="spinner-border spinner-border-sm me-2" role="status"></span>
            Submitting...
        `;
    });

    @if (session('error'))
        Swal.fire({
            icon: 'error',
            title: 'Registrasi Gagal',
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

    @if ($errors->any())
        Swal.fire({
            icon: 'error',
            title: 'Validasi Gagal',
            html: `{!! implode('<br>', $errors->all()) !!}`,
            confirmButtonColor: '#d33'
        });
    @endif
});
</script>
@endpush