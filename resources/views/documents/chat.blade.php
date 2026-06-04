@extends('layouts.app')

@section('content')
<div class="row g-4 animate-fade-up">
    <!-- Sidebar -->
    <div class="col-lg-3">

        
        <div class="card p-3">
            <div class="fw-bold mb-3 d-flex align-items-center gap-2" style="font-size:.9rem;">
                <i class="bi bi-lightning-charge" style="color:var(--warning);"></i> Quick Prompts
            </div>
            <div class="d-grid gap-1">
                <button class="btn btn-sm text-start" style="background:var(--surface-alt); font-size:.82rem; border-radius:10px;" onclick="setPrompt('Ringkas semua materi')"><i class="bi bi-stars me-1" style="color:var(--primary);"></i>Ringkas semua materi</button>
                <button class="btn btn-sm text-start" style="background:var(--surface-alt); font-size:.82rem; border-radius:10px;" onclick="setPrompt('Ringkas bab ini')"><i class="bi bi-stars me-1" style="color:var(--primary);"></i>Ringkas bab ini</button>
                <button class="btn btn-sm text-start" style="background:var(--surface-alt); font-size:.82rem; border-radius:10px;" onclick="setPrompt('Buatkan 5 soal pilihan ganda dari bab ini')"><i class="bi bi-patch-question me-1" style="color:var(--warning);"></i>Buat 5 soal dari bab ini</button>
                <button class="btn btn-sm text-start" style="background:var(--surface-alt); font-size:.82rem; border-radius:10px;" onclick="setPrompt('Jelaskan materi ini dengan bahasa sederhana')"><i class="bi bi-chat-dots me-1" style="color:var(--accent);"></i>Jelaskan sederhana</button>
                <button class="btn btn-sm text-start" style="background:var(--surface-alt); font-size:.82rem; border-radius:10px;" onclick="setPrompt('Buat poin-poin penting')"><i class="bi bi-list-check me-1" style="color:var(--success);"></i>Poin penting</button>
                <button class="btn btn-sm text-start" style="background:var(--surface-alt); font-size:.82rem; border-radius:10px;" onclick="setPrompt('Buat kesimpulan materi')"><i class="bi bi-journal-check me-1" style="color:#8b5cf6;"></i>Kesimpulan</button>
            </div>
            
            <hr style="border-color:var(--border);">
            <div class="fw-bold mb-2 d-flex align-items-center gap-2" style="font-size:.85rem;">
                <i class="bi bi-cpu" style="color:var(--accent);"></i> Aksi Otomatis AI
            </div>
            <div class="d-grid gap-2">
                <button class="btn btn-sm btn-primary" onclick="generateAuto('summarize')" id="btn-auto-sum">
                    <i class="bi bi-stars me-1"></i> Ringkasan Otomatis
                </button>
                <button class="btn btn-sm btn-success" onclick="generateAuto('quiz')" id="btn-auto-quiz">
                    <i class="bi bi-patch-question me-1"></i> Quiz Otomatis (5 Soal)
                </button>
                <button class="btn btn-sm btn-outline-primary" onclick="generateAuto('study-plan')" id="btn-auto-plan">
                    <i class="bi bi-calendar-check me-1"></i> Rencana Belajar
                </button>
            </div>
        </div>
    </div>
    
    <!-- Chat Area -->
    <div class="col-lg-9 animate-fade-up stagger-2">
        <div class="card d-flex flex-column" style="min-height:600px;">
            <div class="p-3 d-flex align-items-center justify-content-between" style="border-bottom:1px solid var(--border);">
                <div class="section-title" style="font-size:1.1rem;">
                    <span class="icon-box" style="background:rgba(6,182,212,.1); color:var(--accent); width:34px; height:34px; font-size:1rem;"><i class="bi bi-chat-dots"></i></span>
                    Chat Material
                </div>
                <span id="chatStatus" class="badge badge-soft">Pilih Dokumen</span>
            </div>
            
            <!-- Dokumen Aktif -->
            <div class="p-2 mx-3 mt-3 rounded" style="background:rgba(245,158,11,.08); border:1px solid rgba(245,158,11,.2);">
                <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                    <div class="fw-bold d-flex align-items-center gap-2" style="font-size:.85rem; color:var(--warning);">
                        <i class="bi bi-file-earmark-text"></i> Dokumen Aktif
                    </div>
                    <div class="d-flex gap-2 flex-grow-1" style="max-width:400px; margin-left:auto;">
                        <select id="documentSelect" class="form-select form-select-sm flex-grow-1" onchange="loadDocumentDetails()">
                            <option value="">Memuat dokumen...</option>
                        </select>
                        <select id="chapterSelect" class="form-select form-select-sm" style="width:130px;">
                            <option value="">Semua Materi</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="flex-grow-1 overflow-auto p-3" id="chatHistory" style="min-height: 400px; max-height: 500px; background:var(--surface-alt);">
                <div class="doc-chat-empty">
                    <div class="ai-orb-illustration compact" aria-hidden="true">
                        <div class="ai-orb-core"><span class="ai-mouth"></span></div>
                        <span class="ai-orbit orbit-one"></span>
                        <span class="ai-orbit orbit-two"></span>
                    </div>
                    <b>Mulai obrolan dengan dokumenmu</b>
                    <span>Pilih dokumen, lalu ketik pertanyaan di bawah.</span>
                </div>
            </div>
            
            <div class="p-3" style="border-top:1px solid var(--border);">
                <form id="chatForm" onsubmit="sendChat(event)">
                    <div class="input-group">
                        <input type="text" id="chatInput" class="form-control" placeholder="Tanya tentang isi buku..." required disabled style="border-radius:12px 0 0 12px;">
                        <button class="btn btn-primary px-4 fw-bold" type="submit" id="btnSend" disabled style="border-radius:0 12px 12px 0;">
                            <i class="bi bi-send me-1"></i> Kirim
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
let currentDocument = null;

async function loadDocuments() {
    try {
        const res = await fetch('/api/documents');
        const json = await res.json();
        const select = document.getElementById('documentSelect');
        const selectedFromUrl = new URLSearchParams(window.location.search).get('document');
        select.innerHTML = '<option value="">-- Pilih Buku --</option>';
        json.data.forEach(doc => {
            const id = doc._id || doc.id;
            select.innerHTML += `<option value="${id}" ${selectedFromUrl === id ? 'selected' : ''}>${doc.title}</option>`;
        });
        if (selectedFromUrl) loadDocumentDetails();
    } catch(e) { console.error(e); }
}

async function loadDocumentDetails() {
    const docId = document.getElementById('documentSelect').value;
    const chatInput = document.getElementById('chatInput');
    const btnSend = document.getElementById('btnSend');
    
    if (!docId) {
        chatInput.disabled = true;
        btnSend.disabled = true;
        document.getElementById('chatStatus').innerText = 'Pilih Dokumen';
        document.getElementById('chatStatus').className = 'badge badge-soft';
        return;
    }
    
    document.getElementById('chatStatus').innerText = 'Memuat...';
    document.getElementById('chatStatus').className = 'badge badge-soft-warning';
    
    try {
        const res = await fetch('/api/documents/' + docId);
        const json = await res.json();
        currentDocument = json.data;
        
        const chapSelect = document.getElementById('chapterSelect');
        chapSelect.innerHTML = '<option value="">Semua Materi</option>';
        if (currentDocument.chapters) {
            currentDocument.chapters.forEach(c => {
                chapSelect.innerHTML += `<option value="${c.chapter}">${c.chapter} ${c.title ? '— '+c.title : ''}</option>`;
            });
        }
        
        chatInput.disabled = false;
        btnSend.disabled = false;
        document.getElementById('chatStatus').innerText = 'Siap Chat ✓';
        document.getElementById('chatStatus').className = 'badge badge-soft-success';
        loadChatHistory(docId);
    } catch(e) {
        console.error(e);
        document.getElementById('chatStatus').innerText = 'Gagal';
        document.getElementById('chatStatus').className = 'badge badge-soft-danger';
    }
}

function parseMarkdown(text) {
    if (!text) return '';
    return text.replace(/\*\*(.*?)\*\*/g, '<b>$1</b>').replace(/\n/g, '<br>');
}

function renderAIResponse(data) {
    if (typeof data === 'string') {
        try { data = JSON.parse(data); } catch(e) { return parseMarkdown(data); }
    }
    if (data.summary) return parseMarkdown(data.summary);
    if (data.goal || data.steps || data.tips) {
        const steps = Array.isArray(data.steps) ? data.steps : [];
        const tips = Array.isArray(data.tips) ? data.tips : [];
        return `
            <div class="fw-bold mb-2" style="color:var(--primary);">${data.goal || 'Rencana belajar'}</div>
            ${steps.map((step, i) => `
                <div class="quiz-card mb-2">
                    <div class="fw-bold">${step.day || 'Tahap ' + (i + 1)}</div>
                    <div>${step.task || '-'}</div>
                    ${step.duration ? `<div class="small mt-1" style="color:var(--text-muted);">${step.duration}</div>` : ''}
                </div>
            `).join('')}
            ${tips.length ? `<div class="small mt-2"><b>Tips:</b><ul>${tips.map(t => `<li>${t}</li>`).join('')}</ul></div>` : ''}
        `;
    }
    if (data.questions) {
        return data.questions.map((q, i) => `
            <div class="quiz-card">
                <b style="color:var(--primary);">${i+1}. ${q.question || '-'}</b>
                ${Array.isArray(q.options) ? `<div class="ps-3 mt-2 mb-2">${q.options.map(opt => `<div class="mb-1">${opt}</div>`).join('')}</div>` : ''}
                <div class="fw-bold" style="color:var(--success);">Jawaban: ${q.answer || q.answer_key || '-'}</div>
                ${q.explanation ? `<div class="small mt-1" style="color:var(--text-muted);"><b>Pembahasan:</b> ${q.explanation}</div>` : ''}
            </div>
        `).join('');
    }
    return parseMarkdown(JSON.stringify(data, null, 2));
}

function appendChat(role, message, isHtml = false) {
    const history = document.getElementById('chatHistory');
    if(history.innerHTML.includes('Mulai obrolan')) history.innerHTML = '';
    
    const div = document.createElement('div');
    div.className = `animate-fade-up chat-row ${role === 'user' ? 'user' : 'ai'}`;
    const avatar = role === 'user' ? "{{ asset('assets/images/avatar-user.png') }}" : "{{ asset('assets/images/avatar-ai.png') }}";
    if (role === 'user') {
        div.innerHTML = `
            <img class="chat-avatar" src="${avatar}" alt="Avatar user" onerror="this.onerror=null;this.src='{{ asset('assets/icons/fallback-illustration.svg') }}';">
            <div class="text-end">
                <div class="small mb-1" style="color:var(--text-muted);">Kamu</div>
                <div class="chat-bubble chat-bubble-user">${message}</div>
            </div>
        `;
    } else {
        div.innerHTML = `
            <img class="chat-avatar" src="${avatar}" alt="Avatar AI" onerror="this.onerror=null;this.src='{{ asset('assets/icons/fallback-illustration.svg') }}';">
            <div class="text-start">
                <div class="small mb-1" style="color:var(--text-muted);"><i class="bi bi-cpu me-1"></i>AI Study Buddy</div>
                <div class="chat-bubble chat-bubble-ai">${isHtml ? message : parseMarkdown(message)}</div>
            </div>
        `;
    }
    history.appendChild(div);
    history.scrollTo({ top: history.scrollHeight, behavior: 'smooth' });
}

async function loadChatHistory(docId) {
    const history = document.getElementById('chatHistory');
    history.innerHTML = '<div class="text-center py-3" style="color:var(--text-muted);"><span class="spinner-border spinner-border-sm me-2"></span>Memuat riwayat chat...</div>';
    
    try {
        const res = await fetch(`/api/documents/${docId}/history`);
        const json = await res.json();
        history.innerHTML = '';
        if (json.data.length === 0) {
            history.innerHTML = `<div class="doc-chat-empty"><div class="ai-orb-illustration compact" aria-hidden="true"><div class="ai-orb-core"><span class="ai-mouth"></span></div><span class="ai-orbit orbit-one"></span><span class="ai-orbit orbit-two"></span></div><b>Mulai obrolan dengan dokumenmu</b><span>Pilih dokumen, lalu ketik pertanyaan di bawah.</span></div>`;
            return;
        }
        json.data.reverse().forEach(chat => {
            appendChat('user', chat.user_message);
            let parsed = chat.ai_response;
            try { parsed = JSON.parse(parsed); } catch(e) {}
            appendChat('ai', renderAIResponse(parsed), true);
        });
    } catch(e) {
        history.innerHTML = '<div class="alert" style="background:rgba(239,68,68,.1);color:var(--danger);border-radius:var(--radius);"><i class="bi bi-exclamation-triangle me-1"></i>Gagal memuat riwayat.</div>';
    }
}

function setPrompt(text) {
    document.getElementById('chatInput').value = text;
    document.getElementById('chatInput').focus();
}

async function sendChat(e) {
    e.preventDefault();
    const docId = document.getElementById('documentSelect').value;
    const msg = document.getElementById('chatInput').value;
    const chap = document.getElementById('chapterSelect').value;
    if (!docId || !msg.trim()) return;
    
    document.getElementById('chatInput').value = '';
    appendChat('user', msg);
    
    const loadingId = 'loading-' + Date.now();
    const history = document.getElementById('chatHistory');
    const loadDiv = document.createElement('div');
    loadDiv.id = loadingId;
    loadDiv.className = 'd-flex flex-column align-items-start mb-3 animate-fade-up';
    loadDiv.innerHTML = `
        <div class="small mb-1" style="color:var(--text-muted);"><i class="bi bi-cpu me-1"></i>AI Study Buddy</div>
        <div class="chat-bubble chat-bubble-ai" style="color:var(--text-muted);">
            <span class="spinner-border spinner-border-sm me-2" role="status"></span> Sedang berpikir...
        </div>
    `;
    history.appendChild(loadDiv);
    history.scrollTo({ top: history.scrollHeight, behavior: 'smooth' });
    
    try {
        const res = await fetch(`/api/documents/${docId}/chat`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
            body: JSON.stringify({ message: msg, chapter: chap })
        });
        const json = await res.json();
        document.getElementById(loadingId).remove();
        if (!res.ok) throw new Error(json.error || json.message || 'Error');
        appendChat('ai', renderAIResponse(json.data.ai_response), true);
    } catch(e) {
        document.getElementById(loadingId).remove();
        appendChat('ai', `<div style="color:var(--danger);"><i class="bi bi-exclamation-triangle me-1"></i>${e.message}</div>`, true);
    }
}

async function generateAuto(type) {
    const docId = document.getElementById('documentSelect').value;
    const chap = document.getElementById('chapterSelect').value;
    if (!docId) { alert("Pilih dokumen dulu!"); return; }
    
    const endpointMap = {
        'summarize': `/api/documents/${docId}/summarize`,
        'quiz': `/api/documents/${docId}/quiz`,
        'study-plan': `/api/documents/${docId}/study-plan`
    };
    const btnMap = {
        'summarize': document.getElementById('btn-auto-sum'),
        'quiz': document.getElementById('btn-auto-quiz'),
        'study-plan': document.getElementById('btn-auto-plan')
    };
    const labelMap = {
        'summarize': 'RINGKASAN',
        'quiz': 'QUIZ',
        'study-plan': 'RENCANA BELAJAR'
    };
    const endpoint = endpointMap[type];
    const btn = btnMap[type];
    const oldHtml = btn.innerHTML;
    
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Memproses...';
    appendChat('user', `(Permintaan Otomatis: ${labelMap[type]})`);
    
    try {
        const res = await fetch(endpoint, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
            body: JSON.stringify({ chapter: chap, total_questions: 5 })
        });
        const json = await res.json();
        btn.disabled = false;
        btn.innerHTML = oldHtml;
        if (!res.ok) throw new Error(json.error || json.message || 'Error');
        let parsed = json.data.ai_response;
        try { parsed = JSON.parse(parsed); } catch(e) {}
        appendChat('ai', renderAIResponse(parsed), true);
    } catch(e) {
        btn.disabled = false;
        btn.innerHTML = oldHtml;
        alert(e.message);
    }
}

window.onload = loadDocuments;
</script>
@endpush
