@extends('layouts.app')

@section('content')
<div class="animate-fade-up">
    <!-- Header -->
    <div class="card p-4 p-lg-5 mb-4">
        <div class="section-title mb-3" style="font-size:1.5rem;">
            <span class="icon-box" style="background:rgba(79,70,229,.1); color:var(--primary); width:44px; height:44px; font-size:1.2rem;"><i class="bi bi-book"></i></span>
            Dokumentasi Project
        </div>
        <p style="color:var(--text-muted); max-width:600px;">Halaman ini menampilkan problem statement, fitur, teknologi, dan endpoint API untuk keperluan demo YouTube dan presentasi UTS.</p>
    </div>

    <!-- Problem & Solusi -->
    <div class="row g-3 mb-4">
        <div class="col-md-6 animate-fade-up stagger-1">
            <div class="card p-4 h-100">
                <div class="fw-bold mb-2 d-flex align-items-center gap-2">
                    <i class="bi bi-exclamation-circle" style="color:var(--danger);"></i> Problem Statement
                </div>
                <p class="mb-0" style="color:var(--text-muted); line-height:1.7; font-size:.9rem;">
                    Mahasiswa sering kesulitan memahami materi kuliah yang panjang, terutama saat harus membaca buku, file PDF, atau catatan yang banyak. Selain itu, mahasiswa juga membutuhkan latihan soal dan rangkuman cepat untuk membantu proses belajar sebelum ujian.
                </p>
            </div>
        </div>
        <div class="col-md-6 animate-fade-up stagger-2">
            <div class="card p-4 h-100">
                <div class="fw-bold mb-2 d-flex align-items-center gap-2">
                    <i class="bi bi-lightbulb" style="color:var(--success);"></i> Solusi AI
                </div>
                <p class="mb-0" style="color:var(--text-muted); line-height:1.7; font-size:.9rem;">
                    Membuat web AI Study Buddy yang dapat meringkas materi, membuat soal latihan, membuat rencana belajar, dan menjawab pertanyaan berdasarkan materi yang dimasukkan atau file materi yang diunggah. AI digunakan pada <b>alur utama</b> aplikasi, bukan hanya fitur tambahan.
                </p>
            </div>
        </div>
    </div>

    <!-- Fitur -->
    <div class="card p-4 mb-4 animate-fade-up stagger-2">
        <div class="fw-bold mb-3 d-flex align-items-center gap-2">
            <i class="bi bi-grid-3x3-gap" style="color:var(--primary);"></i> Fitur Aplikasi
        </div>
        <div class="row g-3">
            <div class="col-md-4">
                <div class="p-3 rounded-4" style="background:var(--surface-alt);">
                    <div class="fw-bold mb-1"><i class="bi bi-stars me-1" style="color:var(--primary);"></i> AI Summarizer</div>
                    <div class="small" style="color:var(--text-muted);">Ringkas materi panjang menjadi poin-poin penting secara otomatis menggunakan Groq AI.</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="p-3 rounded-4" style="background:var(--surface-alt);">
                    <div class="fw-bold mb-1"><i class="bi bi-patch-question me-1" style="color:var(--warning);"></i> Quiz Generator</div>
                    <div class="small" style="color:var(--text-muted);">Buat soal pilihan ganda, essay, atau campuran dengan jumlah sesuai prompt, lengkap jawaban dan pembahasan.</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="p-3 rounded-4" style="background:var(--surface-alt);">
                    <div class="fw-bold mb-1"><i class="bi bi-calendar-check me-1" style="color:#8b5cf6;"></i> Study Plan</div>
                    <div class="small" style="color:var(--text-muted);">Rencana belajar bertahap otomatis berdasarkan materi dan deadline ujian.</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="p-3 rounded-4" style="background:var(--surface-alt);">
                    <div class="fw-bold mb-1"><i class="bi bi-cloud-upload me-1" style="color:var(--success);"></i> Upload Materi</div>
                    <div class="small" style="color:var(--text-muted);">Upload file PDF, DOCX, TXT, atau foto materi. Sistem otomatis mengekstrak teks.</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="p-3 rounded-4" style="background:var(--surface-alt);">
                    <div class="fw-bold mb-1"><i class="bi bi-chat-dots me-1" style="color:var(--accent);"></i> Chat Material</div>
                    <div class="small" style="color:var(--text-muted);">Chat langsung dengan isi dokumen. Bisa ringkas per bab, buat soal per bab, dan susun rencana belajar.</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="p-3 rounded-4" style="background:var(--surface-alt);">
                    <div class="fw-bold mb-1"><i class="bi bi-clock-history me-1" style="color:#f43f5e;"></i> Riwayat AI</div>
                    <div class="small" style="color:var(--text-muted);">Semua hasil AI tersimpan di MongoDB, bisa diakses kembali, dan bisa dilanjutkan dari sesi lama.</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Teknologi -->
    <div class="card p-4 mb-4 animate-fade-up stagger-3">
        <div class="fw-bold mb-3 d-flex align-items-center gap-2">
            <i class="bi bi-gear" style="color:var(--accent);"></i> Teknologi yang Digunakan
        </div>
        <div class="d-flex flex-wrap gap-2">
            <span class="badge badge-soft px-3 py-2">Laravel</span>
            <span class="badge badge-soft-success px-3 py-2">MongoDB</span>
            <span class="badge badge-soft-warning px-3 py-2">Groq API</span>
            <span class="badge badge-soft-cyan px-3 py-2">Postman</span>
            <span class="badge px-3 py-2" style="background:rgba(0,0,0,.06);">GitHub + VPS</span>
            <span class="badge px-3 py-2" style="background:rgba(0,0,0,.06);">Bootstrap 5</span>
            <span class="badge px-3 py-2" style="background:rgba(0,0,0,.06);">Blade</span>
        </div>
    </div>

    <!-- API Endpoints -->
    <div class="card p-4 mb-4 animate-fade-up stagger-4">
        <div class="fw-bold mb-3 d-flex align-items-center gap-2">
            <i class="bi bi-hdd-network" style="color:var(--primary);"></i> Endpoint API (Postman)
        </div>
        <p class="small mb-3" style="color:var(--text-muted);">Import file <code>docs/postman_collection.json</code> ke Postman untuk testing.</p>
        
        <!-- General -->
        <div class="fw-bold small mb-2" style="color:var(--text-muted);"><i class="bi bi-heart-pulse me-1"></i>General</div>
        <div class="table-responsive mb-4">
            <table class="table table-sm align-middle mb-0" style="font-size:.85rem;">
                <thead><tr style="background:var(--surface-alt);"><th style="border-radius:8px 0 0 8px;">Method</th><th>URL</th><th style="border-radius:0 8px 8px 0;">Keterangan</th></tr></thead>
                <tbody>
                    <tr><td><span class="badge badge-soft-success">GET</span></td><td><code>/api/health</code></td><td>Cek API aktif</td></tr>
                </tbody>
            </table>
        </div>
        
        <!-- Materials -->
        <div class="fw-bold small mb-2" style="color:var(--text-muted);"><i class="bi bi-journal-text me-1"></i>Materi Manual</div>
        <div class="table-responsive mb-4">
            <table class="table table-sm align-middle mb-0" style="font-size:.85rem;">
                <thead><tr style="background:var(--surface-alt);"><th style="border-radius:8px 0 0 8px;">Method</th><th>URL</th><th style="border-radius:0 8px 8px 0;">Keterangan</th></tr></thead>
                <tbody>
                    <tr><td><span class="badge badge-soft-success">GET</span></td><td><code>/api/materials</code></td><td>Daftar materi</td></tr>
                    <tr><td><span class="badge badge-soft">POST</span></td><td><code>/api/materials</code></td><td>Simpan materi baru</td></tr>
                    <tr><td><span class="badge badge-soft-success">GET</span></td><td><code>/api/materials/{id}</code></td><td>Detail materi</td></tr>
                    <tr><td><span class="badge badge-soft-danger">DELETE</span></td><td><code>/api/materials/{id}</code></td><td>Hapus materi</td></tr>
                </tbody>
            </table>
        </div>

        <!-- AI -->
        <div class="fw-bold small mb-2" style="color:var(--text-muted);"><i class="bi bi-cpu me-1"></i>AI Feature</div>
        <div class="table-responsive mb-4">
            <table class="table table-sm align-middle mb-0" style="font-size:.85rem;">
                <thead><tr style="background:var(--surface-alt);"><th style="border-radius:8px 0 0 8px;">Method</th><th>URL</th><th style="border-radius:0 8px 8px 0;">Keterangan</th></tr></thead>
                <tbody>
                    <tr><td><span class="badge badge-soft">POST</span></td><td><code>/api/ai/summarize</code></td><td>Ringkas materi dengan Groq AI</td></tr>
                    <tr><td><span class="badge badge-soft">POST</span></td><td><code>/api/ai/quiz</code></td><td>Buat quiz dari materi dengan jumlah terkontrol</td></tr>
                    <tr><td><span class="badge badge-soft">POST</span></td><td><code>/api/ai/study-plan</code></td><td>Buat rencana belajar</td></tr>
                    <tr><td><span class="badge badge-soft">POST</span></td><td><code>/api/ai/chat</code></td><td>Chat tentang materi</td></tr>
                    <tr><td><span class="badge badge-soft-success">GET</span></td><td><code>/api/ai/web-search</code></td><td>Web enrichment opsional dari You.com</td></tr>
                    <tr><td><span class="badge badge-soft-success">GET</span></td><td><code>/api/ai/history</code></td><td>Riwayat penggunaan AI</td></tr>
                    <tr><td><span class="badge badge-soft-success">GET</span></td><td><code>/api/ai/history/{id}</code></td><td>Detail satu riwayat</td></tr>
                    <tr><td><span class="badge badge-soft">POST</span></td><td><code>/api/ai/history/{id}/continue</code></td><td>Lanjutkan sesi dari riwayat</td></tr>
                    <tr><td><span class="badge badge-soft-danger">DELETE</span></td><td><code>/api/ai/history/{id}</code></td><td>Hapus riwayat tertentu</td></tr>
                    <tr><td><span class="badge badge-soft-danger">DELETE</span></td><td><code>/api/ai/history/all</code></td><td>Hapus semua riwayat</td></tr>
                </tbody>
            </table>
        </div>

        <!-- Documents -->
        <div class="fw-bold small mb-2" style="color:var(--text-muted);"><i class="bi bi-file-earmark-text me-1"></i>Upload Dokumen</div>
        <div class="table-responsive mb-4">
            <table class="table table-sm align-middle mb-0" style="font-size:.85rem;">
                <thead><tr style="background:var(--surface-alt);"><th style="border-radius:8px 0 0 8px;">Method</th><th>URL</th><th style="border-radius:0 8px 8px 0;">Keterangan</th></tr></thead>
                <tbody>
                    <tr><td><span class="badge badge-soft-success">GET</span></td><td><code>/api/documents</code></td><td>Daftar dokumen</td></tr>
                    <tr><td><span class="badge badge-soft">POST</span></td><td><code>/api/documents</code></td><td>Upload & ekstrak teks dari file</td></tr>
                    <tr><td><span class="badge badge-soft-success">GET</span></td><td><code>/api/documents/{id}</code></td><td>Detail dokumen</td></tr>
                    <tr><td><span class="badge badge-soft-danger">DELETE</span></td><td><code>/api/documents/{id}</code></td><td>Hapus dokumen</td></tr>
                </tbody>
            </table>
        </div>

        <!-- Document Chat -->
        <div class="fw-bold small mb-2" style="color:var(--text-muted);"><i class="bi bi-chat-dots me-1"></i>Chat Dokumen</div>
        <div class="table-responsive">
            <table class="table table-sm align-middle mb-0" style="font-size:.85rem;">
                <thead><tr style="background:var(--surface-alt);"><th style="border-radius:8px 0 0 8px;">Method</th><th>URL</th><th style="border-radius:0 8px 8px 0;">Keterangan</th></tr></thead>
                <tbody>
                    <tr><td><span class="badge badge-soft">POST</span></td><td><code>/api/documents/{id}/chat</code></td><td>Chat dengan isi dokumen</td></tr>
                    <tr><td><span class="badge badge-soft">POST</span></td><td><code>/api/documents/{id}/summarize</code></td><td>Ringkas dokumen/bab</td></tr>
                    <tr><td><span class="badge badge-soft">POST</span></td><td><code>/api/documents/{id}/quiz</code></td><td>Buat quiz dari dokumen/bab dengan jumlah terkontrol</td></tr>
                    <tr><td><span class="badge badge-soft">POST</span></td><td><code>/api/documents/{id}/study-plan</code></td><td>Buat rencana belajar dari dokumen/bab</td></tr>
                    <tr><td><span class="badge badge-soft-success">GET</span></td><td><code>/api/documents/{id}/history</code></td><td>Riwayat chat dokumen</td></tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Pengembang -->
    <div class="card p-4 animate-fade-up stagger-5">
        <div class="fw-bold mb-3 d-flex align-items-center gap-2">
            <i class="bi bi-person-badge" style="color:#8b5cf6;"></i> Pengembang
        </div>
        <div class="d-flex align-items-center gap-3 p-3 rounded-4" style="background:var(--surface-alt);">
            <div class="stat-icon purple"><i class="bi bi-person"></i></div>
            <div>
                <div class="fw-bold">{{ config('services.developer.name') }}</div>
                <div class="small" style="color:var(--text-muted);">Mahasiswa — Pengembang Aplikasi</div>
            </div>
        </div>
    </div>
</div>
@endsection
