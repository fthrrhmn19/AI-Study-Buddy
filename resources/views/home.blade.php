@extends('layouts.app')

@php $fallbackImg = asset('assets/icons/fallback-illustration.svg'); @endphp

@section('content')
<div class="home-premium">
    <section class="premium-hero">
        <canvas id="landing-particles-canvas" aria-hidden="true"></canvas>
        <div class="cursor-glow" aria-hidden="true"></div>

        <div class="premium-container hero-layout">
            <div class="hero-copy animate-fade-up">
                <div class="hero-kicker"><span class="hero-kicker-dot"></span> Asisten belajar AI - ringkasan, kuis, dan chat materi</div>
                <h1 class="hero-title"><span>AI Study</span><span>Buddy</span></h1>
                <p class="hero-subtitle">
                    Asisten belajar berbasis AI untuk merangkum materi, membuat kuis, menyusun rencana belajar, dan menjawab pertanyaan dari catatan atau file kuliah.
                </p>
                <div class="hero-actions">
                    <a href="#input-section" class="premium-btn primary"><i class="bi bi-arrow-right-circle"></i> Mulai Belajar</a>
                    <a href="#features" class="premium-btn secondary"><i class="bi bi-grid-3x3-gap"></i> Lihat Fitur</a>
                </div>

                <div class="hero-mini-stats" aria-label="Fitur utama AI Study Buddy">
                    <div class="hero-mini-stat" role="button" tabindex="0" onclick="jumpToFeature('summarize')" onkeydown="handleFeatureKey(event, 'summarize')">
                        <div class="mini-stat-icon"><i class="bi bi-stars"></i></div>
                        <b>AI Summarizer</b>
                        <small>Ringkasan cepat</small>
                    </div>
                    <div class="hero-mini-stat" role="button" tabindex="0" onclick="jumpToFeature('quiz')" onkeydown="handleFeatureKey(event, 'quiz')">
                        <div class="mini-stat-icon"><i class="bi bi-patch-question"></i></div>
                        <b>Quiz Generator</b>
                        <small>Soal latihan</small>
                    </div>
                    <div class="hero-mini-stat" role="button" tabindex="0" onclick="jumpToFeature('chat')" onkeydown="handleFeatureKey(event, 'chat')">
                        <div class="mini-stat-icon"><i class="bi bi-chat-square-text"></i></div>
                        <b>Chat Material</b>
                        <small>Tanya isi materi</small>
                    </div>
                    <div class="hero-mini-stat" role="button" tabindex="0" onclick="jumpToFeature('upload')" onkeydown="handleFeatureKey(event, 'upload')">
                        <div class="mini-stat-icon"><i class="bi bi-file-earmark-arrow-up"></i></div>
                        <b>File Upload</b>
                        <small>PDF, DOCX, TXT</small>
                    </div>
                </div>
            </div>

            <div class="hero-visual animate-fade-up stagger-2" aria-label="Ilustrasi AI education premium">
                <div class="hero-image-stage tilt-card">
                    <img src="{{ asset('assets/images/hero-ai-study.png') }}" alt="Ilustrasi AI Study Buddy membantu belajar" onerror="this.onerror=null;this.src='{{ $fallbackImg }}';">
                    <button class="hero-visual-chip chip-summary" type="button" onclick="jumpToFeature('summarize')">
                        <i class="bi bi-stars"></i>
                        <span>Ringkasan</span>
                    </button>
                    <button class="hero-visual-chip chip-quiz" type="button" onclick="jumpToFeature('quiz')">
                        <i class="bi bi-patch-question"></i>
                        <span>Kuis</span>
                    </button>
                    <button class="hero-visual-chip chip-chat" type="button" onclick="jumpToFeature('chat')">
                        <i class="bi bi-chat-square-text"></i>
                        <span>Chat materi</span>
                    </button>
                </div>
            </div>
        </div>

        <a href="#input-section" class="scroll-cue">Jelajahi fitur</a>
    </section>

    @if($dbWarning)
        <div class="premium-container mt-3">
            <div class="alert alert-warning d-flex align-items-center gap-2" style="border-radius:18px;">
                <i class="bi bi-exclamation-triangle-fill fs-5"></i>
                <span>{{ $dbWarning }}</span>
            </div>
        </div>
    @endif

    <section class="premium-section">
        <div class="premium-container">
            <div class="section-heading">
                <div>
                    <span class="badge badge-soft-cyan mb-2">Problem Statement</span>
                    <h2>Masalah belajar yang diselesaikan</h2>
                </div>
                <p>AI Study Buddy membantu mahasiswa mengubah materi panjang menjadi ringkasan, latihan, dan percakapan belajar yang lebih mudah dipahami.</p>
            </div>

            <div class="problem-grid">
                <div class="premium-card">
                    <div class="premium-card-icon"><i class="bi bi-journal-richtext"></i></div>
                    <h3>Materi panjang</h3>
                    <p>Catatan kuliah, modul, dan buku sering terlalu panjang untuk dipahami menjelang ujian.</p>
                </div>
                <div class="premium-card">
                    <div class="premium-card-icon"><i class="bi bi-hourglass-split"></i></div>
                    <h3>Ringkasan manual lambat</h3>
                    <p>Membuat rangkuman sendiri memakan waktu dan sering melewatkan poin penting.</p>
                </div>
                <div class="premium-card">
                    <div class="premium-card-icon"><i class="bi bi-ui-checks-grid"></i></div>
                    <h3>Latihan soal terbatas</h3>
                    <p>Mahasiswa butuh kuis otomatis agar bisa menguji pemahaman dari materi yang sama.</p>
                </div>
                <div class="premium-card">
                    <div class="premium-card-icon"><i class="bi bi-files"></i></div>
                    <h3>Materi tersebar</h3>
                    <p>File PDF, DOCX, TXT, gambar, dan catatan manual perlu masuk ke satu alur belajar.</p>
                </div>
            </div>
        </div>
    </section>

    <section id="features" class="premium-section pt-0">
        <div class="premium-container">
            <div class="section-heading">
                <div>
                    <span class="badge badge-soft mb-2">AI Solution</span>
                    <h2>Fitur utama AI Study Buddy</h2>
                </div>
                <p>Dirancang untuk membantu proses belajar dari input manual maupun file materi dalam satu workspace yang rapi.</p>
            </div>

            <div class="feature-grid">
                <div class="premium-card">
                    <div class="feature-card-art">
                        <img src="{{ asset('assets/images/feature-summarizer.png') }}" alt="Ilustrasi AI Summarizer" onerror="this.onerror=null;this.src='{{ $fallbackImg }}';">
                    </div>
                    <div class="premium-card-icon"><i class="bi bi-stars"></i></div>
                    <h3>AI Summarizer</h3>
                    <p>Meringkas materi menjadi inti pembahasan, poin penting, istilah kunci, dan kesimpulan.</p>
                    <a href="#input-section" class="card-action" onclick="jumpToFeature('summarize')">Coba ringkas</a>
                </div>
                <div class="premium-card">
                    <div class="feature-card-art">
                        <img src="{{ asset('assets/images/feature-quiz.png') }}" alt="Ilustrasi Quiz Generator" onerror="this.onerror=null;this.src='{{ $fallbackImg }}';">
                    </div>
                    <div class="premium-card-icon"><i class="bi bi-patch-question"></i></div>
                    <h3>Quiz Generator</h3>
                    <p>Membuat soal pilihan ganda, essay, atau campuran lengkap dengan jawaban dan pembahasan.</p>
                    <a href="#input-section" class="card-action" onclick="jumpToFeature('quiz')">Buat kuis</a>
                </div>
                <div class="premium-card">
                    <div class="feature-card-art">
                        <img src="{{ asset('assets/images/feature-study-plan.png') }}" alt="Ilustrasi Study Plan" onerror="this.onerror=null;this.src='{{ $fallbackImg }}';">
                    </div>
                    <div class="premium-card-icon"><i class="bi bi-calendar-check"></i></div>
                    <h3>Study Plan</h3>
                    <p>Menyusun rencana belajar bertahap dari materi yang sama agar persiapan lebih terstruktur.</p>
                    <a href="#input-section" class="card-action" onclick="jumpToFeature('study-plan')">Buat rencana</a>
                </div>
                <div class="premium-card">
                    <div class="feature-card-art">
                        <img src="{{ asset('assets/images/feature-chat.png') }}" alt="Ilustrasi Chat Material" onerror="this.onerror=null;this.src='{{ $fallbackImg }}';">
                    </div>
                    <div class="premium-card-icon"><i class="bi bi-chat-square-text"></i></div>
                    <h3>Chat Material</h3>
                    <p>User bisa bertanya langsung berdasarkan materi yang diketik atau file yang sudah diupload.</p>
                    <a href="#input-section" class="card-action" onclick="jumpToFeature('chat')">Tanya AI</a>
                </div>
                <div class="premium-card">
                    <div class="feature-card-art">
                        <img src="{{ asset('assets/images/feature-upload.png') }}" alt="Ilustrasi Upload Materi" onerror="this.onerror=null;this.src='{{ $fallbackImg }}';">
                    </div>
                    <div class="premium-card-icon"><i class="bi bi-file-earmark-arrow-up"></i></div>
                    <h3>Upload Materi</h3>
                    <p>Dokumen masuk dari Ringkasan, Kuis, dan Rencana sehingga navbar tetap ringkas.</p>
                    <a href="#input-section" class="card-action" onclick="jumpToFeature('upload')">Upload file</a>
                </div>
            </div>
        </div>
    </section>

    <section id="input-section" class="workspace-section" style="scroll-margin-top:90px;">
        <div class="premium-container">
            <div class="section-heading">
                <div>
                    <span class="badge badge-soft mb-2">Learning Workspace</span>
                    <h2>Mulai belajar dari materi kamu</h2>
                </div>
                <p>Input atau upload materi, pilih aksi AI, baca hasilnya, lalu lanjut bertanya lewat chat material.</p>
            </div>

            <div class="row g-3 mb-3">
                <div class="col-6 col-lg-3">
                    <div class="stat-card bg-white">
                        <div class="stat-icon purple"><i class="bi bi-journal-text"></i></div>
                        <div class="stat-value">{{ $materialsCount }}</div>
                        <div class="stat-label">Materi Tersimpan</div>
                    </div>
                </div>
                <div class="col-6 col-lg-3">
                    <div class="stat-card bg-white">
                        <div class="stat-icon cyan"><i class="bi bi-cpu"></i></div>
                        <div class="stat-value">{{ $aiCount }}</div>
                        <div class="stat-label">Riwayat AI</div>
                    </div>
                </div>
                <div class="col-6 col-lg-3">
                    <div class="stat-card bg-white">
                        <div class="stat-icon emerald"><i class="bi bi-lightning-charge"></i></div>
                        <div class="stat-value" style="font-size:1.24rem;">Groq API</div>
                        <div class="stat-label">Provider AI</div>
                    </div>
                </div>
                <div class="col-6 col-lg-3">
                    <div class="stat-card bg-white">
                        <div class="stat-icon amber"><i class="bi bi-database"></i></div>
                        <div class="stat-value" style="font-size:1.28rem;">MongoDB</div>
                        <div class="stat-label">Database</div>
                    </div>
                </div>
            </div>

            <div class="workspace-shell">
                <div class="workspace-panel p-4">
                    <div class="section-title mb-3">
                        <span class="icon-box" style="background:rgba(79,70,229,.1); color:var(--primary);"><i class="bi bi-journal-text"></i></span>
                        Input Materi
                    </div>
                    <ul class="nav nav-tabs mb-3" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#home-manual" type="button" role="tab"><i class="bi bi-pencil-square me-1"></i>Ketik Manual</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" data-bs-toggle="tab" data-bs-target="#home-upload" type="button" role="tab"><i class="bi bi-cloud-upload me-1"></i>Upload File</button>
                        </li>
                    </ul>
                    <div class="tab-content">
                        <div class="tab-pane fade show active" id="home-manual" role="tabpanel">
                            <div class="mb-3">
                                <label class="form-label">Judul Materi</label>
                                <input id="title" class="form-control" placeholder="Contoh: Konsep Dasar Machine Learning">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Mata Kuliah / Topik</label>
                                <input id="subject" class="form-control" placeholder="Contoh: Kecerdasan Buatan">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Isi Materi</label>
                                <textarea id="content" class="form-control" placeholder="Tempel materi kuliah di sini, minimal 40 karakter"></textarea>
                            </div>
                        </div>
                        <div class="tab-pane fade" id="home-upload" role="tabpanel">
                            <div class="upload-drop mb-3">
                                <div class="d-flex align-items-center gap-3 mb-3">
                                    <span class="icon-box" style="background:rgba(6,182,212,.12); color:var(--accent);"><i class="bi bi-file-earmark-arrow-up"></i></span>
                                    <div>
                                        <div class="fw-bold">Upload materi belajar</div>
                                        <div class="small text-muted">PDF, DOCX, TXT, JPG, PNG sampai 10MB.</div>
                                    </div>
                                </div>
                                <input type="file" id="fileInput" class="form-control" accept=".txt,.pdf,.docx,.jpg,.jpeg,.png">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Judul Dokumen</label>
                                <input id="uploadTitle" class="form-control" placeholder="Contoh: Materi AI">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Topik / Mata Kuliah</label>
                                <input id="uploadSubject" class="form-control" placeholder="Contoh: Kecerdasan Buatan">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Teks Manual / OCR <span class="badge badge-soft" style="font-size:.65rem;">Opsional</span></label>
                                <textarea id="ocrText" class="form-control" rows="2" placeholder="Untuk gambar: paste teks OCR atau ketik isi materi di sini"></textarea>
                            </div>
                            <button class="btn btn-primary w-100 mb-2" onclick="uploadAndLoad()" id="btnUpload">
                                <i class="bi bi-cloud-upload me-1"></i> Upload & Muat Materi
                            </button>
                        </div>
                    </div>
                </div>

                <div class="workspace-panel p-4 d-flex flex-column">
                    <div class="d-flex align-items-center justify-content-between gap-2 mb-3">
                        <div class="section-title">
                            <span class="icon-box" style="background:rgba(6,182,212,.1); color:var(--accent);"><i class="bi bi-cpu"></i></span>
                            Interaksi AI
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <button type="button" class="btn btn-sm btn-outline-primary" onclick="copyResult()" title="Salin hasil"><i class="bi bi-copy"></i></button>
                            <span id="status" class="badge badge-soft">Siap digunakan</span>
                        </div>
                    </div>

                    <div id="result" class="result-box premium-result flex-grow-1" style="overflow-y:auto;">
                        <div class="result-empty">
                            <div class="empty-state-content">
                                <div class="ai-orb-illustration" aria-hidden="true">
                                    <div class="ai-orb-core">
                                        <span class="ai-eye left"></span>
                                        <span class="ai-eye right"></span>
                                        <span class="ai-mouth"></span>
                                    </div>
                                    <span class="ai-orbit orbit-one"></span>
                                    <span class="ai-orbit orbit-two"></span>
                                    <span class="ai-spark spark-one"></span>
                                    <span class="ai-spark spark-two"></span>
                                </div>
                                <h5 class="fw-bold mb-2" style="color:#f8fafc; font-size:1.15rem;">Asisten AI siap membantu</h5>
                                <p class="empty-state-desc">Ketik materi atau upload file, lalu pilih aksi AI di bawah.</p>
                                <div class="empty-state-hints">
                                    <span class="hint-pill"><i class="bi bi-stars"></i> Ringkasan</span>
                                    <span class="hint-pill"><i class="bi bi-patch-question"></i> Kuis</span>
                                    <span class="hint-pill"><i class="bi bi-calendar-check"></i> Rencana</span>
                                    <span class="hint-pill"><i class="bi bi-chat-square-text"></i> Chat</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <form onsubmit="sendDashChat(event)" class="mt-3 d-flex gap-2">
                        <input id="dashChatInput" class="form-control" placeholder="Tanya tentang isi materi..." autocomplete="off">
                        <button type="submit" class="btn btn-primary px-3"><i class="bi bi-send"></i></button>
                    </form>

                    <div class="mt-3 pt-3" style="border-top:1px solid var(--border);">
                        <div class="mb-2 small fw-bold text-muted"><i class="bi bi-lightning-charge text-warning"></i> Menu Cepat AI</div>
                        <div class="quick-actions align-items-center">
                            <button class="btn btn-sm btn-primary" onclick="summarize()" id="btnSummarize">
                                <i class="bi bi-stars"></i> Ringkasan
                            </button>
                            <button class="btn btn-sm btn-outline-primary" onclick="makePlan()" id="btnPlan" style="border-color:var(--accent);color:var(--accent);">
                                <i class="bi bi-calendar-check"></i> Rencana Belajar
                            </button>
                            <div class="d-flex align-items-center gap-2 ms-auto ps-3" style="border-left:1px solid var(--border);">
                                <select id="quick_qtype" class="form-select form-select-sm" style="width:120px; border-radius:8px;">
                                    <option value="pilihan_ganda">PG</option>
                                    <option value="essay">Essay</option>
                                    <option value="campuran" selected>Campuran</option>
                                </select>
                                <input id="quick_qtotal" type="number" class="form-control form-control-sm" value="5" min="1" max="25" style="width:64px; border-radius:8px;" title="Jumlah Soal">
                                <button class="btn btn-sm btn-outline-warning fw-bold" onclick="makeQuiz()" id="btnQuiz">
                                    <i class="bi bi-patch-question"></i> Kuis
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="workspace-panel materials-premium p-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div class="section-title">
                        <span class="icon-box" style="background:rgba(245,158,11,.1); color:var(--warning);"><i class="bi bi-folder2-open"></i></span>
                        Materi Terbaru
                    </div>
                    <button class="btn btn-sm btn-outline-primary" onclick="loadMaterials()">
                        <i class="bi bi-arrow-clockwise me-1"></i> Refresh
                    </button>
                </div>
                <div id="materials-list">
                    @forelse($recentMaterials as $material)
                        <div class="material-item mb-2" onclick="loadMaterialDetail('{{ $material->_id ?? $material->id }}')">
                            <div class="d-flex align-items-center gap-2">
                                <i class="bi bi-file-earmark-text" style="color:var(--primary);"></i>
                                <div>
                                    <div class="fw-bold" style="font-size:.92rem;">{{ $material->title }}</div>
                                    <div style="color:var(--text-muted); font-size:.8rem;">{{ $material->subject }} - {{ optional($material->created_at)->format('d M Y H:i') }}</div>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="materials-empty-state">
                            <div class="materials-empty-visual" aria-hidden="true">
                                <span class="materials-file-line line-one"></span>
                                <span class="materials-file-line line-two"></span>
                                <span class="materials-file-line line-three"></span>
                                <i class="bi bi-folder2-open"></i>
                            </div>
                            <div class="materials-empty-text">
                                <div class="fw-semibold" style="color:#334155; font-size:.92rem;">Belum ada materi tersimpan</div>
                                <div style="color:#94a3b8; font-size:.8rem; margin-top:.25rem;">Mulai dengan memasukkan materi di atas.</div>
                            </div>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </section>
</div>
@endsection

@push('scripts')
<script>
let latestMaterialId = null;
let uploadedContent = '';
let dashChatHistory = [];
const initialFeature = JSON.parse('{!! json_encode($activeFeature ?? null) !!}');

function escapeHtml(value) {
    return String(value ?? '')
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
}

function parseMarkdown(text) {
    return escapeHtml(text)
        .replace(/\*\*(.*?)\*\*/g, '<b>$1</b>')
        .replace(/\n/g, '<br>');
}

function getContent() {
    return uploadedContent || document.getElementById('content').value;
}

function formPayload() {
    return {
        title: document.getElementById('title').value || document.getElementById('uploadTitle')?.value || '',
        subject: document.getElementById('subject').value || document.getElementById('uploadSubject')?.value || '',
        content: getContent()
    };
}

function setStatus(text, type = 'soft') {
    const status = document.getElementById('status');
    const classes = {
        soft: 'badge badge-soft',
        warning: 'badge badge-soft-warning',
        success: 'badge badge-soft-success',
        danger: 'badge badge-soft-danger',
        cyan: 'badge badge-soft-cyan'
    };
    status.className = classes[type] || classes.soft;
    status.innerText = text;
}

function showTab(target) {
    const tabButton = document.querySelector(`[data-bs-target="${target}"]`);
    if (tabButton && window.bootstrap) {
        new bootstrap.Tab(tabButton).show();
    }
}

function focusFeatureTarget(elementId) {
    const element = document.getElementById(elementId);
    if (!element) {
        return;
    }

    element.classList.add('feature-target-pulse');
    element.focus({ preventScroll: true });
    window.setTimeout(() => element.classList.remove('feature-target-pulse'), 1200);
}

function jumpToFeature(feature) {
    document.getElementById('input-section')?.scrollIntoView({ behavior: 'smooth', block: 'start' });

    window.setTimeout(() => {
        if (feature === 'upload') {
            showTab('#home-upload');
            setStatus('Upload materi', 'cyan');
            focusFeatureTarget('fileInput');
            return;
        }

        showTab('#home-manual');

        if (feature === 'quiz') {
            setStatus('Pilih Kuis', 'cyan');
            focusFeatureTarget('btnQuiz');
            return;
        }

        if (feature === 'study-plan') {
            setStatus('Pilih Rencana Belajar', 'cyan');
            focusFeatureTarget('btnPlan');
            return;
        }

        if (feature === 'chat') {
            setStatus('Chat material', 'cyan');
            focusFeatureTarget('dashChatInput');
            return;
        }

        setStatus('Pilih Ringkasan', 'cyan');
        focusFeatureTarget('btnSummarize');
    }, 420);
}

function handleFeatureKey(event, feature) {
    if (event.key === 'Enter' || event.key === ' ') {
        event.preventDefault();
        jumpToFeature(feature);
    }
}

function setLoading(text) {
    setStatus(text, 'warning');
    document.getElementById('result').innerHTML = `
        <div class="d-flex align-items-center gap-2">
            <span class="spinner-border spinner-border-sm" role="status"></span>
            <span class="loading-dots">${escapeHtml(text)}</span>
        </div>
    `;
}

function renderWebSources(sources) {
    if (!sources || !sources.length) {
        return '';
    }

    let html = '<div class="web-sources mt-3"><div class="small fw-bold mb-2" style="color:var(--accent);"><i class="bi bi-globe2 me-1"></i>Referensi Web</div>';
    sources.forEach(source => {
        html += `
            <div class="web-source-item">
                <div>
                    <a href="${escapeHtml(source.url)}" target="_blank" rel="noopener">${escapeHtml(source.title || source.url)}</a>
                    <div class="desc">${escapeHtml((source.description || '').substring(0, 140))}</div>
                </div>
            </div>
        `;
    });
    return html + '</div>';
}

function renderQuiz(data) {
    const questions = Array.isArray(data?.questions) ? data.questions : [];
    if (!questions.length) {
        return `<pre>${escapeHtml(JSON.stringify(data, null, 2))}</pre>`;
    }

    return questions.map((question, index) => {
        const type = question.type || (question.options ? 'multiple_choice' : 'essay');
        const title = `<b style="color:#bfdbfe;">${index + 1}. ${escapeHtml(question.question || '')}</b>`;

        if (type === 'essay') {
            return `
                <div class="quiz-card" style="background:rgba(255,255,255,.055); border-color:rgba(255,255,255,.12);">
                    <div class="d-flex align-items-center gap-2 mb-2">
                        <span class="badge badge-soft-cyan"><i class="bi bi-pencil me-1"></i>Essay</span>${title}
                    </div>
                    <div class="mt-2 p-3 rounded" style="background:rgba(16,185,129,.08);border-left:3px solid var(--success);">
                        <div class="small fw-bold mb-1" style="color:var(--success);">Kunci Jawaban</div>
                        <div style="font-size:.9rem;line-height:1.7;">${parseMarkdown(question.answer_key || question.answer || '-')}</div>
                    </div>
                    ${question.explanation ? `<div style="color:#94a3b8;" class="small mt-2">${parseMarkdown(question.explanation)}</div>` : ''}
                </div>
            `;
        }

        return `
            <div class="quiz-card" style="background:rgba(255,255,255,.055); border-color:rgba(255,255,255,.12);">
                <div class="d-flex align-items-center gap-2 mb-2">
                    <span class="badge badge-soft-warning"><i class="bi bi-list-check me-1"></i>PG</span>${title}
                </div>
                <div class="ps-2 mt-2 mb-2">${(question.options || []).map(option => `<div class="mb-1">${escapeHtml(option)}</div>`).join('')}</div>
                <div style="color:#34d399;" class="mt-1 fw-bold">Jawaban: ${escapeHtml(question.answer || '-')}</div>
                ${question.explanation ? `<div style="color:#94a3b8;" class="small mt-1">${parseMarkdown(question.explanation)}</div>` : ''}
            </div>
        `;
    }).join('');
}

function renderStudyPlan(data) {
    let html = `<div class="mb-3"><b style="color:#bfdbfe;">Tujuan:</b> ${escapeHtml(data.goal || 'Memahami materi secara bertahap')}</div>`;

    if (Array.isArray(data.steps) && data.steps.length) {
        html += '<div class="mb-2"><b style="color:#bfdbfe;">Langkah belajar:</b></div><ul class="mb-3">';
        data.steps.forEach(step => {
            html += `<li class="mb-1"><b>${escapeHtml(step.day || 'Tahap')}</b>: ${escapeHtml(step.task || '')} <span class="badge badge-soft-cyan">${escapeHtml(step.duration || '')}</span></li>`;
        });
        html += '</ul>';
    }

    if (Array.isArray(data.tips) && data.tips.length) {
        html += '<div class="mb-2"><b style="color:#bfdbfe;">Tips:</b></div><ul>';
        data.tips.forEach(tip => html += `<li>${escapeHtml(tip)}</li>`);
        html += '</ul>';
    }

    return html;
}

function setResult(data, webSources = []) {
    setStatus('Berhasil', 'success');
    dashChatHistory = [];

    let html = '';
    if (typeof data === 'string') {
        html = parseMarkdown(data);
    } else if (data?.summary) {
        html = parseMarkdown(data.summary);
    } else if (data?.questions) {
        html = renderQuiz(data);
    } else if (data?.goal || data?.steps || data?.tips) {
        html = renderStudyPlan(data);
    } else {
        html = `<pre>${escapeHtml(JSON.stringify(data, null, 2))}</pre>`;
    }

    html += renderWebSources(webSources);
    const result = document.getElementById('result');
    result.innerHTML = html;
    result.style.animation = 'scaleIn .3s ease';
}

function setError(error) {
    const raw = String(error?.message || error?.error || JSON.stringify(error, null, 2) || 'Terjadi error saat memproses permintaan.');
    const normalized = raw.toLowerCase();
    const message = (normalized.includes('rate_limit') || normalized.includes('request too large') || normalized.includes('413') || normalized.includes('token') || normalized.includes('groq api error'))
        ? 'Materi terlalu panjang untuk limit Groq saat ini. Coba pilih bab tertentu, upload materi yang lebih fokus, atau ulangi beberapa saat lagi.'
        : (raw.length > 220 ? raw.slice(0, 220) + '...' : raw);
    setStatus('Error', 'danger');
    document.getElementById('result').innerHTML = `<div style="color:#fca5a5;overflow-wrap:anywhere;"><i class="bi bi-exclamation-triangle me-1"></i>${escapeHtml(message)}</div>`;
}

async function apiPost(url, payload) {
    const response = await fetch(url, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
        body: JSON.stringify(payload)
    });
    const json = await response.json();
    if (!response.ok) {
        throw json;
    }
    return json;
}

async function uploadAndLoad() {
    const button = document.getElementById('btnUpload');
    const fileInput = document.getElementById('fileInput');
    const ocrText = document.getElementById('ocrText')?.value || '';

    if (!fileInput.files[0] && !ocrText.trim()) {
        alert('Pilih file atau isi teks manual terlebih dahulu.');
        return;
    }

    const formData = new FormData();
    formData.append('title', document.getElementById('uploadTitle').value);
    formData.append('subject', document.getElementById('uploadSubject').value);
    if (fileInput.files[0]) {
        formData.append('file', fileInput.files[0]);
    }
    if (ocrText.trim()) {
        formData.append('ocr_text', ocrText);
    }

    button.disabled = true;
    button.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Mengupload...';
    setLoading('Mengupload dan mengekstrak teks');

    try {
        const response = await fetch('/api/documents', { method: 'POST', body: formData, headers: { 'Accept': 'application/json' } });
        const json = await response.json();
        if (!response.ok) {
            throw new Error(json.error || json.message || 'Gagal upload');
        }

        uploadedContent = (json.data.extracted_text || '').substring(0, 12000);
        document.getElementById('title').value = document.getElementById('uploadTitle').value;
        document.getElementById('subject').value = document.getElementById('uploadSubject').value;
        document.getElementById('content').value = uploadedContent.substring(0, 5000);

        const chapterCount = Array.isArray(json.data.chapters) ? json.data.chapters.length : 0;
        setStatus('Upload Berhasil', 'success');
        document.getElementById('result').innerHTML = `
            <div>
                <b style="color:#34d399;">Dokumen berhasil diupload.</b><br>
                <b>File:</b> ${escapeHtml(json.data.file_name || 'manual_input.txt')}<br>
                <b>Bab terdeteksi:</b> ${chapterCount}<br>
                <b>Karakter diekstrak:</b> ${uploadedContent.length}<br><br>
                Gunakan tombol Ringkasan, Kuis, atau Rencana Belajar untuk memproses materi ini. Kamu juga bisa langsung bertanya lewat chat material.
            </div>
        `;
        loadMaterials();
    } catch (error) {
        setError({ message: error.message || error.error || 'Gagal upload' });
    } finally {
        button.disabled = false;
        button.innerHTML = '<i class="bi bi-cloud-upload me-1"></i> Upload & Muat Materi';
    }
}

async function summarize() {
    const payload = formPayload();
    if (!payload.content || payload.content.length < 40) {
        alert('Materi minimal 40 karakter. Ketik materi atau upload file dulu.');
        return;
    }

    setLoading('Meringkas materi');
    try {
        const json = await apiPost('/api/ai/summarize', { ...payload, save: true });
        latestMaterialId = json?.data?.material?._id || json?.data?.material?.id || null;
        setResult(json.data.summary, json.data.web_sources);
        loadMaterials();
    } catch (error) {
        setError(error);
    }
}

async function makeQuiz() {
    const payload = formPayload();
    if (!payload.content || payload.content.length < 40) {
        alert('Materi minimal 40 karakter. Ketik materi atau upload file dulu.');
        return;
    }

    const questionType = document.getElementById('quick_qtype').value;
    const totalQuestions = parseInt(document.getElementById('quick_qtotal').value || '5', 10);

    setLoading('Membuat kuis');
    try {
        const requestPayload = latestMaterialId
            ? { material_id: latestMaterialId, total_questions: totalQuestions, question_type: questionType }
            : { ...payload, total_questions: totalQuestions, question_type: questionType };
        const json = await apiPost('/api/ai/quiz', requestPayload);
        setResult(json.data.quiz, json.data.web_sources);
    } catch (error) {
        setError(error);
    }
}

async function makePlan() {
    const payload = formPayload();
    if (!payload.content || payload.content.length < 40) {
        alert('Materi minimal 40 karakter. Ketik materi atau upload file dulu.');
        return;
    }

    const deadline = new Date(Date.now() + 7 * 24 * 60 * 60 * 1000).toISOString().slice(0, 10);
    setLoading('Menyusun rencana belajar');
    try {
        const requestPayload = latestMaterialId ? { material_id: latestMaterialId, deadline } : { ...payload, deadline };
        const json = await apiPost('/api/ai/study-plan', requestPayload);
        setResult(json.data.study_plan, json.data.web_sources);
    } catch (error) {
        setError(error);
    }
}

async function sendDashChat(event) {
    event.preventDefault();
    const input = document.getElementById('dashChatInput');
    const message = input.value.trim();
    if (!message) {
        return;
    }

    const content = getContent();
    if (!content || content.length < 20) {
        document.getElementById('result').innerHTML = '<div style="color:#94a3b8;"><i class="bi bi-info-circle me-1"></i>Ketik materi atau upload file dulu, baru bisa chat.</div>';
        return;
    }

    input.value = '';
    const resultBox = document.getElementById('result');
    if (resultBox.querySelector('.result-empty')) {
        resultBox.innerHTML = '';
    }

    resultBox.innerHTML += `<div class="chat-row user"><img class="chat-avatar" src="{{ asset('assets/images/avatar-user.png') }}" alt="Avatar user" onerror="this.onerror=null;this.src='{{ $fallbackImg }}';"><div class="mb-2 mt-3 p-3 rounded text-end" style="background:var(--gradient-card);color:white;width:fit-content;max-width:85%;"><b>Kamu:</b> ${escapeHtml(message)}</div></div>`;

    const loadingId = 'loading-' + Date.now();
    resultBox.innerHTML += `<div id="${loadingId}" class="chat-row ai"><img class="chat-avatar" src="{{ asset('assets/images/avatar-ai.png') }}" alt="Avatar AI" onerror="this.onerror=null;this.src='{{ $fallbackImg }}';"><div class="mb-2 p-3 rounded" style="background:rgba(255,255,255,.06);border:1px solid rgba(255,255,255,.1);color:#e2e8f0;width:fit-content;max-width:85%;"><span class="spinner-border spinner-border-sm me-2"></span>AI sedang berpikir...</div></div>`;
    resultBox.scrollTo({ top: resultBox.scrollHeight, behavior: 'smooth' });

    setStatus('Memproses', 'warning');
    dashChatHistory.push({ role: 'user', content: message });

    try {
        const json = await apiPost('/api/ai/chat', { content, message, history: dashChatHistory.slice(0, -1) });
        document.getElementById(loadingId)?.remove();
        resultBox.innerHTML += `<div class="chat-row ai"><img class="chat-avatar" src="{{ asset('assets/images/avatar-ai.png') }}" alt="Avatar AI" onerror="this.onerror=null;this.src='{{ $fallbackImg }}';"><div class="mb-2 p-3 rounded" style="background:rgba(255,255,255,.055);border:1px solid rgba(255,255,255,.1);color:#e2e8f0;width:fit-content;max-width:85%;"><b>AI:</b><div class="mt-2">${parseMarkdown(json.data.ai_response)}</div></div></div>`;
        resultBox.scrollTo({ top: resultBox.scrollHeight, behavior: 'smooth' });
        setStatus('Berhasil', 'success');
        dashChatHistory.push({ role: 'assistant', content: json.data.ai_response });
    } catch (error) {
        document.getElementById(loadingId)?.remove();
        dashChatHistory.pop();
        setError(error);
    }
}

async function loadMaterials() {
    try {
        const response = await fetch('/api/materials?limit=5');
        const json = await response.json();
        const wrapper = document.getElementById('materials-list');
        wrapper.innerHTML = '';

        if (!json.data.length) {
            wrapper.innerHTML = `<div class="materials-empty-state"><div class="materials-empty-visual" aria-hidden="true"><span class="materials-file-line line-one"></span><span class="materials-file-line line-two"></span><span class="materials-file-line line-three"></span><i class="bi bi-folder2-open"></i></div><div class="materials-empty-text"><div class="fw-semibold" style="color:#334155; font-size:.92rem;">Belum ada materi tersimpan</div><div style="color:#94a3b8; font-size:.8rem; margin-top:.25rem;">Mulai dengan memasukkan materi di atas.</div></div></div>`;
            return;
        }

        json.data.forEach(item => {
            const div = document.createElement('div');
            div.className = 'material-item mb-2';
            div.onclick = () => loadMaterialDetail(item._id || item.id);
            div.innerHTML = `
                <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-file-earmark-text" style="color:var(--primary);"></i>
                    <div>
                        <div class="fw-bold" style="font-size:.92rem;">${escapeHtml(item.title)}</div>
                        <div style="color:var(--text-muted); font-size:.8rem;">${escapeHtml(item.subject)}</div>
                    </div>
                </div>`;
            wrapper.appendChild(div);
        });
    } catch (error) {
        // Dashboard can still run even if MongoDB is not connected.
    }
}

async function loadMaterialDetail(id) {
    try {
        const response = await fetch('/api/materials/' + id);
        const json = await response.json();
        if (!json.data) {
            return;
        }

        document.getElementById('title').value = json.data.title;
        document.getElementById('subject').value = json.data.subject;
        document.getElementById('content').value = json.data.content;
        uploadedContent = json.data.content || '';
        latestMaterialId = json.data._id || json.data.id;

        if (json.data.latest_ai_result) {
            let parsedResult = json.data.latest_ai_result;
            try {
                parsedResult = JSON.parse(parsedResult);
            } catch (error) {
                // Keep the original text result.
            }
            setResult(parsedResult);
            setStatus('Riwayat AI', 'cyan');
        } else {
            document.getElementById('result').innerHTML = '<div style="opacity:.65;">Belum ada riwayat AI untuk materi ini. Gunakan tombol Ringkasan, Kuis, Rencana Belajar, atau chat di bawah.</div>';
            setStatus('Siap digunakan', 'soft');
        }

        const manualTab = document.querySelector('[data-bs-target="#home-manual"]');
        if (manualTab && window.bootstrap) {
            new bootstrap.Tab(manualTab).show();
        }
        document.getElementById('input-section')?.scrollIntoView({ behavior: 'smooth' });
    } catch (error) {
        alert('Gagal memuat detail materi.');
    }
}

async function copyResult() {
    const text = document.getElementById('result').innerText.trim();
    if (!text) {
        alert('Belum ada hasil untuk disalin.');
        return;
    }

    try {
        await navigator.clipboard.writeText(text);
        setStatus('Hasil disalin', 'success');
    } catch (error) {
        alert('Browser tidak mengizinkan copy otomatis.');
    }
}

window.addEventListener('load', () => {
    if (initialFeature) {
        window.setTimeout(() => jumpToFeature(initialFeature), 220);
    }
});
</script>
@endpush
