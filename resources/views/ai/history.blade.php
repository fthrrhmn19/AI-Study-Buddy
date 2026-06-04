@extends('layouts.app')

@section('content')
@php
    $aiAvatarPath = public_path('assets/images/avatar-ai.png');
    $aiAvatarUrl = asset('assets/images/avatar-ai.png').(is_file($aiAvatarPath) ? '?v='.filemtime($aiAvatarPath) : '');
@endphp
<div class="feature-workspace feature-history animate-fade-up">
    <section class="feature-page-hero">
        <div>
            <span class="feature-kicker"><i class="bi bi-clock-history"></i> AI History</span>
            <h1>Riwayat hasil belajar AI</h1>
            <p>Lihat kembali ringkasan, kuis, rencana belajar, dan hasil dokumen yang pernah dibuat.</p>
        </div>
        <div class="feature-hero-card" aria-label="MongoDB History">
            <div class="feature-hero-visual">
                <span class="feature-hero-ring"></span>
                <img class="feature-hero-robot" src="{{ $aiAvatarUrl }}" alt="Avatar AI Study Buddy" onerror="this.onerror=null;this.src='{{ asset('assets/icons/fallback-illustration.svg') }}';">
                <span class="feature-hero-chip chip-top"><i class="bi bi-database"></i> MongoDB</span>
                <span class="feature-hero-chip chip-bottom"><i class="bi bi-arrow-repeat"></i> Lanjut sesi</span>
            </div>
            <div class="feature-hero-meta">
                <span>Database</span>
                <b>MongoDB History</b>
            </div>
        </div>
    </section>

    <div class="card p-4 mb-4 feature-panel history-toolbar">
        <div class="d-flex flex-wrap justify-content-between align-items-center mb-3">
            <div class="section-title">
                <span class="icon-box" style="background:rgba(244,63,94,.1); color:#f43f5e;"><i class="bi bi-clock-history"></i></span>
                Riwayat AI
            </div>
            <div class="d-flex flex-wrap gap-2 mt-2 mt-md-0">
                <select id="filterType" class="form-select form-select-sm history-filter" onchange="loadHistory()">
                    <option value="">Semua Tipe</option>
                    <option value="summarize">Ringkasan</option>
                    <option value="quiz">Kuis</option>
                    <option value="study_plan">Rencana Belajar</option>
                    <option value="chat">Chat Material</option>
                </select>
                <button class="btn btn-sm btn-outline-primary" onclick="loadHistory()">
                    <i class="bi bi-arrow-clockwise me-1"></i> Refresh
                </button>
                <button class="btn btn-sm btn-outline-danger" onclick="showDeleteAllConfirm()" id="btnDeleteAll">
                    <i class="bi bi-trash me-1"></i> Hapus Semua
                </button>
            </div>
        </div>
        <p style="color:var(--text-muted); font-size:.9rem;">Semua hasil generasi AI tersimpan di MongoDB dan bisa diakses kembali kapan saja.</p>

        <div id="deleteAllConfirm" class="d-none animate-fade-up history-delete-banner">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-exclamation-triangle-fill" style="color:var(--danger); font-size:1.1rem;"></i>
                    <span style="font-size:.9rem; font-weight:600; color:var(--danger);">Yakin hapus semua riwayat? Tindakan ini tidak bisa dibatalkan.</span>
                </div>
                <div class="d-flex gap-2">
                    <button class="btn btn-sm btn-danger px-3" onclick="confirmDeleteAll()" id="btnConfirmDeleteAll" style="border-radius:10px;">
                        <i class="bi bi-trash me-1"></i> Ya, Hapus Semua
                    </button>
                    <button class="btn btn-sm btn-outline-secondary px-3" onclick="hideDeleteAllConfirm()" style="border-radius:10px;">
                        Batal
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div id="history-list">
        <div class="history-loading">
            <span class="spinner-border spinner-border-sm me-2"></span> Memuat riwayat...
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function escapeHtml(value) {
    return String(value ?? '')
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
}

function parseMarkdown(text) {
    if (!text) return '';
    return escapeHtml(text)
        .replace(/\*\*(.*?)\*\*/g, '<b>$1</b>')
        .replace(/\n/g, '<br>');
}

function normalizeType(type) {
    return type === 'summary' ? 'summarize' : type;
}

function normalizeResult(raw) {
    if (typeof raw !== 'string') return raw;

    try {
        return JSON.parse(raw);
    } catch (error) {
        return raw;
    }
}

function renderStudyPlan(data) {
    let html = '';
    if (data.goal) {
        html += `<div class="mb-2"><b>Tujuan:</b> ${escapeHtml(data.goal)}</div>`;
    }

    if (Array.isArray(data.steps) && data.steps.length) {
        html += '<div class="history-steps">';
        data.steps.forEach(step => {
            html += `
                <div class="history-step">
                    <b>${escapeHtml(step.day || 'Tahap')}</b>
                    <span>${escapeHtml(step.task || '-')}</span>
                    ${step.duration ? `<em>${escapeHtml(step.duration)}</em>` : ''}
                </div>
            `;
        });
        html += '</div>';
    }

    if (Array.isArray(data.tips) && data.tips.length) {
        html += '<div class="mt-2"><b>Tips:</b><ul>';
        data.tips.forEach(tip => html += `<li>${escapeHtml(tip)}</li>`);
        html += '</ul></div>';
    }

    return html || '<span style="color:var(--text-muted);">Rencana belajar kosong.</span>';
}

function renderQuiz(data) {
    if (!Array.isArray(data.questions)) {
        return parseMarkdown(JSON.stringify(data, null, 2));
    }

    if (!data.questions.length) {
        if (data.raw) {
            return `
                <div class="history-warning mb-2">AI mengembalikan format kuis mentah. Isi tetap ditampilkan agar tidak hilang.</div>
                ${parseMarkdown(data.raw)}
            `;
        }
        return '<span style="color:var(--text-muted);">Belum ada soal di hasil ini.</span>';
    }

    const multipleChoice = data.questions.filter(question => {
        const options = Array.isArray(question.options) ? question.options : [];
        return question.type !== 'essay' && options.length;
    });
    const essays = data.questions.filter(question => {
        const options = Array.isArray(question.options) ? question.options : [];
        return question.type === 'essay' || !options.length;
    });

    const renderQuestion = (question, number, isEssay) => {
        const options = Array.isArray(question.options) ? question.options : [];
        const answer = question.answer_key || question.answer || '-';

        return `
            <div class="quiz-card history-quiz-item">
                <div class="d-flex align-items-start gap-2 mb-2">
                    <span class="badge ${isEssay ? 'badge-soft-cyan' : 'badge-soft-warning'}">${isEssay ? 'Essay' : 'PG'}</span>
                    <b style="color:var(--primary);">${number}. ${escapeHtml(question.question || '-')}</b>
                </div>
                ${options.length ? `<div class="ps-2 mt-2 mb-2">${options.map(option => `<div class="mb-1">${escapeHtml(option)}</div>`).join('')}</div>` : ''}
                <div class="text-success fw-bold">Jawaban: ${escapeHtml(answer)}</div>
                ${question.explanation ? `<div class="small mt-1" style="color:var(--text-muted);">${parseMarkdown(question.explanation)}</div>` : ''}
            </div>
        `;
    };

    let html = '';
    if (multipleChoice.length) {
        html += `<div class="quiz-section-title"><i class="bi bi-list-check"></i> Pilihan Ganda 1-${multipleChoice.length}</div>`;
        html += multipleChoice.map((question, index) => renderQuestion(question, index + 1, false)).join('');
    }
    if (essays.length) {
        html += `<div class="quiz-section-title essay"><i class="bi bi-pencil-square"></i> Essay 1-${essays.length}</div>`;
        html += essays.map((question, index) => renderQuestion(question, index + 1, true)).join('');
    }

    return html;
}

function renderResult(rawData) {
    const data = normalizeResult(rawData);

    if (typeof data === 'string') return parseMarkdown(data);
    if (data && data.summary) return parseMarkdown(data.summary);
    if (data && data.ai_response) return parseMarkdown(data.ai_response);
    if (data && (data.goal || data.steps || data.tips)) return renderStudyPlan(data);
    if (data && data.questions) return renderQuiz(data);
    if (data && data.raw) return parseMarkdown(data.raw);

    return `<pre class="history-json">${escapeHtml(JSON.stringify(data, null, 2))}</pre>`;
}

function getTypeBadge(type) {
    const normalized = normalizeType(type);
    const map = {
        summarize: { label: 'Ringkasan', class: 'badge-soft', icon: 'bi-stars' },
        quiz: { label: 'Kuis', class: 'badge-soft-warning', icon: 'bi-patch-question' },
        study_plan: { label: 'Rencana Belajar', class: 'badge-soft-cyan', icon: 'bi-calendar-check' },
        chat: { label: 'Chat Material', class: 'badge-soft-success', icon: 'bi-chat-left-text' },
    };
    const item = map[normalized] || { label: normalized || 'AI', class: 'badge-soft', icon: 'bi-cpu' };
    return `<span class="badge ${item.class}"><i class="bi ${item.icon} me-1"></i>${escapeHtml(item.label)}</span>`;
}

function getResumeUrl(item) {
    const id = encodeURIComponent(item._id || item.id || '');
    const type = normalizeType(item.type);
    const routeMap = {
        summarize: '/ai/summarize',
        quiz: '/ai/quiz',
        study_plan: '/ai/study-plan',
        chat: '/ai/summarize',
    };

    return `${routeMap[type] || '/ai/summarize'}?history=${id}`;
}

function showDeleteConfirm(id, btn) {
    document.querySelectorAll('.delete-confirm-inline').forEach(el => el.remove());

    const card = btn.closest('.card');
    const confirmDiv = document.createElement('div');
    confirmDiv.className = 'delete-confirm-inline animate-fade-up mt-3 p-3 rounded-3';
    confirmDiv.style.cssText = 'background:rgba(239,68,68,.06); border:1px solid rgba(239,68,68,.2);';
    confirmDiv.innerHTML = `
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
            <span style="font-size:.85rem; color:var(--danger); font-weight:600;">
                <i class="bi bi-exclamation-triangle me-1"></i>Hapus riwayat ini?
            </span>
            <div class="d-flex gap-2">
                <button class="btn btn-sm btn-danger px-3" style="border-radius:10px; font-size:.8rem;" onclick="doDelete('${escapeHtml(id)}', this)">
                    <i class="bi bi-trash me-1"></i>Ya, Hapus
                </button>
                <button class="btn btn-sm btn-outline-secondary px-3" style="border-radius:10px; font-size:.8rem;" onclick="this.closest('.delete-confirm-inline').remove()">
                    Batal
                </button>
            </div>
        </div>
    `;
    card.appendChild(confirmDiv);
}

async function doDelete(id, btn) {
    const card = btn.closest('.card');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';

    try {
        const response = await fetch('/api/ai/history/' + encodeURIComponent(id), {
            method: 'DELETE',
            headers: { 'Accept': 'application/json' }
        });
        if (!response.ok) throw new Error('Gagal menghapus');
        card.style.animation = 'fadeOut .3s ease forwards';
        setTimeout(() => card.remove(), 300);
    } catch (error) {
        btn.disabled = false;
        btn.innerHTML = '<i class="bi bi-trash me-1"></i>Ya, Hapus';
        alert('Gagal menghapus: ' + error.message);
    }
}

function showDeleteAllConfirm() {
    document.getElementById('deleteAllConfirm').classList.remove('d-none');
}

function hideDeleteAllConfirm() {
    document.getElementById('deleteAllConfirm').classList.add('d-none');
}

async function confirmDeleteAll() {
    const btn = document.getElementById('btnConfirmDeleteAll');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Menghapus...';

    try {
        const response = await fetch('/api/ai/history/all', {
            method: 'DELETE',
            headers: { 'Accept': 'application/json' }
        });
        if (!response.ok) throw new Error('Gagal');
        hideDeleteAllConfirm();
        document.getElementById('history-list').innerHTML = `
            <div class="history-empty">
                <img class="empty-state-visual small" src="{{ asset('assets/images/empty-state.png') }}" alt="Riwayat kosong" onerror="this.onerror=null;this.src='{{ asset('assets/icons/fallback-illustration.svg') }}';">
                Semua riwayat telah dihapus.
            </div>`;
    } catch (error) {
        alert('Gagal menghapus: ' + error.message);
    } finally {
        btn.disabled = false;
        btn.innerHTML = '<i class="bi bi-trash me-1"></i> Ya, Hapus Semua';
    }
}

async function loadHistory() {
    const filter = document.getElementById('filterType').value;
    const wrapper = document.getElementById('history-list');
    wrapper.innerHTML = '<div class="history-loading"><span class="spinner-border spinner-border-sm me-2"></span> Memuat riwayat...</div>';

    try {
        const response = await fetch('/api/ai/history?limit=100', {
            headers: { 'Accept': 'application/json' }
        });
        const json = await response.json();
        if (!response.ok) throw new Error(json.message || 'Gagal memuat riwayat');

        let items = Array.isArray(json.data) ? json.data : [];
        if (filter) {
            items = items.filter(item => normalizeType(item.type) === filter);
        }

        wrapper.innerHTML = '';
        if (!items.length) {
            wrapper.innerHTML = `
                <div class="history-empty">
                    <img class="empty-state-visual" src="{{ asset('assets/images/empty-state.png') }}" alt="Riwayat kosong" onerror="this.onerror=null;this.src='{{ asset('assets/icons/fallback-illustration.svg') }}';">
                    ${filter ? 'Belum ada riwayat untuk tipe ini.' : 'Belum ada riwayat AI.'}
                </div>`;
            return;
        }

        items.forEach((item, index) => {
            const itemId = item._id || item.id;
            const prompt = item.prompt ? String(item.prompt) : '-';
            const createdAt = item.created_at ? new Date(item.created_at).toLocaleString('id-ID') : '-';
            const card = document.createElement('div');
            card.className = 'card p-4 mb-3 animate-fade-up feature-panel history-card';
            card.style.animationDelay = (index * 0.03) + 's';
            card.innerHTML = `
                <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 pb-2" style="border-bottom:1px solid var(--border);">
                    <div class="d-flex align-items-center gap-2 flex-wrap">
                        ${getTypeBadge(item.type)}
                        <span class="badge badge-soft-success" style="font-size:.7rem;">${escapeHtml(item.source || 'manual')}</span>
                    </div>
                    <div class="d-flex align-items-center gap-2 mt-1 mt-md-0 flex-wrap">
                        <span class="badge badge-soft-cyan" style="font-size:.7rem;"><i class="bi bi-lightning-charge me-1"></i>${escapeHtml(item.provider || 'Groq')} / ${escapeHtml(item.model || 'llama')}</span>
                        <span style="color:var(--text-muted); font-size:.8rem;"><i class="bi bi-clock me-1"></i>${escapeHtml(createdAt)}</span>
                        <a class="btn btn-sm btn-outline-primary" style="padding:4px 12px; font-size:.78rem; border-radius:8px;" href="${getResumeUrl(item)}" title="Buka dan lanjutkan sesi ini">
                            <i class="bi bi-arrow-repeat"></i> Lanjutkan
                        </a>
                        <button class="btn btn-sm btn-outline-danger delete-btn" style="padding:4px 12px; font-size:.78rem; border-radius:8px;" data-id="${escapeHtml(itemId)}" title="Hapus riwayat ini">
                            <i class="bi bi-trash" style="pointer-events:none;"></i> <span style="pointer-events:none;">Hapus</span>
                        </button>
                    </div>
                </div>
                <div class="mb-3">
                    <div class="small fw-bold mb-1" style="color:var(--primary);"><i class="bi bi-chat-left-text me-1"></i>Prompt / Permintaan:</div>
                    <div class="history-prompt">${escapeHtml(prompt.substring(0, 220) + (prompt.length > 220 ? '...' : ''))}</div>
                </div>
                <div>
                    <div class="small fw-bold mb-2" style="color:var(--success);"><i class="bi bi-cpu me-1"></i>Hasil AI:</div>
                    <div class="history-result">${renderResult(item.result)}</div>
                </div>
            `;
            wrapper.appendChild(card);
        });
    } catch (error) {
        wrapper.innerHTML = `
            <div class="alert d-flex align-items-center gap-2" style="background:rgba(239,68,68,.1); color:var(--danger); border:1px solid rgba(239,68,68,.2); border-radius:var(--radius);">
                <i class="bi bi-exclamation-triangle"></i> Gagal memuat riwayat: ${escapeHtml(error.message)}
            </div>`;
    }
}

document.getElementById('history-list').addEventListener('click', function(event) {
    const btn = event.target.closest('.delete-btn');
    if (!btn) return;

    const id = btn.getAttribute('data-id');
    if (id) showDeleteConfirm(id, btn);
});

loadHistory();
</script>
<style>
@keyframes fadeOut {
    from { opacity:1; transform:translateY(0); }
    to { opacity:0; transform:translateY(-10px); }
}
</style>
@endpush
