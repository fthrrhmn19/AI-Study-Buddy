# Script Presentasi YouTube AI Study Buddy

## Opening

Assalamualaikum, kami dari tim UTS Kecerdasan Buatan. Project kami bernama **AI Study Buddy**, yaitu aplikasi web berbasis Laravel, MongoDB, dan Groq API untuk membantu mahasiswa belajar lebih cepat.

## Problem Statement

Masalah yang kami angkat adalah mahasiswa sering mendapatkan materi panjang, sedangkan waktu belajar terbatas. Mereka membutuhkan ringkasan materi, latihan soal, dan rencana belajar yang mudah dipahami.

## Solusi

Solusi kami adalah membuat web app yang memiliki 3 fitur AI utama:

1. AI Summarizer untuk meringkas materi.
2. AI Quiz Generator untuk membuat soal pilihan ganda, essay, atau campuran dengan jumlah sesuai prompt.
3. AI Study Plan untuk membuat rencana belajar.

## Teknologi

Aplikasi ini dibuat menggunakan Laravel terbaru, database MongoDB, Groq API sebagai AI provider, Postman untuk testing API, dan source code dipush ke GitHub agar bisa dijalankan di VPS.

## Demo Web

1. Buka dashboard.
2. Masukkan judul, topik, dan materi.
3. Klik Ringkasan.
4. Tampilkan hasil ringkasan AI.
5. Klik Kuis atau ketik `buat 10 pilihan ganda dan 5 essay`.
6. Tampilkan soal pilihan ganda dan essay yang jumlahnya sesuai.
7. Klik Rencana Belajar.
8. Login, buka Riwayat, lalu klik Lanjutkan untuk menunjukkan sesi lama bisa dipakai lagi.

## Demo Postman

1. Test `GET /api/health`.
2. Test `POST /api/materials`.
3. Test `POST /api/ai/summarize`.
4. Test `POST /api/ai/quiz`.
5. Login dulu, lalu test `GET /api/ai/history` dan `POST /api/ai/history/{id}/continue` dengan session/cookie aktif.

## Penutup

Kesimpulannya, AI Study Buddy membantu mahasiswa belajar lebih efisien karena AI benar-benar digunakan dalam alur utama aplikasi. Terima kasih.
