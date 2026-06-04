# AI Study Buddy - Laravel + MongoDB + Groq API

Aplikasi web berbasis AI yang membantu mahasiswa memahami materi kuliah. User dapat memasukkan materi secara manual atau mengunggah file/foto materi, lalu AI akan membantu membuat ringkasan, quiz, rencana belajar, dan menjawab pertanyaan berdasarkan materi tersebut.

## Pengembang

Fathur Rohman

## Problem Statement

Mahasiswa sering kesulitan memahami materi kuliah yang panjang, terutama saat harus membaca buku, file PDF, atau catatan yang banyak. Selain itu, mahasiswa juga membutuhkan latihan soal dan rangkuman cepat untuk membantu proses belajar sebelum ujian.

## Solusi

Membuat web AI Study Buddy yang dapat meringkas materi, membuat soal latihan, membuat rencana belajar, dan menjawab pertanyaan berdasarkan materi yang dimasukkan atau file materi yang diunggah. AI digunakan pada alur utama aplikasi, bukan hanya fitur tambahan.

## Alasan Menggunakan AI

AI digunakan untuk memahami isi materi, merangkum poin penting, membuat soal latihan, dan membantu user bertanya jawab dengan isi dokumen. AI menjadi fitur utama dalam alur aplikasi, bukan hanya fitur tambahan.

## Fitur Utama

1. **Dashboard** - Statistik materi tersimpan, riwayat AI, provider, dan navigasi ke semua fitur. Guest melihat statistik 0 karena materi dan riwayat hanya disimpan setelah login.
2. **Input Materi Manual** - User mengisi judul, topik, dan isi materi lalu bisa langsung membuat Ringkasan, Kuis, atau Rencana Belajar.
3. **AI Summarizer** - Ringkas materi panjang jadi poin-poin penting menggunakan Groq AI.
4. **AI Quiz Generator** - Buat soal pilihan ganda, essay, atau campuran dengan jumlah terkontrol, lengkap jawaban dan pembahasan.
5. **AI Study Plan** - Rencana belajar bertahap otomatis berdasarkan materi dan deadline.
6. **Riwayat AI (History)** - Hasil ringkasan, kuis, rencana belajar, dan chat tersimpan di MongoDB khusus untuk user yang sudah login, bisa dibuka kembali, dan bisa dilanjutkan dari sesi sebelumnya.
7. **Upload Materi** - Upload PDF, DOCX, TXT, atau gambar. Sistem mengekstrak teks otomatis dan mendeteksi bab. Upload guest hanya sementara di halaman aktif; login diperlukan untuk menyimpan dokumen dan riwayat.
8. **Chat Material** - Chat langsung dengan isi dokumen. Bisa ringkas per bab, buat soal per bab, atau tanya jawab.
9. **Login/Register** - Akun user berbasis MongoDB, login email/password, nomor telepon/password, dan Google OAuth.
10. **Profil Akun** - User bisa mengubah nama, email, nomor telepon, avatar URL, dan password.
11. **Logo dan Asset Profesional** - Logo, favicon, avatar AI/user, ilustrasi hero, dan card fitur memakai asset di `public/assets/images` dengan fallback SVG agar tidak error 404.
12. **Dokumentasi** - Halaman dokumentasi endpoint, testing, Postman, dan instruksi deploy.
13. **Landing Page Modern** - Hero AI dengan particle ringan, ilustrasi robot profesional, glassmorphism, card interaktif, dan tampilan responsive.

## Teknologi yang Digunakan

| Teknologi | Keterangan |
|---|---|
| Laravel | Framework backend PHP |
| MongoDB | Database NoSQL |
| Groq API | AI Provider (model LLaMA) |
| Postman | Testing API |
| GitHub + VPS | Source control dan deployment server |
| Bootstrap 5 | CSS Framework |
| Blade | Template Engine Laravel |
| Canvas + CSS Animation | Particle background, mouse glow, dan interaksi visual ringan |
| SweetAlert2 | Alert UX yang lebih modern |
| Custom PNG/SVG Assets | Logo, favicon, avatar chat, empty state, dan ilustrasi fitur |

## Instalasi Lokal

Pastikan environment lokal memiliki PHP 8.2+, Composer, ekstensi PHP `mongodb`, `mbstring`, `openssl`, `fileinfo`, `xml`, `dom`, `zip`, dan MongoDB lokal/Atlas.

```bash
# 1. Clone repository
git clone <URL_REPOSITORY>
cd ai-study-buddy-laravel

# 2. Install dependensi
composer install

# 3. Copy environment file
cp .env.example .env

# 4. Generate application key
php artisan key:generate

# 5. Isi konfigurasi di .env (lihat bagian Konfigurasi .env)

# 6. Jalankan server
php artisan serve

# 7. Buka di browser
# http://localhost:8000
```

### URL Lokal yang Lebih Rapi

Saat memakai `php artisan serve`, buka aplikasi lewat `http://localhost:8000` agar address bar tidak memakai IP angka. Itu tetap URL development. Untuk demo dengan URL seperti website biasa, gunakan salah satu opsi berikut:

- Deploy ke VPS/domain server, lalu buka domain deploy.
- Buat local domain seperti `ai-study-buddy.test` di Windows/XAMPP. Panduan lengkap ada di `docs/local-domain.md`.

## Konfigurasi .env

```env
APP_NAME="AI Study Buddy"
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_URL=http://localhost:8000
SESSION_DRIVER=file
SESSION_LIFETIME=120
CACHE_STORE=file

DEVELOPER_NAME="Fathur Rohman"
TEAM_MEMBER_1="Fathur Rohman"
TEAM_MEMBER_2=

DB_CONNECTION=mongodb
MONGODB_URI="mongodb://127.0.0.1:27017/ai_study_buddy"
MONGODB_DATABASE=ai_study_buddy

GROQ_API_KEY="isi_api_key_groq_kamu"
GROQ_BASE_URL="https://api.groq.com/openai/v1"
GROQ_MODEL="llama-3.3-70b-versatile"
YOU_API_KEY="opsional_jika_pakai_web_search"

GOOGLE_CLIENT_ID=
GOOGLE_CLIENT_SECRET=
GOOGLE_REDIRECT_URI=http://localhost:8000/auth/google/callback

AI_HISTORY_COLLECTION=ai_generations
AI_HISTORY_LEGACY_COLLECTION=ai_histories
```

> **Keamanan:** API key Groq hanya disimpan di file `.env` dan tidak boleh di-commit ke GitHub. File `.env` sudah masuk `.gitignore`.

### Konfigurasi Google Login

1. Buka Google Cloud Console, buat OAuth Client ID tipe Web Application.
2. Tambahkan Authorized redirect URI lokal:
   - `http://localhost:8000/auth/google/callback`
   - `http://127.0.0.1:8000/auth/google/callback`
3. Isi `GOOGLE_CLIENT_ID` dan `GOOGLE_CLIENT_SECRET` di `.env`.
4. Untuk deploy di VPS, ganti redirect URI menjadi `https://domain-vps-kamu/auth/google/callback`.

Jika aplikasi dibuka dari `127.0.0.1`, Laravel akan mengirim callback `127.0.0.1`. Jika dibuka dari `localhost`, Laravel akan mengirim callback `localhost`. Keduanya harus didaftarkan di Google Cloud agar tidak muncul error `redirect_uri_mismatch`.

## Endpoint API (Postman)

Import file `docs/postman_collection.json` ke Postman.

### Health Check

| Method | Endpoint | Fungsi |
|---|---|---|
| GET | `/api/health` | Cek API aktif |

### Materi Manual

| Method | Endpoint | Fungsi |
|---|---|---|
| GET | `/api/materials` | Daftar materi |
| POST | `/api/materials` | Simpan materi baru |
| GET | `/api/materials/{id}` | Detail materi |
| DELETE | `/api/materials/{id}` | Hapus materi |

### AI Feature

| Method | Endpoint | Fungsi |
|---|---|---|
| POST | `/api/ai/summarize` | Ringkas materi dengan Groq AI |
| POST | `/api/ai/quiz` | Buat quiz otomatis |
| POST | `/api/ai/study-plan` | Buat rencana belajar |
| POST | `/api/ai/chat` | Chat tentang isi materi manual |
| GET | `/api/ai/web-search` | Web enrichment opsional dari You.com |
| GET | `/api/ai/history` | Riwayat penggunaan AI user login |
| GET | `/api/ai/history/{id}` | Detail satu riwayat AI user login |
| POST | `/api/ai/history/{id}/continue` | Lanjutkan sesi dari riwayat sebelumnya |
| DELETE | `/api/ai/history/{id}` | Hapus satu riwayat AI user login |
| DELETE | `/api/ai/history/all` | Hapus semua riwayat AI user login |

### Upload Dokumen

| Method | Endpoint | Fungsi |
|---|---|---|
| GET | `/api/documents` | Daftar dokumen |
| POST | `/api/documents` | Upload & ekstrak teks dari file |
| GET | `/api/documents/{id}` | Detail dokumen |
| DELETE | `/api/documents/{id}` | Hapus dokumen |

### Chat Dokumen

| Method | Endpoint | Fungsi |
|---|---|---|
| POST | `/api/documents/{id}/chat` | Chat dengan isi dokumen |
| POST | `/api/documents/{id}/summarize` | Ringkas dokumen/bab |
| POST | `/api/documents/{id}/quiz` | Buat quiz dari dokumen/bab |
| POST | `/api/documents/{id}/study-plan` | Buat rencana belajar dari dokumen/bab |
| GET | `/api/documents/{id}/history` | Riwayat chat dokumen user login |

## Contoh Body API

### POST /api/ai/summarize

```json
{
  "title": "Konsep Dasar AI",
  "subject": "Kecerdasan Buatan",
  "content": "Artificial Intelligence adalah cabang ilmu komputer yang membuat mesin mampu melakukan tugas yang biasanya membutuhkan kecerdasan manusia...",
  "save": true
}
```

### POST /api/documents (Form-Data)

```
title: "Materi AI"
subject: "Kecerdasan Buatan"
file: (pilih file .pdf/.docx/.txt)
```

### POST /api/ai/chat

```json
{
  "content": "Artificial Intelligence membantu mahasiswa belajar dengan merangkum materi, membuat latihan soal, menjawab pertanyaan berdasarkan catatan, dan menyusun rencana belajar bertahap.",
  "message": "Apa manfaat utama materi ini?",
  "history": []
}
```

Jika pesan chat berisi permintaan kuis seperti `buat 10 pilihan ganda dan 5 essay`, backend otomatis memakai generator kuis terstruktur agar jumlah soal sesuai.

### POST /api/ai/quiz

```json
{
  "title": "Konsep Dasar AI",
  "subject": "Kecerdasan Buatan",
  "content": "Artificial Intelligence adalah cabang ilmu komputer yang membuat mesin mampu melakukan tugas yang biasanya membutuhkan kecerdasan manusia...",
  "instruction": "buat 10 pilihan ganda dan 5 essay",
  "total_questions": 15,
  "question_type": "campuran",
  "multiple_choice_count": 10,
  "essay_count": 5
}
```

### POST /api/ai/history/{id}/continue

```json
{
  "message": "Lanjutkan dengan 5 soal essay tambahan"
}
```

Endpoint ini hanya tersedia untuk user login dan memakai konteks riwayat yang sudah tersimpan.

## Catatan Dokumen Besar dan Limit Groq

File besar tetap bisa diupload selama ukuran file sesuai validasi aplikasi. Untuk menjaga request tetap masuk limit Groq, isi dokumen dan riwayat chat dipadatkan otomatis sebelum dikirim ke AI. Jika dokumen sangat panjang, pilih bab tertentu atau gunakan materi yang lebih fokus agar hasil ringkasan/kuis lebih detail dan tidak terkena limit token per menit.

### POST /api/documents/{id}/chat

```json
{
  "message": "Ringkas bab 1",
  "chapter": "Bab 1"
}
```

## Deployment GitHub ke VPS

Project ini disiapkan untuk dipush ke GitHub, lalu diclone/pull oleh server VPS. File `.env` tidak ikut GitHub. Secret production dibuat dan diisi langsung di VPS.

Ringkas:

```bash
git clone https://github.com/fthrrhmn19/AI-Study-Buddy.git
cd AI-Study-Buddy
composer install --no-dev --optimize-autoloader
cp .env.production.example .env
php artisan key:generate
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

Document root web server harus mengarah ke folder `public`. Panduan lengkap ada di `DEPLOYMENT_GUIDE.md`.

## Testing

Lihat panduan lengkap di `docs/testing_guide.md`.

## Database MongoDB

| Collection | Keterangan |
|---|---|
| `materials` | Materi manual (title, topic, content) |
| `ai_generations` | Hasil AI (material_id, type, prompt, result, provider, model, conversation_id, parent_id, source_context) |
| `ai_histories` | Collection lama yang masih dibaca untuk kompatibilitas data sebelumnya |
| `documents` | Dokumen upload (title, subject, file_name, file_type, extracted_text, chapters) |
| `document_chats` | Chat dokumen (document_id, user_message, ai_response, action_type, provider, model) |

## Struktur Folder Penting

```text
app/Http/Controllers/Api/AiController.php
app/Http/Controllers/Api/MaterialController.php
app/Http/Controllers/Api/DocumentController.php
app/Http/Controllers/WebController.php
app/Models/Material.php
app/Models/AiGeneration.php
app/Models/Document.php
app/Models/DocumentChat.php
app/Services/GroqService.php
app/Services/DocumentTextExtractor.php
resources/views/home.blade.php
resources/views/layouts/app.blade.php
resources/views/ai/summarize.blade.php
resources/views/ai/quiz.blade.php
resources/views/ai/study-plan.blade.php
resources/views/ai/history.blade.php
resources/views/documents/upload.blade.php
resources/views/documents/chat.blade.php
resources/views/docs.blade.php
routes/api.php
routes/web.php
docs/postman_collection.json
docs/testing_guide.md
docs/local-domain.md
.env.example
.env.production.example
```

## Checklist Penilaian UTS

| Bobot | Aspek | Bukti di Project |
|---:|---|---|
| 30% | Problem Solving | Problem statement jelas di README dan halaman `/docs` |
| 25% | AI Integration | Summarizer, quiz, study-plan, chat dokumen, ringkas per bab |
| 20% | Technical Implementation | Laravel, REST API, MongoDB, responsive UI, deploy GitHub ke VPS, particle canvas, dan asset profesional |
| 15% | Presentasi YouTube | Script demo tersedia di `docs/youtube_demo_script.md` |
| 10% | Dokumentasi | README, Postman collection, testing guide, endpoint list |

## Catatan Keamanan

- API key Groq disimpan di `.env`, tidak ditulis langsung di kode.
- API key tidak muncul di frontend dan tidak masuk ke GitHub.
- File `.env` sudah masuk `.gitignore`.
- Semua input user divalidasi oleh Laravel.
- Ukuran file upload dibatasi maksimal 10 MB.
- Dokumen panjang dipadatkan otomatis sebelum dikirim ke Groq agar tidak menampilkan error JSON mentah di UI.


## Catatan Revisi Final UI

Bagian landing page, dashboard, halaman fitur, login/register, dan history sudah dipoles supaya siap dipakai publik:

- Hero kanan menggunakan ilustrasi AI Study Buddy profesional, particle canvas ringan, dan interaksi mouse.
- Halaman Ringkasan, Kuis, Rencana Belajar, dan Riwayat memakai hero khusus dengan avatar AI transparan, chip fitur, dan glass panel yang selaras.
- Dashboard memakai card fitur proporsional, workspace belajar, statistik akun, dan empty state visual.
- Login/register memakai brand visual yang bersih, Google button resmi, serta form email/nomor telepon.
- Tidak memakai emoji sebagai visual utama; ikon hanya dipakai sebagai UI kecil dari Bootstrap Icons.
- Efek otomatis berhenti/diringankan untuk user yang mengaktifkan `prefers-reduced-motion`.

## Checklist Demo Sebelum Dinilai

1. Jalankan `php artisan serve`.
2. Buka `/` dan pastikan hero AI, particle background, dan card fitur tampil rapi di desktop/mobile.
3. Isi materi manual minimal 40 karakter, lalu klik **Ringkasan**.
4. Klik **Kuis** dan cek hasil soal tampil dengan jawaban/pembahasan. Coba juga prompt `buat 10 pilihan ganda dan 5 essay`.
5. Klik **Rencana Belajar** dan cek hasil JSON dirender rapi.
6. Upload file TXT/PDF/DOCX, lalu cek teks masuk ke kolom materi.
7. Coba chat: "jelaskan poin paling penting dari materi ini".
8. Login, buka **Riwayat**, klik **Lanjutkan** pada salah satu hasil, lalu kirim pertanyaan lanjutan untuk memastikan sesi bisa diteruskan setelah refresh.
9. Buka `/api/health` untuk bukti API aktif.
10. Import `docs/postman_collection.json` ke Postman untuk demo endpoint.
11. Pastikan `.env` lokal/VPS punya `MONGODB_URI` dan `GROQ_API_KEY`, tetapi **jangan upload `.env` ke GitHub**.
