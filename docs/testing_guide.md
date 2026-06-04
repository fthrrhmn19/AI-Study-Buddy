# Testing Guide - AI Study Buddy

Panduan lengkap untuk menguji semua fitur AI Study Buddy menggunakan browser dan Postman.

## Persiapan

1. Pastikan MongoDB sudah berjalan (lokal atau Atlas).
2. Pastikan file `.env` sudah diisi dengan benar.
3. Jalankan server Laravel:

```bash
php artisan serve
```

4. Buka browser di `http://localhost:8000`.

---

## 1. Browser Testing

### 1.1 Login dan Register

- Buka `/register`.
- Daftar dengan nama, email atau nomor telepon, dan password minimal 8 karakter.
- Setelah login, buka `/profile`.
- Ubah nama, email/nomor telepon, atau URL foto profil lalu klik **Simpan Profil**.
- Pastikan tombol **Keluar** tersedia di navbar/menu dan di halaman profil.
- Logout, lalu buka `/login`.
- Login ulang memakai email atau nomor telepon yang sudah didaftarkan.
- Tombol Google akan aktif setelah `GOOGLE_CLIENT_ID` dan `GOOGLE_CLIENT_SECRET` diisi di `.env`.
- Untuk Google OAuth lokal, daftarkan dua Authorized redirect URI di Google Cloud:
  `http://localhost:8000/auth/google/callback` dan `http://127.0.0.1:8000/auth/google/callback`.

### 1.2 Dashboard

- Buka `http://localhost:8000`.
- Sebagai guest, pastikan statistik `Materi Tersimpan` dan `Riwayat AI` bernilai 0 setelah refresh.
- Setelah login, pastikan statistik muncul sesuai data akun (Materi Tersimpan, Riwayat AI, Provider, Database).
- Pastikan Problem Statement dan Solusi AI tampil.
- Pastikan navigasi ke semua halaman berfungsi.

### 1.3 Input Materi Manual

- Isi Judul: `Konsep Dasar AI`
- Isi Topik: `Kecerdasan Buatan`
- Isi Materi: (tempel materi panjang minimal 40 karakter)
- Klik **Ringkasan** -> Pastikan ringkasan muncul di Hasil AI.
- Klik **Kuis** -> Pastikan soal pilihan ganda muncul.
- Klik **Rencana Belajar** -> Pastikan jadwal belajar muncul.

### 1.4 Halaman Ringkasan (`/ai/summarize`)

- Buka route ini dan pastikan tampilannya sama dengan workspace utama.
- Isi form dan klik **Ringkasan**.
- Pastikan loading spinner muncul saat memproses.
- Pastikan hasil ringkasan tampil rapi.

### 1.5 Halaman Quiz (`/ai/quiz`)

- Buka route ini dan pastikan tampilannya sama dengan workspace utama.
- Isi form dan klik **Kuis**.
- Pastikan soal muncul dengan format: pertanyaan, pilihan, jawaban, penjelasan.
- Di input chat, coba prompt `buat 10 pilihan ganda dan 5 essay`; pastikan hasil memuat tepat 10 PG dan 5 essay.

### 1.6 Halaman Study Plan (`/ai/study-plan`)

- Buka route ini dan pastikan tampilannya sama dengan workspace utama.
- Isi form, lalu klik **Rencana Belajar**.
- Pastikan jadwal per hari muncul.

### 1.7 Halaman Riwayat (`/ai/history`)

- Login terlebih dahulu. Guest tetap bisa memakai fitur AI, tetapi tidak mendapat halaman/penyimpanan riwayat.
- Pastikan hasil AI dari akun yang sedang login muncul.
- Coba filter berdasarkan tipe (Ringkasan, Quiz, Rencana Belajar, Chat Material).
- Klik **Lanjutkan** pada salah satu riwayat, lalu kirim pertanyaan lanjutan. Pastikan prompt dan jawaban sebelumnya tetap muncul setelah refresh.

### 1.8 Upload Materi (`/documents/upload`)

- Pilih file TXT/PDF/DOCX.
- Isi judul dan topik.
- Klik Upload & Ekstrak Teks.
- Pastikan teks mentah dan bab terdeteksi muncul.
- Jika file sangat panjang, aplikasi akan memadatkan konteks sebelum dikirim ke Groq. Untuk hasil paling detail, pilih bab tertentu atau gunakan materi yang lebih fokus.

### 1.9 Chat Material (`/documents/chat`)

- Pilih dokumen yang sudah diupload.
- Pilih bab (opsional).
- Ketik pertanyaan atau gunakan Quick Prompts.
- Pastikan jawaban AI muncul di chat.
- Coba klik **Ringkasan Otomatis** dan **Quiz Otomatis**.

---

## 2. Postman Testing

Import `docs/postman_collection.json` ke Postman.

### 2.1 Health Check

```
GET http://localhost:8000/api/health
```

Expected: Status 200, response JSON berisi status "ok".

### 2.2 Create Material

```
POST http://localhost:8000/api/materials
Content-Type: application/json

{
  "title": "Konsep Dasar AI",
  "subject": "Kecerdasan Buatan",
  "content": "Artificial Intelligence adalah cabang ilmu komputer..."
}
```

Expected: Status 201, data material tersimpan. **Catat `_id` untuk dipakai di request selanjutnya.**

### 2.3 List Materials

```
GET http://localhost:8000/api/materials
```

Expected: Status 200, array berisi materi.

### 2.4 AI Summarize

```
POST http://localhost:8000/api/ai/summarize
Content-Type: application/json

{
  "title": "Konsep Dasar AI",
  "subject": "Kecerdasan Buatan",
  "content": "...(materi panjang)...",
  "save": true
}
```

Expected: Status 200, ringkasan dari Groq AI.

### 2.5 AI Quiz

```
POST http://localhost:8000/api/ai/quiz
Content-Type: application/json

{
  "title": "Konsep Dasar AI",
  "subject": "Kecerdasan Buatan",
  "content": "...(materi panjang minimal 40 karakter)...",
  "instruction": "buat 10 pilihan ganda dan 5 essay",
  "total_questions": 15,
  "question_type": "campuran",
  "multiple_choice_count": 10,
  "essay_count": 5
}
```

Expected: Status 200, array `questions` berisi tepat 10 soal `multiple_choice` dan 5 soal `essay`.

### 2.6 AI Study Plan

```
POST http://localhost:8000/api/ai/study-plan
Content-Type: application/json

{
  "title": "Konsep Dasar AI",
  "subject": "Kecerdasan Buatan",
  "content": "...",
  "deadline": "2026-06-09"
}
```

Expected: Status 200, rencana belajar bertahap.

### 2.7 AI Chat Manual

```
POST http://localhost:8000/api/ai/chat
Content-Type: application/json

{
  "content": "Artificial Intelligence membantu mahasiswa belajar dengan merangkum materi, membuat latihan soal, menjawab pertanyaan berdasarkan catatan, dan menyusun rencana belajar bertahap.",
  "message": "Apa manfaat utama materi ini?",
  "history": []
}
```

Expected: Status 200, jawaban AI berdasarkan isi materi manual.

Jika `message` berisi permintaan kuis, contoh `buat 10 pilihan ganda dan 5 essay`, endpoint ini otomatis mengembalikan format kuis terstruktur.

### 2.8 AI History

```
GET http://localhost:8000/api/ai/history
```

Expected: Status 200 jika Postman membawa session cookie user yang sudah login. Tanpa login, endpoint riwayat mengembalikan 401/redirect login.

```
GET http://localhost:8000/api/ai/history/<HISTORY_ID>
```

Expected: Status 200, detail prompt, hasil AI, dan konteks sesi.

```
POST http://localhost:8000/api/ai/history/<HISTORY_ID>/continue
Content-Type: application/json

{
  "message": "Lanjutkan dengan 5 soal essay tambahan"
}
```

Expected: Status 200, jawaban baru tersimpan sebagai lanjutan riwayat.

### 2.9 Upload Document

```
POST http://localhost:8000/api/documents
Content-Type: multipart/form-data

title: Materi AI
subject: Kecerdasan Buatan
file: (pilih file .txt/.pdf/.docx)
```

Expected: Status 201, data dokumen dengan extracted_text dan chapters. **Catat `_id`.**

### 2.10 Chat with Document

```
POST http://localhost:8000/api/documents/<DOCUMENT_ID>/chat
Content-Type: application/json

{
  "message": "Apa poin penting dari materi ini?",
  "chapter": ""
}
```

Expected: Status 200, jawaban AI berdasarkan isi dokumen.

### 2.11 Quiz from Document

```
POST http://localhost:8000/api/documents/<DOCUMENT_ID>/quiz
Content-Type: application/json

{
  "chapter": "",
  "instruction": "buat 10 pilihan ganda dan 5 essay",
  "total_questions": 15,
  "question_type": "campuran",
  "multiple_choice_count": 10,
  "essay_count": 5
}
```

Expected: Status 200, soal dari isi dokumen dengan jumlah sesuai permintaan.

---

## 3. MongoDB Testing

Gunakan MongoDB Compass atau `mongosh` untuk memverifikasi:

1. Buka database `ai_study_buddy`.
2. Cek collection `materials` - data materi manual harus ada.
3. Cek collection `ai_generations` - hasil AI harus tersimpan. Jika ada data lama, aplikasi juga masih membaca `ai_histories`.
4. Cek collection `documents` - file upload harus ada dengan extracted_text.
5. Cek collection `document_chats` - riwayat chat harus tersimpan.
6. Pastikan **tidak ada API key** tersimpan di dalam dokumen.

---

## 4. Checklist Final

- [ ] Dashboard bisa dibuka
- [ ] Input materi manual berfungsi
- [ ] Ringkasan berfungsi
- [ ] Kuis berfungsi
- [ ] Kuis mengikuti jumlah prompt, contoh 10 PG + 5 essay
- [ ] Rencana Belajar berfungsi
- [ ] Riwayat AI bisa diakses
- [ ] Riwayat AI bisa dibuka kembali dan dilanjutkan
- [ ] Upload file materi berfungsi
- [ ] Chat dengan materi berfungsi
- [ ] Ringkas bab tertentu berfungsi
- [ ] Buat soal bab tertentu berfungsi
- [ ] Postman semua endpoint bisa diakses
- [ ] Data masuk ke MongoDB
- [ ] Responsive di HP dan laptop
- [ ] Loading indicator muncul saat memproses
- [ ] Error handling tersedia
