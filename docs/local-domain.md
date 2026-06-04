# Local Domain untuk Demo

Alamat `127.0.0.1:8000` muncul karena aplikasi dijalankan dari server lokal Laravel. Kode Laravel tidak bisa menyembunyikan address bar browser. Agar terlihat seperti URL web biasa, jalankan aplikasi lewat domain lokal atau deploy.

## Opsi 1: Tetap pakai Artisan dengan domain lokal

1. Buka Notepad sebagai Administrator.
2. Buka file:

```text
C:\Windows\System32\drivers\etc\hosts
```

3. Tambahkan baris:

```text
127.0.0.1 ai-study-buddy.test
```

4. Ubah `.env`:

```env
APP_URL=http://ai-study-buddy.test:8000
GOOGLE_REDIRECT_URI=http://ai-study-buddy.test:8000/auth/google/callback
```

5. Jalankan:

```bash
php artisan serve --host=ai-study-buddy.test --port=8000
```

6. Buka:

```text
http://ai-study-buddy.test:8000
```

Catatan: port `:8000` masih muncul karena server tetap berjalan di port 8000.

## Opsi 2: Tanpa port memakai XAMPP VirtualHost

Pakai opsi ini kalau ingin URL benar-benar seperti:

```text
http://ai-study-buddy.test
```

1. Pastikan project berada di folder yang bisa dibaca Apache, misalnya:

```text
D:\ai-study-buddy-laravel
```

2. Buka file XAMPP Apache VirtualHost:

```text
C:\xampp\apache\conf\extra\httpd-vhosts.conf
```

3. Tambahkan:

```apache
<VirtualHost *:80>
    ServerName ai-study-buddy.test
    DocumentRoot "D:/ai-study-buddy-laravel/public"

    <Directory "D:/ai-study-buddy-laravel/public">
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

4. Buka file hosts sebagai Administrator:

```text
C:\Windows\System32\drivers\etc\hosts
```

5. Tambahkan:

```text
127.0.0.1 ai-study-buddy.test
```

6. Ubah `.env`:

```env
APP_URL=http://ai-study-buddy.test
GOOGLE_REDIRECT_URI=http://ai-study-buddy.test/auth/google/callback
```

7. Restart Apache dari XAMPP Control Panel.
8. Jalankan:

```bash
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

9. Buka:

```text
http://ai-study-buddy.test
```

## Google Login

Kalau memakai domain lokal baru, tambahkan redirect URI ini di Google Cloud OAuth Client:

```text
http://ai-study-buddy.test/auth/google/callback
```

Untuk opsi Artisan port 8000, pakai:

```text
http://ai-study-buddy.test:8000/auth/google/callback
```

Jika tetap memakai URL bawaan Laravel, daftarkan juga:

```text
http://localhost:8000/auth/google/callback
http://127.0.0.1:8000/auth/google/callback
```

Gunakan host yang sama saat membuka web dan saat login Google. Membuka web dari `127.0.0.1` tetapi hanya mendaftarkan callback `localhost` akan menyebabkan error `redirect_uri_mismatch`.
