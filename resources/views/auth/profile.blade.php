@extends('layouts.app')

@section('content')
<section class="profile-shell">
    <div class="profile-hero">
        <div class="profile-avatar-xl">
            @if($user->avatar)
                <img src="{{ $user->avatar }}" alt="Foto profil {{ $user->name }}" onerror="this.onerror=null;this.src='{{ asset('assets/images/avatar-user.png') }}';">
            @else
                <img src="{{ asset('assets/images/avatar-user.png') }}" alt="Avatar pengguna" onerror="this.onerror=null;this.src='{{ asset('assets/icons/fallback-illustration.svg') }}';">
            @endif
        </div>
        <div class="profile-hero-copy">
            <span class="auth-kicker">Profil Belajar</span>
            <h1>{{ $user->name }}</h1>
            <p>Lengkapi data akun supaya pengalaman belajar kamu lebih personal saat memakai AI Study Buddy.</p>
            <div class="profile-meta">
                <span><i class="bi bi-person-badge"></i>{{ ucfirst($user->login_provider ?? 'local') }}</span>
                <span><i class="bi bi-clock-history"></i>{{ $user->last_login_at ? $user->last_login_at->format('d M Y H:i') : 'Login pertama' }}</span>
            </div>
        </div>
    </div>

    @if(session('status'))
        <div class="alert alert-success mt-3">{{ session('status') }}</div>
    @endif

    <div class="profile-grid">
        <div class="auth-card profile-form-card">
            <div class="auth-card-header">
                <span class="badge badge-soft mb-2">Data Profil</span>
                <h2>Informasi akun</h2>
                <p>Data ini dipakai untuk identitas akun di navbar dan login email/nomor telepon.</p>
            </div>

            <form method="POST" action="{{ route('profile.update') }}" class="auth-form">
                @csrf
                <div class="mb-3">
                    <label class="form-label" for="name">Nama</label>
                    <input id="name" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $user->name) }}" required>
                    @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label" for="email">Email</label>
                        <input id="email" type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email', $user->email) }}" placeholder="nama@email.com">
                        @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="phone">Nomor Telepon</label>
                        <input id="phone" name="phone" class="form-control @error('phone') is-invalid @enderror" value="{{ old('phone', $user->phone) }}" placeholder="08123456789">
                        @error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
                <div class="form-text mb-3">Isi minimal salah satu: email atau nomor telepon.</div>

                <div class="mb-4">
                    <label class="form-label" for="avatar">Foto Profil URL</label>
                    <input id="avatar" name="avatar" class="form-control @error('avatar') is-invalid @enderror" value="{{ old('avatar', $user->avatar) }}" placeholder="https://example.com/foto.jpg">
                    @error('avatar')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="profile-password-box">
                    <div class="d-flex align-items-center gap-2 mb-2">
                        <i class="bi bi-shield-lock text-primary"></i>
                        <b>Ganti Password</b>
                    </div>
                    <p>Kosongkan bagian ini kalau tidak ingin mengganti password.</p>

                    @if(($user->login_provider ?? 'local') !== 'google')
                        <div class="mb-3">
                            <label class="form-label" for="current_password">Password Lama</label>
                            <input id="current_password" type="password" name="current_password" class="form-control @error('current_password') is-invalid @enderror" autocomplete="current-password">
                            @error('current_password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    @else
                        <div class="alert alert-info py-2">Akun Google bisa langsung membuat password baru tanpa password lama.</div>
                    @endif

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label" for="password">Password Baru</label>
                            <input id="password" type="password" name="password" class="form-control @error('password') is-invalid @enderror" autocomplete="new-password">
                            @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="password_confirmation">Konfirmasi Password Baru</label>
                            <input id="password_confirmation" type="password" name="password_confirmation" class="form-control" autocomplete="new-password">
                        </div>
                    </div>
                </div>

                <button class="btn btn-primary w-100 mt-4" type="submit">
                    <i class="bi bi-save me-1"></i> Simpan Profil
                </button>
            </form>
        </div>

        <aside class="profile-side-card">
            <div class="profile-side-section">
                <span class="badge badge-soft-cyan mb-2">Akses Akun</span>
                <h3>Masuk sebagai</h3>
                <div class="profile-identity">
                    <b>{{ $user->name }}</b>
                    <span>{{ $user->email ?: 'Email belum diisi' }}</span>
                    <span>{{ $user->phone ?: 'Nomor telepon belum diisi' }}</span>
                </div>
            </div>

            <div class="profile-side-section">
                <h3>Aksi cepat</h3>
                <a href="{{ route('ai.history') }}" class="profile-action-link"><i class="bi bi-clock-history"></i>Lihat Riwayat</a>
                <a href="{{ route('ai.summarize') }}" class="profile-action-link"><i class="bi bi-stars"></i>Mulai Ringkasan</a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button class="profile-logout-button" type="submit">
                        <i class="bi bi-box-arrow-right"></i> Keluar dari Akun
                    </button>
                </form>
            </div>
        </aside>
    </div>
</section>
@endsection
