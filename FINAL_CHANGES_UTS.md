# Perubahan Final UTS — AI Study Buddy

## Yang Sudah Disesuaikan dengan PPT UTS

- Web app Laravel berbasis browser dan responsive.
- Problem statement jelas: mahasiswa kesulitan memahami materi panjang dan membutuhkan ringkasan, kuis, chat materi, serta rencana belajar.
- AI Groq digunakan sebagai fitur inti: summarizer, quiz generator, study plan, chat materi/dokumen.
- Database MongoDB digunakan untuk materi, dokumen, riwayat AI, dan chat dokumen.
- Postman collection tersedia di `docs/postman_collection.json`.
- Dokumentasi tersedia di README, deployment guide, testing guide, assessment checklist, dan demo notes.
- Deployment disiapkan untuk GitHub ke VPS. File `.env` tidak ikut GitHub dan secret diisi langsung di server.

## Upgrade Visual dan UX

- Landing page ditambah **3D interactive hero scene** berbasis Three.js.
- Background ditambah particle canvas ringan.
- Card/statistik ditambah hover tilt interaction.
- UI tetap responsive dan punya fallback untuk reduced motion.
- Visual utama tidak memakai gambar modal/emote; scene dibuat langsung dari kode Three.js.

## File Penting yang Diubah/Ditambah

- `resources/views/layouts/app.blade.php`
- `resources/views/home.blade.php`
- `README.md`
- `.env.example`
- `docs/assessment_checklist.md`
- `docs/final_demo_notes.md`
- `FINAL_CHANGES_UTS.md`

## Cara Menjalankan

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan serve
```

Isi `.env`:

```env
DB_CONNECTION=mongodb
MONGODB_URI=mongodb+srv://...
MONGODB_DATABASE=ai_study_buddy
GROQ_API_KEY=isi_api_key_groq_kamu
GROQ_BASE_URL=https://api.groq.com/openai/v1
GROQ_MODEL=llama-3.3-70b-versatile
```

## Catatan Testing di Environment Ini

- `php -l` untuk file PHP/Blade utama berhasil tanpa syntax error.
- `php artisan route:list` berhasil dan menampilkan 33 route.
- `php artisan test` berhasil: 1 test passed, 3 assertions.
- `composer validate --no-check-publish` berhasil.
