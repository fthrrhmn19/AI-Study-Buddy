@extends('layouts.app')

@section('content')
@php
    $authLogoPath = public_path('assets/images/logo-ai-study-buddy.png');
    $authLogoUrl = asset('assets/images/logo-ai-study-buddy.png').(is_file($authLogoPath) ? '?v='.filemtime($authLogoPath) : '');
@endphp
<section class="auth-shell">
    <div class="auth-visual-panel">
        <div class="auth-logo-large">
            <img src="{{ $authLogoUrl }}" alt="AI Study Buddy logo" onerror="this.onerror=null;this.src='{{ asset('assets/icons/fallback-illustration.svg') }}';">
        </div>
        <span class="auth-kicker">Create Study Account</span>
        <h1>Buat akun belajar yang rapi dan personal.</h1>
        <p>Daftar dengan email atau nomor telepon. Setelah itu kamu bisa langsung memakai fitur AI seperti biasa.</p>
        <div class="auth-feature-list">
            <span><i class="bi bi-file-earmark-arrow-up"></i> Upload File</span>
            <span><i class="bi bi-calendar-check"></i> Study Plan</span>
            <span><i class="bi bi-clock-history"></i> Riwayat</span>
        </div>
    </div>

    <div class="auth-card">
        <div class="auth-card-header">
            <div>
                <span class="badge badge-soft-cyan mb-2">Register</span>
                <h2>Daftar akun baru</h2>
                <p>Gunakan email, nomor telepon, atau lanjutkan dengan Google.</p>
            </div>
        </div>

        @if($errors->has('google'))
            <div class="alert alert-warning">{{ $errors->first('google') }}</div>
        @endif

        <a href="{{ route('auth.google') }}" class="btn-google">
            <span class="google-mark"><img src="{{ asset('assets/img/google-g.svg') }}" alt=""></span>
            Daftar dengan Google
        </a>
        @unless(config('services.google.client_id') && config('services.google.client_secret'))
            <div class="auth-provider-note">
                Login Google butuh OAuth Client ID dan Client Secret, bukan API key AI Studio/Gemini.
            </div>
        @endunless

        <div class="auth-divider"><span>atau isi data berikut</span></div>

        <form method="POST" action="{{ route('register.store') }}" class="auth-form">
            @csrf
            <div class="mb-3">
                <label class="form-label" for="name">Nama</label>
                <input id="name" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" placeholder="Nama lengkap" autocomplete="name" required>
                @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label" for="email">Email</label>
                    <input id="email" type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email') }}" placeholder="nama@email.com" autocomplete="email">
                    @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label" for="phone">Nomor Telepon</label>
                    <input id="phone" name="phone" class="form-control @error('phone') is-invalid @enderror" value="{{ old('phone') }}" placeholder="08123456789" autocomplete="tel">
                    @error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>
            <div class="form-text mb-3">Isi minimal salah satu: email atau nomor telepon.</div>

            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label" for="password">Password</label>
                    <input id="password" type="password" name="password" class="form-control @error('password') is-invalid @enderror" placeholder="Minimal 8 karakter" autocomplete="new-password" required>
                    @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label" for="password_confirmation">Konfirmasi Password</label>
                    <input id="password_confirmation" type="password" name="password_confirmation" class="form-control" placeholder="Ulangi password" autocomplete="new-password" required>
                </div>
            </div>

            <div class="d-flex align-items-center justify-content-between gap-3 mt-4 mb-4">
                <span class="auth-small-note">Data akun disimpan di MongoDB.</span>
                <a href="{{ route('login') }}" class="auth-link">Sudah punya akun?</a>
            </div>

            <button class="btn btn-primary w-100" type="submit">
                <i class="bi bi-person-plus me-1"></i> Buat Akun
            </button>
        </form>
    </div>
</section>
@endsection
