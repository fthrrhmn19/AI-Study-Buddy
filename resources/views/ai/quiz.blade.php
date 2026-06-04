@extends('layouts.app')

@section('content')
@php
    $featureImagePath = public_path('assets/images/feature-quiz.png');
    $featureImageUrl = asset('assets/images/feature-quiz.png').(is_file($featureImagePath) ? '?v='.filemtime($featureImagePath) : '');
@endphp
<div class="feature-workspace feature-quiz">
    <section class="feature-page-hero animate-fade-up">
        <div>
            <span class="feature-kicker"><i class="bi bi-patch-question"></i> Quiz Generator</span>
            <h1>Buat latihan soal dari materi yang sama</h1>
            <p>Ubah catatan kuliah, dokumen, atau ringkasan menjadi kuis pilihan ganda, essay, atau campuran lengkap dengan jawaban.</p>
        </div>
        <div class="feature-hero-card" aria-label="Generator Kuis">
            <div class="feature-hero-visual">
                <span class="feature-hero-ring"></span>
                <img class="feature-hero-robot" src="{{ $featureImageUrl }}" alt="Ilustrasi Quiz Generator" onerror="this.onerror=null;this.src='{{ asset('assets/icons/fallback-illustration.svg') }}';">
                <span class="feature-hero-chip chip-top"><i class="bi bi-ui-checks-grid"></i> Soal terstruktur</span>
                <span class="feature-hero-chip chip-bottom"><i class="bi bi-check2-circle"></i> Jawaban lengkap</span>
            </div>
            <div class="feature-hero-meta">
                <span>Fokus halaman</span>
                <b>Generator Kuis</b>
            </div>
        </div>
    </section>

<div class="row g-4 animate-fade-up feature-shell">
    <!-- Input Panel -->
    <div class="col-lg-5">
        <div class="card p-4 feature-panel">
            <div class="section-title mb-3">
                <span class="icon-box" style="background:rgba(16,185,129,.1); color:var(--success);"><i class="bi bi-patch-question"></i></span>
                AI Pembuat Kuis
            </div>
            <p style="color:var(--text-muted); font-size:.85rem;">Ketik materi manual atau upload file (PDF, DOCX, TXT, gambar), lalu buat latihan soal otomatis.</p>

            <!-- Tabs -->
            <ul class="nav nav-tabs mb-3" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="tab-manual" data-bs-toggle="tab" data-bs-target="#pane-manual" type="button" role="tab" style="font-size:.85rem;">
                        <i class="bi bi-pencil-square me-1"></i> Ketik Manual
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="tab-upload" data-bs-toggle="tab" data-bs-target="#pane-upload" type="button" role="tab" style="font-size:.85rem;">
                        <i class="bi bi-cloud-upload me-1"></i> Upload File
                    </button>
                </li>
            </ul>

            <div class="tab-content">
                <!-- Tab Manual -->
                <div class="tab-pane fade show active" id="pane-manual" role="tabpanel">
                    <div class="mb-3">
                        <label class="form-label">Judul Materi</label>
                        <input id="title" class="form-control" placeholder="Contoh: Konsep Dasar AI">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Topik / Mata Kuliah</label>
                        <input id="subject" class="form-control" placeholder="Contoh: Kecerdasan Buatan">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Isi Materi</label>
                        <textarea id="content" class="form-control" placeholder="Tempel materi kuliah di sini... minimal 40 karakter"></textarea>
                        <div class="small mt-1" style="color:var(--text-muted);" id="charCount">0 karakter</div>
                    </div>
                        <button class="btn btn-primary w-100 mt-2" onclick="document.getElementById('chatInput').focus()" type="button">
                            <i class="bi bi-arrow-right me-1"></i> Lanjut ke Chat & Aksi
                        </button>
                </div>

                <!-- Tab Upload -->
                <div class="tab-pane fade" id="pane-upload" role="tabpanel">
                    <div class="mb-3">
                        <label class="form-label">Judul Dokumen</label>
                        <input id="uploadTitle" class="form-control" placeholder="Contoh: Buku Kecerdasan Buatan">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Topik / Mata Kuliah</label>
                        <input id="uploadSubject" class="form-control" placeholder="Contoh: Kecerdasan Buatan">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">File Dokumen</label>
                        <div id="dropZone" class="text-center p-4 rounded-4" style="border:2px dashed var(--border); cursor:pointer; transition:all .2s;" onclick="document.getElementById('fileInput').click()" ondragover="event.preventDefault();this.style.borderColor='var(--primary)';this.style.background='rgba(79,70,229,.05)'" ondragleave="this.style.borderColor='var(--border)';this.style.background=''" ondrop="handleDrop(event)">
                            <i class="bi bi-cloud-arrow-up fs-2 d-block mb-2" style="color:var(--primary); opacity:.6;"></i>
                            <div class="small fw-bold" style="color:var(--text);">Klik atau seret file ke sini</div>
                            <div class="small" style="color:var(--text-muted);">PDF, DOCX, TXT, JPG, PNG (maks 10MB)</div>
                        </div>
                        <input type="file" id="fileInput" accept=".txt,.pdf,.docx,.jpg,.jpeg,.png" style="display:none" onchange="showFileInfo(this)">
                        <div id="fileInfo" class="small mt-2 d-none p-2 rounded" style="background:var(--surface-alt);"></div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Teks Manual / OCR <span class="badge badge-soft" style="font-size:.6rem;">Opsional</span></label>
                        <textarea id="ocrText" class="form-control" rows="3" placeholder="Untuk gambar: paste teks dari foto di sini..."></textarea>
                    </div>
                    <button class="btn btn-primary w-100" onclick="uploadFile()" id="btnUpload">
                        <i class="bi bi-cloud-upload me-1"></i> Upload Materi
                    </button>
                </div>
            </div>
        </div>

    </div>

    <!-- Result + Chat Panel -->
    <div class="col-lg-7 animate-fade-up stagger-2">
        <div class="card d-flex flex-column feature-panel feature-result-panel" style="min-height:620px;">
            <div class="p-3 d-flex flex-column" style="border-bottom:1px solid var(--border);">
                <div class="d-flex align-items-center justify-content-between">
                    <div class="section-title" style="font-size:1.1rem;">
                        <span class="icon-box" style="background:rgba(6,182,212,.1); color:var(--accent); width:34px; height:34px; font-size:1rem;"><i class="bi bi-cpu"></i></span>
                        Hasil AI
                    </div>
                    <span id="status" class="badge badge-soft">Siap digunakan</span>
                </div>
                <!-- Token Usage Bar -->
                <div id="usageBar" class="mt-2" style="display:none;">
                    <div class="d-flex flex-wrap gap-2 align-items-center" style="font-size:.72rem;">
                        <span class="d-flex align-items-center gap-1" style="color:var(--text-muted);"><i class="bi bi-lightning-charge" style="color:var(--warning);"></i> Token: <b id="usageTokens" style="color:var(--text);">-</b></span>
                        <span class="d-flex align-items-center gap-1" style="color:var(--text-muted);"><i class="bi bi-arrow-up" style="color:var(--primary);"></i> Prompt: <b id="usagePrompt">-</b></span>
                        <span class="d-flex align-items-center gap-1" style="color:var(--text-muted);"><i class="bi bi-arrow-down" style="color:var(--success);"></i> Reply: <b id="usageCompletion">-</b></span>
                        <span id="usageLimitWrap" class="d-flex align-items-center gap-1" style="color:var(--text-muted); display:none;"><i class="bi bi-speedometer2" style="color:var(--accent);"></i> Sisa: <b id="usageRemaining" style="color:var(--text);">-</b> / <span id="usageLimit">-</span></span>
                    </div>
                    <div class="progress mt-1" style="height:4px; border-radius:4px; background:var(--border);">
                        <div id="usageProgressBar" class="progress-bar" style="width:0%; background:var(--gradient-card); border-radius:4px; transition:width .5s ease;"></div>
                    </div>
                </div>
            </div>

            <!-- Dokumen Aktif (Pindahan) -->
            <div class="p-2 mx-3 mt-3 rounded" id="docListCard" style="display:none; background:rgba(245,158,11,.08); border:1px solid rgba(245,158,11,.2);">
                <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                    <div class="fw-bold d-flex align-items-center gap-2" style="font-size:.85rem; color:var(--warning);">
                        <i class="bi bi-file-earmark-text"></i> Dokumen Aktif
                    </div>
                    <div class="d-flex gap-2 flex-grow-1" style="max-width:400px; margin-left:auto;">
                        <select id="docSelect" class="form-select form-select-sm flex-grow-1" onchange="onDocSelect()">
                            <option value="">-- Pilih Dokumen --</option>
                        </select>
                        <select id="chapSelect" class="form-select form-select-sm" style="display:none; width:130px;">
                            <option value="">Semua Materi</option>
                        </select>
                    </div>
                </div>
                <div id="docActions" style="display:none;"></div>
            </div>

            <div class="flex-grow-1 overflow-auto p-3" id="chatArea" style="min-height:380px; max-height:460px; background:var(--surface-alt);">
                <div class="text-center py-5" style="color:var(--text-muted);">
                    <i class="bi bi-patch-question fs-1 d-block mb-3" style="opacity:.3;"></i>
                    <b style="color:var(--text);">Kuis siap dibuat</b><br>
                    <div class="small mt-2" style="max-width:300px; margin:0 auto;">
                        1. Ketik/Upload materi di panel sebelah kiri.<br>
                        2. Klik "Buat Kuis" di bawah, atau langsung ketik pertanyaan Anda!
                    </div>
                </div>
            </div>

            <!-- Chat Input -->
            <div class="p-3" style="border-top:1px solid var(--border);">
                <!-- Quick Actions -->
                <div class="d-flex flex-wrap gap-2 mb-3" id="quickActions">
                    <button class="btn btn-sm btn-outline-success rounded-pill fw-bold" onclick="triggerQuiz()" id="btnQuiz">
                        <i class="bi bi-patch-question"></i> Buat Kuis (5 Soal)
                    </button>
                </div>
                
                <form onsubmit="sendChat(event)">
                    <div class="input-group">
                        <input type="text" id="chatInput" class="form-control" placeholder="Tanya tentang materi... (contoh: jelaskan poin penting)" style="border-radius:12px 0 0 12px;">
                        <button class="btn btn-primary px-4 fw-bold" type="submit" id="btnChat" style="border-radius:0 12px 12px 0;">
                            <i class="bi bi-send me-1"></i> Kirim
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
</div>
@endsection

@push('scripts')
<script>
let currentDocId = null;
let latestMaterialId = null;
let currentGuestDoc = null;
let activeHistoryId = null;

// === UTILS ===
function parseMarkdown(t) { return t ? t.replace(/\*\*(.*?)\*\*/g,'<b>$1</b>').replace(/\n/g,'<br>') : ''; }

function setStatus(text, type) {
    const s = document.getElementById('status');
    s.className = 'badge badge-soft' + (type ? '-'+type : '');
    s.innerText = text;
}

function errorMessage(error) {
    const raw = String(error?.error || error?.message || 'Terjadi error saat memproses permintaan.');
    const normalized = raw.toLowerCase();
    if (normalized.includes('rate_limit') || normalized.includes('request too large') || normalized.includes('413') || normalized.includes('token') || normalized.includes('groq api error')) {
        return 'Materi terlalu panjang untuk limit Groq saat ini. Coba pilih bab tertentu, upload materi yang lebih fokus, atau ulangi beberapa saat lagi.';
    }
    return raw.length > 220 ? raw.slice(0, 220) + '...' : raw;
}

function updateUsage(usage) {
    if (!usage) return;
    const bar = document.getElementById('usageBar');
    bar.style.display = 'block';
    document.getElementById('usageTokens').innerText = (usage.total_tokens || 0).toLocaleString();
    document.getElementById('usagePrompt').innerText = (usage.prompt_tokens || 0).toLocaleString();
    document.getElementById('usageCompletion').innerText = (usage.completion_tokens || 0).toLocaleString();
    if (usage.limit_tokens && usage.limit_tokens > 0) {
        document.getElementById('usageLimitWrap').style.display = 'flex';
        document.getElementById('usageRemaining').innerText = (usage.remaining_tokens || 0).toLocaleString();
        document.getElementById('usageLimit').innerText = (usage.limit_tokens || 0).toLocaleString();
        const pct = Math.max(0, Math.min(100, ((usage.limit_tokens - usage.remaining_tokens) / usage.limit_tokens) * 100));
        const pb = document.getElementById('usageProgressBar');
        pb.style.width = pct + '%';
        pb.style.background = pct > 80 ? 'var(--danger)' : pct > 50 ? 'var(--warning)' : 'var(--gradient-card)';
    } else {
        document.getElementById('usageLimitWrap').style.display = 'none';
    }
}

function appendBubble(role, html) {
    const area = document.getElementById('chatArea');
    if (area.querySelector('.text-center')) area.innerHTML = '';
    const div = document.createElement('div');
    div.className = `animate-fade-up chat-row ${role==='user'?'user':'ai'}`;
    const label = role==='user' ? 'Kamu' : '<i class="bi bi-cpu me-1"></i>AI Groq';
    const cls = role==='user' ? 'chat-bubble-user' : 'chat-bubble-ai';
    const avatar = role==='user' ? "{{ asset('assets/images/avatar-user.png') }}" : "{{ asset('assets/images/avatar-ai.png') }}";
    div.innerHTML = `<img class="chat-avatar" src="${avatar}" alt="${role==='user'?'Avatar user':'Avatar AI'}" onerror="this.onerror=null;this.src='{{ asset('assets/icons/fallback-illustration.svg') }}';"><div class="${role==='user'?'text-end':'text-start'}"><div class="small mb-1" style="color:var(--text-muted);">${label}</div><div class="chat-bubble ${cls}">${html}</div></div>`;
    area.appendChild(div);
    area.scrollTo({top:area.scrollHeight, behavior:'smooth'});
}

function renderQuiz(data) {
    if (typeof data === 'string') {
        try { data = JSON.parse(data); } catch(e) { return parseMarkdown(data); }
    }
    if (!data || !Array.isArray(data.questions)) {
        if (data && data.raw) {
            return `<div class="mb-2 p-2 rounded" style="background:rgba(245,158,11,.1);border:1px solid rgba(245,158,11,.25);color:#92400e;">AI mengembalikan format kuis mentah. Isi tetap ditampilkan.</div>${parseMarkdown(data.raw)}`;
        }
        return parseMarkdown(JSON.stringify(data || {}, null, 2));
    }
    if (!data.questions.length) {
        if (data.raw) {
            return `<div class="mb-2 p-2 rounded" style="background:rgba(245,158,11,.1);border:1px solid rgba(245,158,11,.25);color:#92400e;">AI mengembalikan format kuis mentah. Isi tetap ditampilkan.</div>${parseMarkdown(data.raw)}`;
        }
        return '<div style="color:var(--text-muted);">Belum ada soal pada hasil ini.</div>';
    }
    const multipleChoice = data.questions.filter(q => {
        const options = Array.isArray(q.options) ? q.options : [];
        return q.type !== 'essay' && options.length;
    });
    const essays = data.questions.filter(q => {
        const options = Array.isArray(q.options) ? q.options : [];
        return q.type === 'essay' || !options.length;
    });
    const renderQuestion = (q, number, isEssay) => {
        const options = Array.isArray(q.options) ? q.options : [];
        const answer = q.answer_key || q.answer || '-';
        if(isEssay){
            return `<div class="quiz-card"><div class="d-flex align-items-center gap-2 mb-2"><span class="badge badge-soft"><i class="bi bi-pencil me-1"></i>Essay</span><b style="color:var(--primary);">${number}. ${q.question}</b></div><div class="mt-2 p-2 rounded" style="background:var(--surface-alt);border-left:3px solid var(--success);"><div class="small fw-bold mb-1" style="color:var(--success);"><i class="bi bi-key me-1"></i>Kunci Jawaban:</div><div style="font-size:.88rem;line-height:1.7;">${q.answer_key||q.answer||'-'}</div></div>${q.explanation?`<div style="color:var(--text-muted);" class="small mt-2"><b>Pembahasan:</b> ${q.explanation}</div>`:''}</div>`;
        }
        return `<div class="quiz-card"><div class="d-flex align-items-center gap-2 mb-2"><span class="badge badge-soft-warning"><i class="bi bi-list-check me-1"></i>PG</span><b style="color:var(--primary);">${number}. ${q.question}</b></div><div class="ps-3 mt-2 mb-2">${options.map(o=>`<div class="mb-1">${o}</div>`).join('')}</div><div style="color:var(--success);" class="mt-1 fw-bold">Jawaban: ${answer}</div>${q.explanation?`<div style="color:var(--text-muted);" class="small mt-1"><b>Pembahasan:</b> ${q.explanation}</div>`:''}</div>`;
    };
    let html = '';
    if (multipleChoice.length) {
        html += `<div class="quiz-section-title"><i class="bi bi-list-check"></i> Pilihan Ganda 1-${multipleChoice.length}</div>`;
        html += multipleChoice.map((q, i) => renderQuestion(q, i + 1, false)).join('');
    }
    if (essays.length) {
        html += `<div class="quiz-section-title essay"><i class="bi bi-pencil-square"></i> Essay 1-${essays.length}</div>`;
        html += essays.map((q, i) => renderQuestion(q, i + 1, true)).join('');
    }
    return html;
}

function renderPlan(data) {
    if (typeof data==='string') return parseMarkdown(data);
    let h='';
    if(data.goal) h+=`<div class="mb-2"><b style="color:var(--primary);">Tujuan:</b> ${data.goal}</div>`;
    if(data.steps&&data.steps.length){h+='<div class="mb-2"><b style="color:var(--primary);">Langkah:</b></div>';data.steps.forEach(s=>{h+=`<div class="quiz-card"><div class="d-flex justify-content-between"><b style="color:var(--primary);">${s.day}</b><span class="badge badge-soft">${s.duration}</span></div><div class="mt-1 text-muted" style="font-size:.95rem;">${s.task}</div></div>`;});}
    if(data.tips&&data.tips.length){h+='<div class="mb-2 mt-3"><b style="color:var(--primary);">Tips:</b></div><ul>';data.tips.forEach(t=>h+=`<li class="text-muted" style="font-size:.95rem;">${t}</li>`);h+='</ul>';}
    return h||parseMarkdown(JSON.stringify(data,null,2));
}

function renderAI(data) {
    if(typeof data==='string') return parseMarkdown(data);
    if(!data) return '<span style="color:var(--text-muted);">Tidak ada hasil AI yang bisa ditampilkan.</span>';
    if(data.summary) return parseMarkdown(data.summary);
    if(data.questions || data.raw) return renderQuiz(data);
    if(data.goal) return renderPlan(data);
    return parseMarkdown(JSON.stringify(data,null,2));
}

function historyText(payload) {
    if (payload === null || payload === undefined) return '';
    if (typeof payload === 'string') return payload;
    try { return JSON.stringify(payload); } catch(e) { return String(payload); }
}

function rememberAiTurn(userText, answerPayload, historyId = null) {
    if (historyId) activeHistoryId = historyId;
    if (userText) appChatHistory.push({role: 'user', content: userText});
    const answerText = historyText(answerPayload);
    if (answerText) appChatHistory.push({role: 'assistant', content: answerText});
}

function setActiveHistoryId(id) {
    if (id) activeHistoryId = id;
}

async function apiPost(url, payload) {
    const r = await fetch(url, {method:'POST', headers:{'Content-Type':'application/json','Accept':'application/json'}, body:JSON.stringify(payload)});
    const j = await r.json(); if(!r.ok) throw j; return j;
}

function renderWebSources(sources) {
    if(!sources||!sources.length) return '';
    let h='<div class="web-sources mt-3"><div class="small fw-bold mb-2" style="color:var(--accent);"><i class="bi bi-globe2 me-1"></i>Referensi Web (You.com)</div>';
    sources.forEach(s=>{
        const domain=s.url?new URL(s.url).hostname:'';
        h+=`<div class="web-source-item"><div><a href="${s.url}" target="_blank">${s.title||domain}</a><div class="desc">${(s.description||'').substring(0,120)}${s.description&&s.description.length>120?'...':''}</div></div></div>`;
    });
    return h+'</div>';
}

function btnLoading(id,loading,originalHtml) {
    const b=document.getElementById(id);
    if (!b) return;
    b.disabled=loading;
    b.innerHTML=loading?'<span class="spinner-border spinner-border-sm me-2"></span>Memproses...':originalHtml;
}

// === CHAR COUNTER ===
document.getElementById('content').addEventListener('input',function(){
    document.getElementById('charCount').innerText=this.value.length+' karakter';
});

// === MANUAL INPUT ===
async function summarizeManual() {
    const p={title:document.getElementById('title').value,subject:document.getElementById('subject').value,content:document.getElementById('content').value,save:true};
    btnLoading('btnSummarize',true,''); setStatus('Meringkas + Web Search...','warning');
    try {
        const j=await apiPost('/api/ai/summarize',p);
        latestMaterialId=j?.data?.material?._id||j?.data?.material?.id||null;
        const summaryText = typeof j.data.summary==='string'?j.data.summary:j.data.summary.summary||JSON.stringify(j.data.summary);
        let html=parseMarkdown(summaryText);
        html+=renderWebSources(j.data.web_sources);
        appendBubble('ai',html);
        rememberAiTurn('Ringkas materi: '+(p.title || 'Materi'), summaryText, j.data.history_id);
        setStatus('Berhasil','success'); updateUsage(j.usage);
    } catch(e) { appendBubble('ai',`<span style="color:#fca5a5;"><i class="bi bi-exclamation-triangle me-1"></i>${errorMessage(e)}</span>`); setStatus('Gagal','danger'); }
    finally { btnLoading('btnSummarize',false,'<i class="bi bi-stars me-1"></i> Ringkasan'); }
}

async function quizManual() {
    const p = latestMaterialId ? {material_id:latestMaterialId,total_questions:5,question_type:'campuran'} : {title:document.getElementById('title').value,subject:document.getElementById('subject').value,content:document.getElementById('content').value,total_questions:5,question_type:'campuran'};
    btnLoading('btnQuiz',true,''); setStatus('Membuat Quiz + Web...','warning');
    try {
        const j=await apiPost('/api/ai/quiz',p);
        let html=renderQuiz(j.data.quiz)+renderWebSources(j.data.web_sources);
        appendBubble('ai',html);
        rememberAiTurn('Buat kuis dari materi: '+(document.getElementById('title').value || 'Materi'), j.data.quiz, j.data.history_id);
        setStatus('Berhasil','success'); updateUsage(j.usage);
    } catch(e) { appendBubble('ai',`<span style="color:#fca5a5;">${errorMessage(e)}</span>`); setStatus('Gagal','danger'); }
    finally { btnLoading('btnQuiz',false,'<i class="bi bi-patch-question me-1"></i> Quiz'); }
}

async function planManual() {
    const deadline=new Date(Date.now()+7*86400000).toISOString().slice(0,10);
    const p = latestMaterialId ? {material_id:latestMaterialId,deadline} : {title:document.getElementById('title').value,subject:document.getElementById('subject').value,content:document.getElementById('content').value,deadline};
    btnLoading('btnPlan',true,''); setStatus('Menyusun Rencana + Web...','warning');
    try {
        const j=await apiPost('/api/ai/study-plan',p);
        let html=renderPlan(j.data.study_plan)+renderWebSources(j.data.web_sources);
        appendBubble('ai',html);
        rememberAiTurn('Buat rencana belajar: '+(document.getElementById('title').value || 'Materi'), j.data.study_plan, j.data.history_id);
        setStatus('Berhasil','success'); updateUsage(j.usage);
    } catch(e) { appendBubble('ai',`<span style="color:#fca5a5;">${errorMessage(e)}</span>`); setStatus('Gagal','danger'); }
    finally { btnLoading('btnPlan',false,'<i class="bi bi-calendar-check me-1"></i> Rencana'); }
}

// === UPLOAD ===
function handleDrop(e) {
    e.preventDefault(); e.target.closest('#dropZone').style.borderColor='var(--border)'; e.target.closest('#dropZone').style.background='';
    if(e.dataTransfer.files.length) { document.getElementById('fileInput').files=e.dataTransfer.files; showFileInfo(document.getElementById('fileInput')); }
}

function showFileInfo(input) {
    const info=document.getElementById('fileInfo');
    if(input.files[0]) {
        const f=input.files[0]; const size=(f.size/1024).toFixed(1);
        info.innerHTML=`<i class="bi bi-file-earmark-check me-1" style="color:var(--success);"></i><b>${f.name}</b> (${size} KB)`;
        info.classList.remove('d-none');
    }
}

async function uploadFile() {
    const fd=new FormData();
    fd.append('title',document.getElementById('uploadTitle').value);
    fd.append('subject',document.getElementById('uploadSubject').value);
    const f=document.getElementById('fileInput');
    if(f.files[0]) fd.append('file',f.files[0]);
    const ocr=document.getElementById('ocrText').value;
    if(ocr) fd.append('ocr_text',ocr);
    if(!f.files[0]&&!ocr){alert('Pilih file atau isi teks manual.');return;}

    btnLoading('btnUpload',true,''); setStatus('Mengupload...','warning');
    try {
        const r=await fetch('/api/documents',{method:'POST',body:fd,headers:{'Accept':'application/json'}});
        const j=await r.json(); if(!r.ok) throw new Error(j.error||j.message||'Error');
        currentDocId=j.data._id||j.data.id;
        appendBubble('ai',`<div><b style="color:#34d399;">Dokumen berhasil diupload.</b><br><b>File:</b> ${j.data.file_name}<br><b>Bab terdeteksi:</b> ${j.data.chapters?j.data.chapters.length:0}<br><br>Gunakan tombol di bawah untuk meringkas atau membuat quiz dari dokumen ini.</div>`);
        setStatus('Upload Berhasil','success');
        loadDocList();
    } catch(e) { appendBubble('ai',`<span style="color:#fca5a5;">${errorMessage(e)}</span>`); setStatus('Gagal','danger'); }
    finally { btnLoading('btnUpload',false,'<i class="bi bi-cloud-upload me-1"></i> Upload & Proses'); }
}

// === DOCUMENT LIST ===
async function loadDocList() {
    try {
        const r=await fetch('/api/documents'); const j=await r.json();
        if(j.data.length) {
            document.getElementById('docListCard').style.display='block';
            const sel=document.getElementById('docSelect');
            sel.innerHTML='<option value="">-- Pilih Dokumen --</option>';
            j.data.forEach(d=>{sel.innerHTML+=`<option value="${d._id||d.id}" ${(d._id||d.id)===currentDocId?'selected':''}>${d.title}</option>`;});
            if(currentDocId) onDocSelect();
        }
    }catch(e){}
}

function onDocSelect() {
    const id=document.getElementById('docSelect').value;
    if(!id){document.getElementById('docActions').style.display='none';document.getElementById('chapSelect').style.display='none';currentDocId=null;return;}
    currentDocId=id;
    document.getElementById('docActions').style.display='grid';
    // Load chapters
    fetch('/api/documents/'+id).then(r=>r.json()).then(j=>{
        const cs=document.getElementById('chapSelect');
        cs.innerHTML='<option value="">Semua Materi</option>';
        if(j.data.chapters&&j.data.chapters.length>1){
            j.data.chapters.forEach(c=>{cs.innerHTML+=`<option value="${c.chapter}">${c.chapter} ${c.title?' - '+c.title:''}</option>`;});
            cs.style.display='block';
        }else{cs.style.display='none';}
    });
}

async function docSummarize() {
    if(!currentDocId) return;
    const chap=document.getElementById('chapSelect').value;
    btnLoading('btnSummarize',true,''); setStatus('Meringkas Dokumen...','warning');
    appendBubble('user','Ringkaskan '+(chap||'semua materi'));
    try {
        const j=await apiPost(`/api/documents/${currentDocId}/summarize`,{chapter:chap});
        let d=j.summary ?? j.data?.ai_response;
        if (typeof d === 'string') { try{d=JSON.parse(d);}catch(e){} }
        appendBubble('ai',renderAI(d)); setStatus('Berhasil','success'); updateUsage(j.usage);
    }catch(e){appendBubble('ai',`<span style="color:#fca5a5;">${errorMessage(e)}</span>`);setStatus('Gagal','danger');}
    finally { btnLoading('btnSummarize',false,'<i class="bi bi-stars"></i> Ringkas Materi'); }
}

async function docQuiz() {
    if(!currentDocId) return;
    const chap=document.getElementById('chapSelect').value;
    btnLoading('btnQuiz',true,'');
    setStatus('Membuat Quiz Dokumen...','warning');
    appendBubble('user','Buatkan 5 soal dari '+(chap||'semua materi'));
    try {
        const j=await apiPost(`/api/documents/${currentDocId}/quiz`,{chapter:chap,total_questions:5});
        let d=j.quiz ?? j.data?.ai_response;
        if (typeof d === 'string') { try{d=JSON.parse(d);}catch(e){} }
        appendBubble('ai',renderAI(d)); setStatus('Berhasil','success'); updateUsage(j.usage);
    }catch(e){appendBubble('ai',`<span style="color:#fca5a5;">${errorMessage(e)}</span>`);setStatus('Gagal','danger');}
    finally { btnLoading('btnQuiz',false,'<i class="bi bi-patch-question"></i> Buat Kuis (5 Soal)'); }
}

async function docPlan() {
    if(!currentDocId) return;
    const chap=document.getElementById('chapSelect').value;
    btnLoading('btnPlan',true,'');
    setStatus('Menyusun Rencana Dokumen...','warning');
    appendBubble('user','Buatkan rencana belajar dari '+(chap||'semua materi'));
    try {
        const j=await apiPost(`/api/documents/${currentDocId}/study-plan`,{chapter:chap});
        let d=j.study_plan ?? j.data?.ai_response;
        if (typeof d === 'string') { try{d=JSON.parse(d);}catch(e){} }
        appendBubble('ai',renderAI(d)); setStatus('Berhasil','success'); updateUsage(j.usage);
    }catch(e){appendBubble('ai',`<span style="color:#fca5a5;">${errorMessage(e)}</span>`);setStatus('Gagal','danger');}
    finally { btnLoading('btnPlan',false,'<i class="bi bi-calendar-check"></i> Rencana Belajar'); }
}

// === QUICK ACTIONS ===
function triggerSummarize() {
    if (currentDocId) { docSummarize(); }
    else if (document.getElementById('content').value.trim().length >= 40) { summarizeManual(); }
    else { alert('Silakan isi materi minimal 40 karakter di panel kiri atau upload dokumen.'); }
}

function triggerQuiz() {
    if (currentDocId) { docQuiz(); }
    else if (document.getElementById('content').value.trim().length >= 40) { quizManual(); }
    else { alert('Silakan isi materi minimal 40 karakter di panel kiri atau upload dokumen.'); }
}

function triggerPlan() {
    if (currentDocId) { docPlan(); }
    else if (document.getElementById('content').value.trim().length >= 40) { planManual(); }
    else { alert('Silakan isi materi minimal 40 karakter di panel kiri atau upload dokumen.'); }
}

// === CHAT ===
let appChatHistory = [];

async function sendChat(e) {
    e.preventDefault();
    const msg=document.getElementById('chatInput').value.trim();
    if(!msg) return;
    document.getElementById('chatInput').value='';
    appendBubble('user',msg);
    
    // Add to history
    appChatHistory.push({role: 'user', content: msg});

    // Show typing indicator
    const loadDiv=document.createElement('div');loadDiv.id='typing';loadDiv.className='animate-fade-up d-flex flex-column align-items-start mb-3';
    loadDiv.innerHTML='<div class="small mb-1" style="color:var(--text-muted);"><i class="bi bi-cpu me-1"></i>AI Groq</div><div class="chat-bubble chat-bubble-ai"><span class="spinner-border spinner-border-sm me-2"></span>Sedang berpikir...</div>';
    document.getElementById('chatArea').appendChild(loadDiv);
    document.getElementById('chatArea').scrollTo({top:document.getElementById('chatArea').scrollHeight,behavior:'smooth'});

    // If document selected, chat with document
    if(currentDocId) {
        const chap=document.getElementById('chapSelect').value;
        try {
            const j=await apiPost(`/api/documents/${currentDocId}/chat`,{message:msg,chapter:chap,history:appChatHistory.slice(0,-1)});
            document.getElementById('typing')?.remove();
            let d=j.data.ai_response; try{d=JSON.parse(d);}catch(e){} 
            appendBubble('ai',renderAI(d)); 
            appChatHistory.push({role: 'assistant', content: typeof d === 'string' ? d : JSON.stringify(d)});
            updateUsage(j.usage);
        }catch(e){
            document.getElementById('typing')?.remove();
            appendBubble('ai',`<span style="color:#fca5a5;">${errorMessage(e)}</span>`);
            appChatHistory.pop();
        }
    } else {
        // Chat with manual content
        const manualContent=document.getElementById('content').value.trim();
        if(manualContent.length < 20) {
            document.getElementById('typing')?.remove();
            appendBubble('ai','<span style="color:var(--text-muted);">Ketik materi terlebih dahulu di kolom "Isi Materi" (minimal 20 karakter), atau upload dokumen, lalu kamu bisa chat tentang materinya di sini.</span>');
            appChatHistory.pop();
            return;
        }
        try {
            const j=await apiPost('/api/ai/chat',{content:manualContent,message:msg,history:appChatHistory.slice(0,-1)});
            document.getElementById('typing')?.remove();
            const answerPayload = j.data.quiz || j.data.ai_response;
            appendBubble('ai', j.data.quiz ? renderQuiz(j.data.quiz) : parseMarkdown(j.data.ai_response)); 
            appChatHistory.push({role: 'assistant', content: typeof answerPayload === 'string' ? answerPayload : JSON.stringify(answerPayload)});
            updateUsage(j.usage);
        }catch(e){
            document.getElementById('typing')?.remove();
            appendBubble('ai',`<span style="color:#fca5a5;">${errorMessage(e)}</span>`);
            appChatHistory.pop();
        }
    }
}

const guestDocValue = '__guest_document__';

function activeChapter() {
    return document.getElementById('chapSelect').value;
}

function fillChapterSelect(chapters = []) {
    const cs = document.getElementById('chapSelect');
    cs.innerHTML = '<option value="">Semua Materi</option>';
    if (Array.isArray(chapters) && chapters.length > 1) {
        chapters.forEach(c => { cs.innerHTML += `<option value="${c.chapter}">${c.chapter} ${c.title ? ' - ' + c.title : ''}</option>`; });
        cs.style.display = 'block';
    } else {
        cs.style.display = 'none';
    }
}

function getGuestText(chapter) {
    if (!currentGuestDoc) return '';
    if (!chapter) return currentGuestDoc.extracted_text || '';
    const chapters = Array.isArray(currentGuestDoc.chapters) ? currentGuestDoc.chapters : [];
    const selected = chapters.find(c => c.chapter === chapter);
    return selected?.content || currentGuestDoc.extracted_text || '';
}

function setGuestDocument(doc) {
    currentGuestDoc = doc;
    currentDocId = null;
    const card = document.getElementById('docListCard');
    const sel = document.getElementById('docSelect');
    card.style.display = 'block';
    sel.innerHTML = `<option value="${guestDocValue}" selected>${doc.title || 'Materi sementara'}</option>`;
    fillChapterSelect(doc.chapters || []);
}

async function uploadFile() {
    const fd=new FormData();
    fd.append('title',document.getElementById('uploadTitle').value);
    fd.append('subject',document.getElementById('uploadSubject').value);
    const f=document.getElementById('fileInput');
    if(f.files[0]) fd.append('file',f.files[0]);
    const ocr=document.getElementById('ocrText').value;
    if(ocr) fd.append('ocr_text',ocr);
    if(!f.files[0]&&!ocr){alert('Pilih file atau isi teks manual.');return;}

    btnLoading('btnUpload',true,''); setStatus('Mengupload...','warning');
    try {
        const r=await fetch('/api/documents',{method:'POST',body:fd,headers:{'Accept':'application/json'}});
        const j=await r.json(); if(!r.ok) throw new Error(j.error||j.message||'Error');
        const id = j.data._id || j.data.id;
        if (id) {
            currentGuestDoc = null;
            currentDocId = id;
            loadDocList();
        } else {
            setGuestDocument(j.data);
        }
        appendBubble('ai',`<div><b style="color:#34d399;">Dokumen berhasil diekstrak.</b><br><b>File:</b> ${j.data.file_name || 'teks manual'}<br><b>Bab terdeteksi:</b> ${j.data.chapters?j.data.chapters.length:0}<br><br>${id ? 'Dokumen tersimpan di akunmu.' : 'Mode guest: dokumen ini hanya tersedia di halaman ini. Jika refresh/pindah halaman, silakan upload ulang.'}</div>`);
        setStatus('Upload Berhasil','success');
    } catch(e) { appendBubble('ai',`<span style="color:#fca5a5;">${errorMessage(e)}</span>`); setStatus('Gagal','danger'); }
    finally { btnLoading('btnUpload',false,'<i class="bi bi-cloud-upload me-1"></i> Upload & Proses'); }
}

async function loadDocList() {
    if (currentGuestDoc) {
        setGuestDocument(currentGuestDoc);
        return;
    }

    try {
        const r=await fetch('/api/documents'); const j=await r.json();
        const card = document.getElementById('docListCard');
        const sel=document.getElementById('docSelect');
        sel.innerHTML='<option value="">-- Pilih Dokumen --</option>';
        if(j.data.length) {
            card.style.display='block';
            j.data.forEach(d=>{sel.innerHTML+=`<option value="${d._id||d.id}" ${(d._id||d.id)===currentDocId?'selected':''}>${d.title}</option>`;});
            if(currentDocId) onDocSelect();
        } else {
            card.style.display='none';
            currentDocId = null;
            fillChapterSelect([]);
        }
    }catch(e){}
}

function onDocSelect() {
    const id=document.getElementById('docSelect').value;
    if(id === guestDocValue){ currentDocId=null; fillChapterSelect(currentGuestDoc?.chapters || []); return; }
    if(!id){document.getElementById('docActions').style.display='none';document.getElementById('chapSelect').style.display='none';currentDocId=null;return;}
    currentGuestDoc = null;
    currentDocId=id;
    document.getElementById('docActions').style.display='grid';
    fetch('/api/documents/'+id).then(r=>r.json()).then(j=>fillChapterSelect(j.data.chapters || []));
}

async function docSummarize() {
    const chap=activeChapter();
    btnLoading('btnSummarize',true,''); setStatus('Meringkas Dokumen...','warning');
    appendBubble('user','Ringkaskan '+(chap||'semua materi'));
    try {
        if (currentGuestDoc) {
            const j=await apiPost('/api/ai/summarize',{title:currentGuestDoc.title||'Materi sementara',subject:currentGuestDoc.subject||'Materi',content:getGuestText(chap),save:false});
            const summaryText = typeof j.data.summary==='string'?j.data.summary:JSON.stringify(j.data.summary);
            appendBubble('ai',parseMarkdown(summaryText));
            rememberAiTurn('Ringkaskan '+(chap||'semua materi'), summaryText, j.data.history_id);
            setStatus('Berhasil','success'); updateUsage(j.usage); return;
        }
        if(!currentDocId) return;
        const j=await apiPost(`/api/documents/${currentDocId}/summarize`,{chapter:chap});
        let d=j.summary ?? j.data?.ai_response;
        if (typeof d === 'string') { try{d=JSON.parse(d);}catch(e){} }
        appendBubble('ai',renderAI(d));
        rememberAiTurn('Ringkaskan '+(chap||'semua materi'), d);
        setStatus('Berhasil','success'); updateUsage(j.usage);
    }catch(e){appendBubble('ai',`<span style="color:#fca5a5;">${errorMessage(e)}</span>`);setStatus('Gagal','danger');}
    finally { btnLoading('btnSummarize',false,'<i class="bi bi-stars"></i> Ringkas Materi'); }
}

async function docQuiz() {
    const chap=activeChapter();
    btnLoading('btnQuiz',true,''); setStatus('Membuat Quiz Dokumen...','warning');
    appendBubble('user','Buatkan 5 soal dari '+(chap||'semua materi'));
    try {
        if (currentGuestDoc) {
            const j=await apiPost('/api/ai/quiz',{title:currentGuestDoc.title||'Materi sementara',subject:currentGuestDoc.subject||'Materi',content:getGuestText(chap),total_questions:5,question_type:'campuran'});
            appendBubble('ai',renderQuiz(j.data.quiz));
            rememberAiTurn('Buatkan 5 soal dari '+(chap||'semua materi'), j.data.quiz, j.data.history_id);
            setStatus('Berhasil','success'); updateUsage(j.usage); return;
        }
        if(!currentDocId) return;
        const j=await apiPost(`/api/documents/${currentDocId}/quiz`,{chapter:chap,total_questions:5});
        let d=j.quiz ?? j.data?.ai_response;
        if (typeof d === 'string') { try{d=JSON.parse(d);}catch(e){} }
        appendBubble('ai',renderAI(d));
        rememberAiTurn('Buatkan 5 soal dari '+(chap||'semua materi'), d);
        setStatus('Berhasil','success'); updateUsage(j.usage);
    }catch(e){appendBubble('ai',`<span style="color:#fca5a5;">${errorMessage(e)}</span>`);setStatus('Gagal','danger');}
    finally { btnLoading('btnQuiz',false,'<i class="bi bi-patch-question"></i> Buat Kuis (5 Soal)'); }
}

async function docPlan() {
    const chap=activeChapter();
    btnLoading('btnPlan',true,''); setStatus('Menyusun Rencana Dokumen...','warning');
    appendBubble('user','Buatkan rencana belajar dari '+(chap||'semua materi'));
    try {
        if (currentGuestDoc) {
            const deadline=new Date(Date.now()+7*86400000).toISOString().slice(0,10);
            const j=await apiPost('/api/ai/study-plan',{title:currentGuestDoc.title||'Materi sementara',subject:currentGuestDoc.subject||'Materi',content:getGuestText(chap),deadline});
            appendBubble('ai',renderPlan(j.data.study_plan));
            rememberAiTurn('Buatkan rencana belajar dari '+(chap||'semua materi'), j.data.study_plan, j.data.history_id);
            setStatus('Berhasil','success'); updateUsage(j.usage); return;
        }
        if(!currentDocId) return;
        const j=await apiPost(`/api/documents/${currentDocId}/study-plan`,{chapter:chap});
        let d=j.study_plan ?? j.data?.ai_response;
        if (typeof d === 'string') { try{d=JSON.parse(d);}catch(e){} }
        appendBubble('ai',renderAI(d));
        rememberAiTurn('Buatkan rencana belajar dari '+(chap||'semua materi'), d);
        setStatus('Berhasil','success'); updateUsage(j.usage);
    }catch(e){appendBubble('ai',`<span style="color:#fca5a5;">${errorMessage(e)}</span>`);setStatus('Gagal','danger');}
    finally { btnLoading('btnPlan',false,'<i class="bi bi-calendar-check"></i> Rencana Belajar'); }
}

function triggerSummarize() {
    if (currentGuestDoc || currentDocId) { docSummarize(); }
    else if (document.getElementById('content').value.trim().length >= 40) { summarizeManual(); }
    else { alert('Silakan isi materi minimal 40 karakter di panel kiri atau upload dokumen.'); }
}

function triggerQuiz() {
    if (currentGuestDoc || currentDocId) { docQuiz(); }
    else if (document.getElementById('content').value.trim().length >= 40) { quizManual(); }
    else { alert('Silakan isi materi minimal 40 karakter di panel kiri atau upload dokumen.'); }
}

function triggerPlan() {
    if (currentGuestDoc || currentDocId) { docPlan(); }
    else if (document.getElementById('content').value.trim().length >= 40) { planManual(); }
    else { alert('Silakan isi materi minimal 40 karakter di panel kiri atau upload dokumen.'); }
}

async function sendChat(e) {
    e.preventDefault();
    const msg=document.getElementById('chatInput').value.trim();
    if(!msg) return;
    document.getElementById('chatInput').value='';
    appendBubble('user',msg);
    appChatHistory.push({role: 'user', content: msg});

    const loadDiv=document.createElement('div');loadDiv.id='typing';loadDiv.className='animate-fade-up d-flex flex-column align-items-start mb-3';
    loadDiv.innerHTML='<div class="small mb-1" style="color:var(--text-muted);"><i class="bi bi-cpu me-1"></i>AI Groq</div><div class="chat-bubble chat-bubble-ai"><span class="spinner-border spinner-border-sm me-2"></span>Sedang berpikir...</div>';
    document.getElementById('chatArea').appendChild(loadDiv);
    document.getElementById('chatArea').scrollTo({top:document.getElementById('chatArea').scrollHeight,behavior:'smooth'});

    try {
        if(currentGuestDoc) {
            const payload = {content:getGuestText(activeChapter()),message:msg,history:appChatHistory.slice(0,-1)};
            if (activeHistoryId) payload.history_id = activeHistoryId;
            const j=await apiPost('/api/ai/chat',payload);
            document.getElementById('typing')?.remove();
            const answerPayload = j.data.quiz || j.data.ai_response;
            appendBubble('ai', j.data.quiz ? renderQuiz(j.data.quiz) : parseMarkdown(j.data.ai_response));
            activeHistoryId = j.data.history_id || activeHistoryId;
            appChatHistory.push({role: 'assistant', content: typeof answerPayload === 'string' ? answerPayload : JSON.stringify(answerPayload)});
            updateUsage(j.usage);
            return;
        }
        if(currentDocId) {
            const j=await apiPost(`/api/documents/${currentDocId}/chat`,{message:msg,chapter:activeChapter(),history:appChatHistory.slice(0,-1)});
            document.getElementById('typing')?.remove();
            let d=j.data.ai_response; try{d=JSON.parse(d);}catch(e){}
            appendBubble('ai',renderAI(d));
            appChatHistory.push({role: 'assistant', content: typeof d === 'string' ? d : JSON.stringify(d)});
            updateUsage(j.usage);
            return;
        }

        const manualContent=document.getElementById('content').value.trim();
        if(manualContent.length < 20) {
            document.getElementById('typing')?.remove();
            appendBubble('ai','<span style="color:var(--text-muted);">Ketik materi terlebih dahulu di kolom "Isi Materi" (minimal 20 karakter), atau upload dokumen, lalu kamu bisa chat tentang materinya di sini.</span>');
            appChatHistory.pop();
            return;
        }
        const payload = {content:manualContent,message:msg,history:appChatHistory.slice(0,-1)};
        if (activeHistoryId) payload.history_id = activeHistoryId;
        const j=await apiPost('/api/ai/chat',payload);
        document.getElementById('typing')?.remove();
        const answerPayload = j.data.quiz || j.data.ai_response;
        appendBubble('ai', j.data.quiz ? renderQuiz(j.data.quiz) : parseMarkdown(j.data.ai_response));
        activeHistoryId = j.data.history_id || activeHistoryId;
        appChatHistory.push({role: 'assistant', content: typeof answerPayload === 'string' ? answerPayload : JSON.stringify(answerPayload)});
        updateUsage(j.usage);
    }catch(e){
        document.getElementById('typing')?.remove();
        appendBubble('ai',`<span style="color:#fca5a5;">${errorMessage(e)}</span>`);
        appChatHistory.pop();
    }
}

// Init
Object.assign(window, {
    apiPost,
    appendBubble,
    parseMarkdown,
    renderAI,
    renderQuiz,
    renderPlan,
    setStatus,
    updateUsage,
    loadDocList,
    triggerSummarize,
    triggerQuiz,
    triggerPlan,
    sendChat,
    setActiveHistoryId,
});
loadDocList();
</script>
@endpush
