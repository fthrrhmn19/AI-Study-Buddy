<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function showLogin(): View
    {
        return view('auth.login');
    }

    public function showRegister(): View
    {
        return view('auth.register');
    }

    public function register(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['nullable', 'email', 'not_regex:/[\r\n]/', 'max:150', 'required_without:phone'],
            'phone' => ['nullable', 'string', 'max:30', 'required_without:email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ], [
            'email.required_without' => 'Isi email atau nomor telepon.',
            'email.not_regex' => 'Email tidak boleh berisi karakter baris baru.',
            'phone.required_without' => 'Isi nomor telepon atau email.',
            'password.confirmed' => 'Konfirmasi password tidak sama.',
        ]);

        $email = $this->normalizeEmail($validated['email'] ?? null);
        $phone = $this->normalizePhone($validated['phone'] ?? null);

        if (! $email && ! $phone) {
            throw ValidationException::withMessages([
                'email' => 'Isi minimal email atau nomor telepon.',
            ]);
        }

        if ($email && User::query()->where('email', $email)->exists()) {
            throw ValidationException::withMessages([
                'email' => 'Email sudah terdaftar.',
            ]);
        }

        if ($phone && User::query()->where('phone', $phone)->exists()) {
            throw ValidationException::withMessages([
                'phone' => 'Nomor telepon sudah terdaftar.',
            ]);
        }

        $user = User::query()->create([
            'name' => $validated['name'],
            'email' => $email,
            'phone' => $phone,
            'password' => Hash::make($validated['password']),
            'login_provider' => 'local',
            'last_login_at' => now(),
        ]);

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('home')->with('status', 'Akun berhasil dibuat. Selamat belajar!');
    }

    public function login(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'login' => ['required', 'string', 'not_regex:/[\r\n]/', 'max:150'],
            'password' => ['required', 'string'],
            'remember' => ['nullable', 'boolean'],
        ], [
            'login.not_regex' => 'Email/nomor telepon tidak boleh berisi karakter baris baru.',
        ]);

        $login = trim($validated['login']);
        $field = filter_var($login, FILTER_VALIDATE_EMAIL) ? 'email' : 'phone';
        $value = $field === 'email' ? $this->normalizeEmail($login) : $this->normalizePhone($login);

        if (! $value || ! Auth::attempt([$field => $value, 'password' => $validated['password']], $request->boolean('remember'))) {
            throw ValidationException::withMessages([
                'login' => 'Email/nomor telepon atau password salah.',
            ])->redirectTo(route('login'));
        }

        $request->session()->regenerate();
        $request->user()?->forceFill(['last_login_at' => now()])->save();

        return redirect()->intended(route('home'))->with('status', 'Berhasil masuk.');
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home')->with('status', 'Kamu sudah keluar.');
    }

    public function redirectToGoogle(): RedirectResponse
    {
        if (! config('services.google.client_id') || ! config('services.google.client_secret')) {
            return redirect()->route('login')->withErrors([
                'google' => 'Google login belum dikonfigurasi. Isi GOOGLE_CLIENT_ID dan GOOGLE_CLIENT_SECRET dari Google Cloud OAuth di .env. API key AI Studio/Gemini tidak bisa dipakai untuk login Google.',
            ]);
        }

        $state = Str::random(40);
        session(['google_oauth_state' => $state]);

        $query = http_build_query([
            'client_id' => config('services.google.client_id'),
            'redirect_uri' => $this->googleRedirectUri(request()),
            'response_type' => 'code',
            'scope' => 'openid email profile',
            'state' => $state,
            'access_type' => 'online',
            'prompt' => 'select_account',
        ]);

        return redirect()->away('https://accounts.google.com/o/oauth2/v2/auth?'.$query);
    }

    public function handleGoogleCallback(Request $request): RedirectResponse
    {
        $expectedState = (string) session('google_oauth_state', '');
        $receivedState = (string) $request->input('state', '');

        if ($expectedState === '' || ! hash_equals($expectedState, $receivedState)) {
            session()->forget('google_oauth_state');

            return redirect()->route('login')->withErrors([
                'google' => 'Sesi Google login tidak valid. Coba ulangi.',
            ]);
        }

        session()->forget('google_oauth_state');

        if (! $request->filled('code')) {
            return redirect()->route('login')->withErrors([
                'google' => 'Google tidak mengirim kode login.',
            ]);
        }

        $tokenResponse = Http::asForm()->post('https://oauth2.googleapis.com/token', [
            'code' => $request->input('code'),
            'client_id' => config('services.google.client_id'),
            'client_secret' => config('services.google.client_secret'),
            'redirect_uri' => $this->googleRedirectUri($request),
            'grant_type' => 'authorization_code',
        ]);

        if ($tokenResponse->failed()) {
            return redirect()->route('login')->withErrors([
                'google' => 'Gagal mengambil token Google.',
            ]);
        }

        $profileResponse = Http::withToken((string) $tokenResponse->json('access_token'))
            ->get('https://www.googleapis.com/oauth2/v3/userinfo');

        if ($profileResponse->failed() || ! $profileResponse->json('email')) {
            return redirect()->route('login')->withErrors([
                'google' => 'Gagal mengambil profil Google.',
            ]);
        }

        $email = $this->normalizeEmail($profileResponse->json('email'));
        $user = User::query()->where('email', $email)->first();

        if (! $user) {
            $user = User::query()->create([
                'name' => $profileResponse->json('name') ?: Str::before($email, '@'),
                'email' => $email,
                'password' => Hash::make(Str::random(48)),
                'google_id' => $profileResponse->json('sub'),
                'avatar' => $profileResponse->json('picture'),
                'login_provider' => 'google',
                'email_verified_at' => now(),
                'last_login_at' => now(),
            ]);
        } else {
            $user->forceFill([
                'google_id' => $profileResponse->json('sub'),
                'avatar' => $profileResponse->json('picture'),
                'email_verified_at' => $user->email_verified_at ?: now(),
                'last_login_at' => now(),
            ])->save();
        }

        Auth::login($user, true);
        $request->session()->regenerate();

        return redirect()->route('home')->with('status', 'Berhasil masuk dengan Google.');
    }

    private function normalizeEmail(?string $email): ?string
    {
        $email = strtolower(trim((string) $email));

        if (preg_match('/[\r\n]/', $email)) {
            return null;
        }

        return $email !== '' ? $email : null;
    }

    private function normalizePhone(?string $phone): ?string
    {
        $phone = preg_replace('/[^\d+]/', '', trim((string) $phone));

        if (! $phone) {
            return null;
        }

        if (str_starts_with($phone, '0')) {
            return '+62'.substr($phone, 1);
        }

        if (str_starts_with($phone, '62')) {
            return '+'.$phone;
        }

        return $phone;
    }

    private function googleRedirectUri(?Request $request = null): string
    {
        $request ??= request();
        $host = $request->getHost();

        if (in_array($host, ['127.0.0.1', 'localhost'], true)) {
            return $request->getScheme().'://'.$request->getHttpHost().'/auth/google/callback';
        }

        return config('services.google.redirect') ?: route('auth.google.callback');
    }
}
