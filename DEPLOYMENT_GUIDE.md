# Panduan Deploy GitHub ke VPS

Panduan ini disiapkan untuk alur dosen/server mengambil source code dari GitHub, lalu menjalankan aplikasi di VPS. Secret seperti API key, MongoDB URI, Google OAuth secret, dan APP_KEY tidak boleh masuk GitHub.

## 1. Yang Di-Upload ke GitHub

Upload seluruh source project kecuali file yang berisi secret atau hasil install lokal.

Wajib ikut GitHub:

- `app/`, `bootstrap/`, `config/`, `database/`, `docs/`, `public/`, `resources/`, `routes/`, `tests/`
- `composer.json`
- `composer.lock`
- `.env.example`
- `.env.production.example`
- `README.md`
- `DEPLOYMENT_GUIDE.md`

Wajib tidak ikut GitHub:

- `.env`
- `.env.*` selain `.env.example` dan `.env.production.example`
- `vendor/`
- `node_modules/`
- `storage/logs/*.log`
- file backup berisi secret

## 2. Persiapan VPS

Pastikan VPS memiliki:

- PHP 8.2 atau lebih baru
- Composer
- ekstensi PHP: `mongodb`, `mbstring`, `openssl`, `fileinfo`, `xml`, `dom`, `zip`, `curl`
- web server Nginx atau Apache
- MongoDB lokal atau MongoDB Atlas
- akses internet keluar untuk Groq API dan Google OAuth

## 3. Clone dari GitHub

```bash
cd /var/www
git clone https://github.com/fthrrhmn19/AI-Study-Buddy.git
cd AI-Study-Buddy
composer install --no-dev --optimize-autoloader
```

Jika dosen memakai branch lain, sesuaikan:

```bash
git checkout main
git pull origin main
```

## 4. Buat File Environment di VPS

Jangan ambil `.env` dari GitHub. Buat langsung di server:

```bash
cp .env.production.example .env
php artisan key:generate
```

Lalu edit `.env` di VPS:

```env
APP_NAME="AI Study Buddy"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://domain-vps-kamu.example

SESSION_DRIVER=file
SESSION_LIFETIME=120
SESSION_SECURE_COOKIE=true
SESSION_SAME_SITE=lax
CACHE_STORE=file

DB_CONNECTION=mongodb
MONGODB_URI="mongodb://127.0.0.1:27017/ai_study_buddy"
MONGODB_DATABASE=ai_study_buddy

GROQ_API_KEY="isi_di_server_vps"
GROQ_BASE_URL="https://api.groq.com/openai/v1"
GROQ_MODEL="llama-3.3-70b-versatile"

GOOGLE_CLIENT_ID=
GOOGLE_CLIENT_SECRET=
GOOGLE_REDIRECT_URI=https://domain-vps-kamu.example/auth/google/callback
```

Untuk MongoDB Atlas, ganti `MONGODB_URI` dengan URI Atlas. Untuk MongoDB lokal VPS, pastikan service MongoDB aktif dan database bisa diakses.

## 5. Permission Laravel

```bash
chmod -R ug+rwx storage bootstrap/cache
```

Jika memakai user web server khusus:

```bash
chown -R www-data:www-data storage bootstrap/cache
```

## 6. Cache Production

Jalankan setelah `.env` production sudah benar:

```bash
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

## 7. Konfigurasi Web Server

Document root harus mengarah ke folder `public`, bukan root project.

Contoh Nginx:

```nginx
server {
    listen 80;
    server_name domain-vps-kamu.example;
    root /var/www/AI-Study-Buddy/public;
    index index.php index.html;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:/run/php/php8.2-fpm.sock;
    }

    location ~ /\. {
        deny all;
    }
}
```

Setelah domain aktif, gunakan HTTPS/SSL. Jika sudah HTTPS, `SESSION_SECURE_COOKIE=true` aman dipakai.

## 8. Google OAuth di VPS

Di Google Cloud Console, tambahkan redirect URI production:

```text
https://domain-vps-kamu.example/auth/google/callback
```

Lalu isi:

- `GOOGLE_CLIENT_ID`
- `GOOGLE_CLIENT_SECRET`
- `GOOGLE_REDIRECT_URI`

Jika belum mau memakai Google login, kosongkan `GOOGLE_CLIENT_ID` dan `GOOGLE_CLIENT_SECRET`. Tombol Google akan diarahkan kembali ke login dengan pesan konfigurasi.

## 9. Checklist Setelah Deploy

1. Buka `/api/health`, pastikan status `ok`.
2. Buka `/`, `/login`, `/register`, `/documents/upload`.
3. Register akun test.
4. Coba login dan logout.
5. Input materi manual minimal 40 karakter.
6. Klik Ringkasan, Kuis, dan Rencana Belajar.
7. Upload TXT/PDF/DOCX atau input OCR manual.
8. Jika memakai Google OAuth, klik login Google dan pastikan tidak 500.
9. Cek `storage/logs/laravel.log` jika ada error.
10. Pastikan `.env` tidak pernah dipush ke GitHub.

## 10. Update dari GitHub ke VPS

Saat dosen/server ingin mengambil update terbaru:

```bash
cd /var/www/AI-Study-Buddy
git pull origin main
composer install --no-dev --optimize-autoloader
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

## 11. Catatan Keamanan

- Jangan commit `.env`, `.env.production`, `.env.vps`, atau file secret lain.
- Secret hanya diisi lewat environment VPS.
- `APP_DEBUG=false` wajib untuk production.
- `APP_KEY` production dibuat di server dengan `php artisan key:generate`.
- `CACHE_STORE=file` dipakai agar cache/rate-limit aman dengan MongoDB.
- Upload file dibatasi maksimal 10 MB oleh validasi aplikasi.
