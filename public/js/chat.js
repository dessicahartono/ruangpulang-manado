const chat = document.querySelector('[data-chat]');

if (chat) {
    const log = chat.querySelector('[data-chat-messages]');
    const form = chat.querySelector('[data-chat-form]');
    const input = chat.querySelector('[data-chat-input]');
    const send = chat.querySelector('[data-chat-send]');
    const modeLabel = chat.querySelector('[data-chat-mode]');
    const clearButton = chat.querySelector('[data-chat-clear]');
    const maxHistory = Number(chat.dataset.maxHistory);
    const token = document.querySelector('meta[name="csrf-token"]').content;

    // Riwayat hanya disimpan di browser perangkat ini, tidak pernah di server website.
    const STORAGE_KEY = 'ruang-pulang-chat';
    const MAX_STORED_ENTRIES = 100;

    let messages = [];
    let entries = [];
    let busy = false;
    let requestId = 0;

    const addBubble = (text, className = '') => {
        const bubble = document.createElement('p');
        bubble.className = `bubble ${className}`.trim();
        bubble.textContent = text;
        log.appendChild(bubble);
        log.scrollTop = log.scrollHeight;
        return bubble;
    };

    const addCallButton = () => {
        const call = document.createElement('a');
        call.className = 'btn btn--accent chat__call';
        call.href = 'tel:119';
        call.textContent = 'Telepon 119, lalu ekstensi 8';
        log.appendChild(call);
        log.scrollTop = log.scrollHeight;
    };

    const showEntry = ({ text, kind }) => {
        addBubble(text, kind === 'user' ? 'bubble--user' : kind === 'crisis' ? 'bubble--crisis' : '');
        if (kind === 'crisis') {
            addCallButton();
        }
    };

    // Browser bisa menolak penyimpanan, misalnya di mode privat. Chat tetap jalan tanpa riwayat.
    const saveHistory = () => {
        clearButton.hidden = entries.length === 0;
        try {
            if (entries.length) {
                localStorage.setItem(STORAGE_KEY, JSON.stringify({ messages: messages.slice(-MAX_STORED_ENTRIES), entries }));
            } else {
                localStorage.removeItem(STORAGE_KEY);
            }
        } catch {
            // Abaikan.
        }
    };

    const loadHistory = () => {
        try {
            const saved = JSON.parse(localStorage.getItem(STORAGE_KEY));
            if (Array.isArray(saved?.messages) && Array.isArray(saved?.entries)) {
                messages = saved.messages.filter((m) => typeof m?.content === 'string' && ['user', 'assistant'].includes(m?.role));
                entries = saved.entries.filter((e) => typeof e?.text === 'string');
            }
        } catch {
            messages = [];
            entries = [];
        }
        entries.forEach(showEntry);
        clearButton.hidden = entries.length === 0;
    };

    const remember = (text, kind) => {
        entries = [...entries, { text, kind }].slice(-MAX_STORED_ENTRIES);
        saveHistory();
    };

    const setBusy = (value) => {
        busy = value;
        send.disabled = value;
    };

    const resizeInput = () => {
        input.style.height = 'auto';
        input.style.height = `${input.scrollHeight}px`;
    };

    // Riwayat dipotong supaya tetap dalam batas server dan selalu dimulai dari pesan pengguna.
    const trimmedHistory = () => {
        let history = messages.slice(-maxHistory);
        while (history.length && history[0].role !== 'user') {
            history = history.slice(1);
        }
        return history;
    };

    const failureText = (status, body) => {
        if (status === 429) {
            return 'Kamu sudah mengirim banyak pesan. Istirahat sebentar, lalu coba lagi nanti ya.';
        }
        if (status === 419) {
            return 'Halaman ini sudah terlalu lama terbuka. Muat ulang halaman, lalu coba lagi.';
        }
        if (status === 422 && body?.message) {
            return body.message;
        }
        return 'Maaf, pesanmu belum terkirim. Coba lagi sebentar lagi. Kalau kamu butuh bantuan sekarang, hubungi 119 ext. 8.';
    };

    const submit = async (text) => {
        const content = text.trim();
        if (!content || busy) {
            return;
        }

        const currentRequest = ++requestId;
        addBubble(content, 'bubble--user');
        messages.push({ role: 'user', content });
        input.value = '';
        resizeInput();
        setBusy(true);
        const typing = addBubble('Teman Pulang sedang mengetik…', 'bubble--typing');

        try {
            const response = await fetch(chat.dataset.url, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', Accept: 'application/json', 'X-CSRF-TOKEN': token },
                body: JSON.stringify({ messages: trimmedHistory() }),
            });
            const body = await response.json().catch(() => null);

            if (currentRequest !== requestId) {
                return;
            }

            typing.remove();

            if (!response.ok || !body?.reply) {
                messages.pop();
                input.value = content;
                resizeInput();
                addBubble(failureText(response.status, body), 'bubble--notice');
                return;
            }

            if (body.status === 'error' || body.status === 'busy') {
                // Pesan gagal diproses, jadi jangan masuk riwayat. Kembalikan ke kotak input.
                addBubble(body.reply);
                messages.pop();
                input.value = content;
                resizeInput();
            } else {
                const kind = body.status === 'crisis' ? 'crisis' : 'assistant';
                showEntry({ text: body.reply, kind });
                messages.push({ role: 'assistant', content: body.reply });
                remember(content, 'user');
                remember(body.reply, kind);
            }

            if (body.status === 'demo') {
                modeLabel.textContent = 'Mode demo, tanpa AI';
                modeLabel.hidden = false;
            }
        } catch {
            if (currentRequest !== requestId) {
                return;
            }
            typing.remove();
            messages.pop();
            input.value = content;
            resizeInput();
            addBubble(failureText(0), 'bubble--notice');
        } finally {
            if (currentRequest === requestId) {
                setBusy(false);
                input.focus();
            }
        }
    };

    clearButton.addEventListener('click', () => {
        requestId++;
        messages = [];
        entries = [];
        saveHistory();
        log.querySelectorAll('.bubble:not([data-chat-greeting]), .chat__call').forEach((element) => element.remove());
        input.value = '';
        resizeInput();
        setBusy(false);
        input.focus();
    });

    loadHistory();

    form.addEventListener('submit', (event) => {
        event.preventDefault();
        submit(input.value);
    });

    input.addEventListener('input', resizeInput);
    input.addEventListener('keydown', (event) => {
        if (event.key === 'Enter' && !event.shiftKey && !event.isComposing) {
            event.preventDefault();
            submit(input.value);
        }
    });
}
