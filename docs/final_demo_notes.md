# Catatan Demo Final - AI Study Buddy

## Alur Demo 5 Menit

1. Buka halaman utama dan tunjukkan hero AI, particle, dan dashboard modern.
2. Tunjukkan visual landing page, dashboard, dan card fitur yang memakai asset profesional AI Study Buddy.
3. Masukkan materi manual singkat atau upload file.
4. Klik **Ringkasan** dan jelaskan hasilnya.
5. Klik **Kuis** atau ketik `buat 10 pilihan ganda dan 5 essay`, lalu tunjukkan jumlah soal sesuai prompt.
6. Klik **Rencana Belajar**.
7. Tanyakan sesuatu di chat materi.
8. Login lalu buka halaman riwayat untuk bukti data tersimpan per akun.
9. Klik **Lanjutkan** dari salah satu riwayat dan kirim pertanyaan lanjutan.
10. Tunjukkan Postman `/api/health` dan salah satu endpoint AI.
11. Tutup dengan stack: Laravel, MongoDB, Groq, Bootstrap, Canvas/CSS animation, GitHub, dan VPS.

## Kalimat Penutup Presentasi

Project ini bukan hanya desain, tetapi aplikasi web yang bisa berjalan. AI digunakan sebagai fitur utama untuk membantu user belajar dari materi yang mereka upload atau tulis sendiri.

## Catatan UI Publik

- Navbar, login, register, dashboard, halaman Ringkasan, Kuis, Rencana Belajar, Riwayat, dan Upload memakai gaya visual yang selaras.
- Logo, favicon, avatar AI/user, hero image, feature image, dan empty state berada di `public/assets/images`.
- Semua gambar punya fallback SVG agar layout tetap aman jika asset gagal dimuat.
- Guest tetap bisa memakai fitur AI, tetapi materi upload dan riwayat hanya tersimpan untuk user login.
- Dokumen besar dipadatkan otomatis sebelum dikirim ke Groq; untuk demo hasil paling stabil, gunakan materi fokus atau pilih bab tertentu.
- Google OAuth lokal membutuhkan redirect URI `localhost` dan `127.0.0.1` terdaftar di Google Cloud jika dua alamat itu dipakai bergantian.
