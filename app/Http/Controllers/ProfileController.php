<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class ProfileController extends Controller
{
    public function edit(Request $request): View
    {
        return view('auth.profile', [
            'user' => $request->user(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['nullable', 'email', 'not_regex:/[\r\n]/', 'max:150', 'required_without:phone'],
            'phone' => ['nullable', 'string', 'max:30', 'required_without:email'],
            'avatar' => ['nullable', 'url', 'max:500'],
            'current_password' => ['nullable', 'string'],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
        ], [
            'email.required_without' => 'Isi email atau nomor telepon.',
            'email.not_regex' => 'Email tidak boleh berisi karakter baris baru.',
            'phone.required_without' => 'Isi nomor telepon atau email.',
            'password.confirmed' => 'Konfirmasi password tidak sama.',
            'avatar.url' => 'Foto profil harus berupa URL gambar yang valid.',
        ]);

        $email = $this->normalizeEmail($validated['email'] ?? null);
        $phone = $this->normalizePhone($validated['phone'] ?? null);

        if (! $email && ! $phone) {
            throw ValidationException::withMessages([
                'email' => 'Isi minimal email atau nomor telepon.',
            ]);
        }

        if ($email) {
            $existing = User::query()->where('email', $email)->first();
            if ($existing && (string) $existing->getKey() !== (string) $user->getKey()) {
                throw ValidationException::withMessages([
                    'email' => 'Email sudah dipakai akun lain.',
                ]);
            }
        }

        if ($phone) {
            $existing = User::query()->where('phone', $phone)->first();
            if ($existing && (string) $existing->getKey() !== (string) $user->getKey()) {
                throw ValidationException::withMessages([
                    'phone' => 'Nomor telepon sudah dipakai akun lain.',
                ]);
            }
        }

        $updates = [
            'name' => $validated['name'],
            'email' => $email,
            'phone' => $phone,
            'avatar' => $validated['avatar'] ?? null,
        ];

        if (! empty($validated['password'])) {
            if ($user->login_provider !== 'google' && ! Hash::check((string) $request->input('current_password'), (string) $user->password)) {
                throw ValidationException::withMessages([
                    'current_password' => 'Password lama tidak sesuai.',
                ]);
            }

            $updates['password'] = Hash::make($validated['password']);
            $updates['login_provider'] = 'local';
        }

        $user->forceFill($updates)->save();

        return redirect()->route('profile.edit')->with('status', 'Profil berhasil diperbarui.');
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
}
