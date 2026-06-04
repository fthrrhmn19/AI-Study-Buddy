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
        <span class="auth-kicker">AI Study Buddy Account</span>
        <h1>Masuk ke ruang belajar AI kamu.</h1>
        <p>Kelola materi, akses riwayat belajar, dan gunakan fitur AI Study Buddy dari satu akun.</p>
        <div class="auth-feature-list">
            <span><i class="bi bi-stars"></i> Ringkasan AI</span>
            <span><i class="bi bi-patch-question"></i> Quiz Generator</span>
            <span><i class="bi bi-chat-square-text"></i> Chat Materi</span>
        </div>
    </div>

    <div class="auth-card">
        <div class="auth-card-header">
            <div>
                <span class="badge badge-soft mb-2">Login</span>
                <h2>Selamat datang kembali</h2>
                <p>Masuk pakai email, nomor telepon, atau akun Google.</p>
            </div>
        </div>

        @if(session('status'))
            <div class="alert alert-success">{{ session('status') }}</div>
        @endif

        @if($errors->has('google'))
            <div class="alert alert-warning">{{ $errors->first('google') }}</div>
        @endif

        <a href="{{ route('auth.google') }}" class="btn-google">
            <span class="google-mark"><img src="{{ asset('assets/img/google-g.svg') }}" alt=""></span>
            Masuk dengan Google
        </a>
        @unless(config('services.google.client_id') && config('services.google.client_secret'))
            <div class="auth-provider-note">
                Login Google butuh OAuth Client ID dan Client Secret, bukan API key AI Studio/Gemini.
            </div>
        @endunless

        <div class="auth-divider"><span>atau masuk manual</span></div>

        <form method="POST" action="{{ route('login.store') }}" class="auth-form">
            @csrf
            <div class="mb-3">
                <label class="form-label" for="login">Email atau Nomor Telepon</label>
                <input id="login" name="login" class="form-control @error('login') is-invalid @enderror" value="{{ old('login') }}" placeholder="nama@email.com atau 08123456789" autocomplete="username" required>
                @error('login')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="mb-3">
                <label class="form-label" for="password">Password</label>
                <input id="password" type="password" name="password" class="form-control @error('password') is-invalid @enderror" placeholder="Masukkan password" autocomplete="current-password" required>
                @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="d-flex align-items-center justify-content-between gap-3 mb-4">
                <label class="auth-check">
                    <input type="checkbox" name="remember" value="1">
                    <span>Ingat saya</span>
                </label>
                <a href="{{ route('register') }}" class="auth-link">Belum punya akun?</a>
            </div>

            <button class="btn btn-primary w-100" type="submit">
                <i class="bi bi-box-arrow-in-right me-1"></i> Masuk
            </button>
        </form>
    </div>
</section>
@endsection
