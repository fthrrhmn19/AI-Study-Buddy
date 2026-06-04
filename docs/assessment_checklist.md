# Checklist Project UTS AI - Final

## Requirement dari PPT

- [x] **Web-based application**: Laravel Blade, responsive, bisa diakses lewat browser.
- [x] **Minimal 1 fitur AI**: Groq API dipakai pada alur utama, bukan dekorasi.
- [x] **Demo fungsional**: form, upload, tombol AI, chat, dan riwayat user login menggunakan API.
- [x] **Kode sumber siap submit**: struktur folder rapi, `.env` tidak dikirim ke GitHub.
- [x] **Problem statement jelas**: masalah user membaca materi panjang dan butuh latihan soal.
- [x] **Tim 1 orang**: Fathur Rohman.

## Pemetaan Penilaian

| Bobot | Aspek | Bukti di Project |
|---:|---|---|
| 30% | Problem Solving | README, halaman `/docs`, problem statement, solusi AI Study Buddy |
| 25% | AI Integration | Summarizer, quiz generator dengan jumlah terkontrol, study plan, chat materi/dokumen dengan Groq |
| 20% | Technical Implementation | Laravel 11, MongoDB, REST API, upload file, Postman, deployment GitHub ke VPS, UI responsive dengan particle/canvas |
| 15% | Presentasi YouTube | `docs/youtube_demo_script.md` + alur demo browser |
| 10% | Kelengkapan Dokumentasi | README, deployment guide, testing guide, collection Postman |

## Fitur yang Wajib Didemokan

1. Dashboard modern responsive.
2. Input materi manual.
3. Upload dokumen TXT/PDF/DOCX/JPG/PNG.
4. Ringkasan AI.
5. Quiz AI pilihan ganda/essay/campuran dengan jumlah sesuai prompt.
6. Rencana belajar AI.
7. Chat dengan materi.
8. Riwayat AI yang bisa dibuka kembali dan dilanjutkan.
9. API health check.
10. Postman collection.

## Catatan Keamanan

- Jangan commit `.env`.
- API key hanya lewat environment variable.
- Upload file dibatasi maksimal 10 MB.
- Untuk deploy VPS, isi `.env` langsung di server dan jangan commit secret ke GitHub.
