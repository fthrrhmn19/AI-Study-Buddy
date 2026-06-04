(() => {
    const featurePaths = ['/ai/summarize', '/ai/quiz', '/ai/study-plan'];
    if (!featurePaths.includes(window.location.pathname)) return;

    const params = new URLSearchParams(window.location.search);
    const requestedHistoryId = params.get('history');
    if (!requestedHistoryId) return;

    let activeHistoryId = requestedHistoryId;
    let loadedHistory = null;
    let originalSendChat = null;

    function safeText(value) {
        return String(value ?? '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    function markdown(text) {
        if (typeof window.parseMarkdown === 'function') {
            return window.parseMarkdown(text);
        }

        return safeText(text).replace(/\*\*(.*?)\*\*/g, '<b>$1</b>').replace(/\n/g, '<br>');
    }

    function normalize(raw) {
        if (typeof raw !== 'string') return raw;

        try {
            return JSON.parse(raw);
        } catch (error) {
            return raw;
        }
    }

    function friendlyError(error, fallback = 'Gagal memproses permintaan.') {
        const raw = String(error?.error || error?.message || fallback);
        const normalized = raw.toLowerCase();

        if (normalized.includes('rate_limit') || normalized.includes('request too large') || normalized.includes('413') || normalized.includes('token') || normalized.includes('groq api error')) {
            return 'Materi terlalu panjang untuk limit Groq saat ini. Coba pilih bab tertentu, upload materi yang lebih fokus, atau ulangi beberapa saat lagi.';
        }

        return raw.length > 220 ? `${raw.slice(0, 220)}...` : raw;
    }

    function renderResult(raw) {
        const data = normalize(raw);

        if (data && data.quiz && typeof window.renderQuiz === 'function') return window.renderQuiz(data.quiz);
        if (data && data.questions && typeof window.renderQuiz === 'function') return window.renderQuiz(data);
        if (data && (data.goal || data.steps) && typeof window.renderPlan === 'function') return window.renderPlan(data);
        if (data && typeof window.renderAI === 'function') return window.renderAI(data);
        if (data && data.summary) return markdown(data.summary);
        if (data && data.ai_response) return markdown(data.ai_response);
        if (typeof data === 'string') return markdown(data);

        return `<pre class="history-json">${safeText(JSON.stringify(data, null, 2))}</pre>`;
    }

    function addBubble(role, html) {
        if (typeof window.appendBubble === 'function') {
            window.appendBubble(role, html);
            return;
        }

        const area = document.getElementById('chatArea');
        if (!area) return;
        area.insertAdjacentHTML('beforeend', `<div class="mb-3">${html}</div>`);
    }

    function setPageStatus(text, type = 'success') {
        if (typeof window.setStatus === 'function') {
            window.setStatus(text, type);
            return;
        }

        const status = document.getElementById('status');
        if (status) status.textContent = text;
    }

    function showTyping() {
        const area = document.getElementById('chatArea');
        if (!area) return;
        document.getElementById('resumeTyping')?.remove();
        area.insertAdjacentHTML('beforeend', `
            <div id="resumeTyping" class="animate-fade-up d-flex flex-column align-items-start mb-3">
                <div class="small mb-1" style="color:var(--text-muted);"><i class="bi bi-cpu me-1"></i>AI Groq</div>
                <div class="chat-bubble chat-bubble-ai"><span class="spinner-border spinner-border-sm me-2"></span>Melanjutkan sesi...</div>
            </div>
        `);
        area.scrollTo({ top: area.scrollHeight, behavior: 'smooth' });
    }

    async function continueSession(event, forcedMessage = null) {
        event?.preventDefault?.();

        const input = document.getElementById('chatInput');
        const message = String(forcedMessage ?? input?.value.trim() ?? '').trim();
        if (!message) return;

        if (!forcedMessage && input) input.value = '';
        addBubble('user', safeText(message));
        showTyping();

        try {
            const manualContent = document.getElementById('content')?.value.trim() || '';
            const payload = { message };
            if (manualContent.length >= 20) payload.content = manualContent;

            const response = await fetch(`/api/ai/history/${encodeURIComponent(activeHistoryId)}/continue`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                body: JSON.stringify(payload),
            });
            const json = await response.json();
            if (!response.ok) throw json;

            document.getElementById('resumeTyping')?.remove();
            if (json.data?.quiz) {
                addBubble('ai', renderResult(json.data.quiz));
            } else {
                addBubble('ai', renderResult(json.data?.ai_response || json.data));
            }

            activeHistoryId = json.data?.history_id || activeHistoryId;
            if (typeof window.setActiveHistoryId === 'function') window.setActiveHistoryId(activeHistoryId);
            loadedHistory = json.data || loadedHistory;
            if (typeof window.updateUsage === 'function') window.updateUsage(json.usage);
            setPageStatus('Sesi dilanjutkan', 'success');
        } catch (error) {
            document.getElementById('resumeTyping')?.remove();
            const messageText = friendlyError(error, 'Gagal melanjutkan sesi riwayat.');
            addBubble('ai', `<span style="color:#fca5a5;">${safeText(messageText)}</span>`);
            setPageStatus('Gagal', 'danger');
        }
    }

    function continueAction(message) {
        return function(event) {
            return continueSession(event, message);
        };
    }

    function installHistoryMode() {
        originalSendChat = window.sendChat;
        window.sendChat = continueSession;

        const path = window.location.pathname;
        if (path === '/ai/summarize') {
            window.triggerSummarize = continueAction('Ringkas ulang seluruh materi dari riwayat ini dengan lebih singkat, jelas, dan rapi. Tetap mencakup semua bagian utama materi, jangan hanya mengambil potongan jawaban sebelumnya.');
        }
        if (path === '/ai/quiz') {
            window.triggerQuiz = continueAction('Buatkan 5 soal dari materi riwayat ini. Jika campuran, pisahkan Pilihan Ganda dan Essay dengan nomor masing-masing.');
        }
        if (path === '/ai/study-plan') {
            window.triggerPlan = continueAction('Buatkan rencana belajar dari materi riwayat ini.');
        }
    }

    async function loadHistory() {
        try {
            const response = await fetch(`/api/ai/history/${encodeURIComponent(activeHistoryId)}`, {
                headers: { 'Accept': 'application/json' },
            });
            const json = await response.json();
            if (!response.ok) throw json;

            loadedHistory = json.data;
            const prompt = String(loadedHistory.prompt || 'Sesi sebelumnya');
            const shortPrompt = prompt.length > 700 ? `${prompt.slice(0, 700)}...` : prompt;

            addBubble('user', `<div class="small fw-bold mb-1" style="color:var(--primary);">Riwayat dimuat</div>${markdown(shortPrompt)}`);
            addBubble('ai', renderResult(loadedHistory.result));
            setPageStatus('Sesi riwayat dimuat', 'success');
            if (typeof window.setActiveHistoryId === 'function') window.setActiveHistoryId(activeHistoryId);

            installHistoryMode();
        } catch (error) {
            const messageText = friendlyError(error, 'Riwayat tidak bisa dimuat. Pastikan kamu sudah login.');
            addBubble('ai', `<span style="color:#fca5a5;">${safeText(messageText)}</span>`);
            setPageStatus('Riwayat gagal dimuat', 'danger');
            if (originalSendChat) window.sendChat = originalSendChat;
        }
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', loadHistory, { once: true });
    } else {
        loadHistory();
    }
})();
