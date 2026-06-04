@extends('layouts.app')

@section('content')
<div class="row g-4 animate-fade-up">
    <div class="col-md-5">
        <div class="card p-4 mb-4">
            <div class="section-title mb-3">
                <span class="icon-box" style="background:rgba(16,185,129,.1); color:var(--success);"><i class="bi bi-cloud-upload"></i></span>
                Upload Materi
            </div>
            <p style="color:var(--text-muted); font-size:.85rem;">Mendukung format: <b>.txt</b>, <b>.pdf</b>, <b>.docx</b>, <b>.jpg/.png</b> (gambar dengan teks manual)</p>
            <div class="feature-card-art upload-page-art">
                <img src="{{ asset('assets/images/feature-upload.png') }}" alt="Ilustrasi upload materi" onerror="this.onerror=null;this.src='{{ asset('assets/icons/fallback-illustration.svg') }}';">
            </div>
            
            <form id="uploadForm" onsubmit="handleUpload(event)">
                <div class="mb-3">
                    <label class="form-label">Judul Buku / Dokumen</label>
                    <input type="text" id="title" class="form-control" required placeholder="Contoh: Buku Kecerdasan Buatan">
                </div>
                <div class="mb-3">
                    <label class="form-label">Topik / Mata Kuliah</label>
                    <input type="text" id="subject" class="form-control" required placeholder="Contoh: Kecerdasan Buatan">
                </div>
                <div class="mb-3">
                    <label class="form-label">File Dokumen</label>
                    <input type="file" id="file" class="form-control" accept=".txt,.pdf,.docx,.jpg,.jpeg,.png" onchange="previewSelectedFile()">
                    <div id="filePreview" class="small mt-2" style="color:var(--text-muted);"></div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Teks Manual / OCR <span class="badge badge-soft" style="font-size:.65rem;">Opsional</span></label>
                    <p class="small mb-1" style="color:var(--text-muted);">Untuk foto materi, klik OCR otomatis atau paste hasil teksnya di sini.</p>
                    <textarea id="ocr_text" class="form-control" rows="4" placeholder="Ketik/paste teks manual di sini..."></textarea>
                    <button type="button" class="btn btn-outline-primary btn-sm mt-2" id="ocrBtn" onclick="runBrowserOcr()" style="display:none;">
                        <i class="bi bi-camera me-1"></i> Baca Foto Otomatis
                    </button>
                </div>
                
                <button type="submit" class="btn btn-primary w-100" id="uploadBtn">
                    <i class="bi bi-cloud-upload me-1"></i> Upload & Ekstrak Teks
                </button>
            </form>
        </div>
    </div>
    
    <div class="col-md-7 animate-fade-up stagger-2">
        <div class="card p-4 h-100 d-flex flex-column">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div class="section-title">
                    <span class="icon-box" style="background:rgba(6,182,212,.1); color:var(--accent);"><i class="bi bi-file-earmark-text"></i></span>
                    Hasil Ekstraksi
                </div>
                <span id="status" class="badge badge-soft">Menunggu File</span>
            </div>
            
            <ul class="nav nav-tabs mb-3" id="resultTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="text-tab" data-bs-toggle="tab" data-bs-target="#text-tab-pane" type="button" role="tab" style="font-size:.85rem;">
                        <i class="bi bi-file-text me-1"></i> Teks Mentah
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="chapter-tab" data-bs-toggle="tab" data-bs-target="#chapter-tab-pane" type="button" role="tab" style="font-size:.85rem;">
                        <i class="bi bi-list-ol me-1"></i> Bab Terdeteksi
                    </button>
                </li>
            </ul>
            <div class="tab-content flex-grow-1" id="myTabContent" style="display: flex; flex-direction: column;">
                <div class="tab-pane fade show active h-100" id="text-tab-pane" role="tabpanel" tabindex="0">
                    <textarea id="extractedResult" class="form-control h-100 w-100 font-monospace" style="min-height: 350px; background:var(--surface-alt); font-size:.85rem;" readonly placeholder="Hasil ekstraksi teks akan muncul di sini..."></textarea>
                </div>
                <div class="tab-pane fade h-100" id="chapter-tab-pane" role="tabpanel" tabindex="0">
                    <div id="chapterResult" class="h-100 w-100 overflow-auto rounded p-3" style="min-height: 350px; background:var(--surface-alt);">
                        <div class="text-center py-4" style="color:var(--text-muted);">
                            <i class="bi bi-collection fs-2 d-block mb-2" style="opacity:.3;"></i>
                            Bab belum dideteksi.
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="mt-3 d-grid gap-2" id="actionButtons" style="display: none;">
                <a href="{{ route('documents.chat') }}" class="btn btn-primary" id="chatLink">
                    <i class="bi bi-chat-dots me-1"></i> Mulai Chat dengan Materi Ini
                </a>
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-outline-primary flex-fill" onclick="copyExtractedToClipboard()">
                        <i class="bi bi-clipboard me-1"></i> Salin Teks
                    </button>
                    <a href="{{ route('ai.summarize') }}" class="btn btn-outline-primary flex-fill">
                        <i class="bi bi-stars me-1"></i> Ringkas Manual
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/tesseract.js@5/dist/tesseract.min.js"></script>
<script>
let uploadedDocumentId = null;

function previewSelectedFile() {
    const fileInput = document.getElementById('file');
    const file = fileInput.files[0];
    const preview = document.getElementById('filePreview');
    const ocrBtn = document.getElementById('ocrBtn');
    if (!file) {
        preview.innerHTML = '';
        ocrBtn.style.display = 'none';
        return;
    }

    const isImage = ['image/jpeg', 'image/png'].includes(file.type);
    preview.innerHTML = `<i class="bi bi-file-earmark me-1"></i>${file.name} - ${(file.size / 1024 / 1024).toFixed(2)} MB${isImage ? '<br><span class="text-warning">Foto memerlukan OCR. Klik tombol OCR otomatis atau isi teks manual.</span>' : ''}`;
    ocrBtn.style.display = isImage ? 'inline-flex' : 'none';
}

async function runBrowserOcr() {
    const file = document.getElementById('file').files[0];
    const btn = document.getElementById('ocrBtn');
    if (!file) return;
    if (!window.Tesseract) {
        alert('Library OCR belum berhasil dimuat. Paste teks foto secara manual.');
        return;
    }

    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Membaca foto...';
    try {
        const result = await Tesseract.recognize(file, 'ind+eng');
        document.getElementById('ocr_text').value = (result.data.text || '').trim();
    } catch (e) {
        alert('OCR gagal. Coba paste teks foto secara manual.');
    } finally {
        btn.disabled = false;
        btn.innerHTML = '<i class="bi bi-camera me-1"></i> Baca Foto Otomatis';
    }
}

function copyExtractedToClipboard() {
    const text = document.getElementById('extractedResult').value;
    if (!text) return;
    navigator.clipboard?.writeText(text);
}

async function handleUpload(e) {
    e.preventDefault();
    const btn = document.getElementById('uploadBtn');
    const status = document.getElementById('status');
    const title = document.getElementById('title').value;
    const subject = document.getElementById('subject').value;
    const ocr_text = document.getElementById('ocr_text').value;
    const fileInput = document.getElementById('file');
    const file = fileInput.files[0];
    
    if (!title.trim() || !subject.trim()) {
        alert("Judul dan topik wajib diisi.");
        return;
    }

    if (!file && !ocr_text.trim()) {
        alert("Silakan pilih file atau isi teks manual.");
        return;
    }

    if (file && ['image/jpeg', 'image/png'].includes(file.type) && ocr_text.trim().length < 20) {
        alert("Foto buku perlu teks OCR minimal 20 karakter. Klik OCR otomatis atau paste teksnya.");
        return;
    }
    
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status"></span> Mengupload & Mengekstrak...';
    status.className = 'badge badge-soft-warning';
    status.innerText = 'Memproses...';
    
    const formData = new FormData();
    formData.append('title', title);
    formData.append('subject', subject);
    if (file) formData.append('file', file);
    if (ocr_text) formData.append('ocr_text', ocr_text);
    
    try {
        const response = await fetch('/api/documents', {
            method: 'POST',
            body: formData,
            headers: { 'Accept': 'application/json' }
        });
        const json = await response.json();
        if (!response.ok) throw new Error(json.error || json.message || 'Terjadi kesalahan.');
        
        document.getElementById('extractedResult').value = json.data.extracted_text;
        uploadedDocumentId = json.data._id || json.data.id;
        
        let chapterHtml = '';
        json.data.chapters.forEach((c, idx) => {
            chapterHtml += `
                <div class="material-item mb-2 animate-fade-up" style="animation-delay:${idx*0.05}s;">
                    <div class="fw-bold" style="color:var(--primary); font-size:.9rem;">
                        <i class="bi bi-bookmark me-1"></i>${c.chapter} ${c.title ? '— ' + c.title : ''}
                    </div>
                    <div class="small mt-1" style="color:var(--text-muted);">${c.content.substring(0, 120)}...</div>
                </div>`;
        });
        document.getElementById('chapterResult').innerHTML = chapterHtml;
        document.getElementById('actionButtons').style.display = 'block';
        if (uploadedDocumentId) {
            document.getElementById('chatLink').href = `{{ route('documents.chat') }}?document=${uploadedDocumentId}`;
        }
        
        status.className = 'badge badge-soft-success';
        status.innerText = 'Berhasil';
        btn.innerHTML = '<i class="bi bi-check-circle me-1"></i> Berhasil Diupload';
        
    } catch (e) {
        console.error(e);
        alert(e.message);
        btn.disabled = false;
        btn.innerHTML = '<i class="bi bi-cloud-upload me-1"></i> Upload & Ekstrak Teks';
        status.className = 'badge badge-soft-danger';
        status.innerText = 'Gagal';
    }
}
</script>
@endpush
