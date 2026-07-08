/**
 * LuxNest AI Chat Widget — Vanilla JS (no jQuery)
 */
(function () {
    'use strict';

    const AGENTS = { Linh: 'Tư vấn viên', Ngọc: 'Chuyên viên đặt phòng', Mai: 'Tư vấn viên', Hương: 'Chuyên viên CSKH' };
    const AGENT_NAMES = Object.keys(AGENTS);

    let agentName   = sessionStorage.getItem('lx_agent') || AGENT_NAMES[Math.floor(Math.random() * AGENT_NAMES.length)];
    let chatHistory = JSON.parse(sessionStorage.getItem('lx_history') || '[]');
    let isChatOpen  = sessionStorage.getItem('lx_open') === 'true';
    let firstMsg    = chatHistory.length > 0;
    let shownCards  = new Set();
    let lastMsgTime = 0;
    sessionStorage.setItem('lx_agent', agentName);

    const widget   = document.getElementById('luxnest-ai-widget');
    if (!widget) return;

    const toggle   = document.getElementById('luxnest-ai-toggle');
    const win      = document.getElementById('luxnest-ai-window');
    const closeBtn = document.getElementById('luxnest-ai-close');
    const messages = document.getElementById('luxnest-ai-messages');
    const input    = document.getElementById('luxnest-ai-input');
    const sendBtn  = document.getElementById('luxnest-ai-send');
    const tooltip  = document.getElementById('luxnest-ai-tooltip');
    const agentEl  = document.getElementById('ai-agent-name');
    const titleEl  = document.getElementById('ai-agent-title');
    const avatarEl = document.querySelector('.ai-header-avatar');

    // ── Init ──────────────────────────────────────────────────────
    function init() {
        if (chatHistory.length > 0) {
            messages.querySelectorAll('.ai-message').forEach(el => el.remove());
            chatHistory.forEach(m => {
                if (m.role === 'user') appendUser(m.content, false);
                else appendBot(m.content, null, [], '', false);
            });
            setAgentUI();
            scrollBottom();
        }
        if (isChatOpen) openChat();
    }

    function setAgentUI() {
        if (agentEl) agentEl.textContent = agentName;
        if (titleEl) titleEl.textContent = AGENTS[agentName] || 'Tư vấn viên';
        if (avatarEl) avatarEl.textContent = agentName.charAt(0);
    }

    // ── Open / Close ──────────────────────────────────────────────
    function openChat() {
        win.classList.remove('hidden');
        toggle.classList.add('hidden');
        tooltip.classList.add('hidden');
        sessionStorage.setItem('lx_open', 'true');
        sessionStorage.setItem('lx_tooltip_shown', '1');
        setTimeout(() => input.focus(), 100);
    }

    function closeChat() {
        win.classList.add('hidden');
        toggle.classList.remove('hidden');
        sessionStorage.setItem('lx_open', 'false');
    }

    toggle.addEventListener('click', openChat);
    tooltip.addEventListener('click', openChat);
    closeBtn.addEventListener('click', closeChat);

    // Tooltip sau khi scroll 8s
    let tooltipFired = false;
    window.addEventListener('scroll', function onScroll() {
        if (tooltipFired || sessionStorage.getItem('lx_tooltip_shown')) return;
        tooltipFired = true;
        window.removeEventListener('scroll', onScroll);
        setTimeout(() => {
            if (win.classList.contains('hidden')) tooltip.classList.remove('hidden');
        }, 8000);
    }, { passive: true });

    // ── Send ──────────────────────────────────────────────────────
    sendBtn.addEventListener('click', sendMessage);
    input.addEventListener('keypress', e => { if (e.key === 'Enter') sendMessage(); });

    async function sendMessage() {
        const text = input.value.trim();
        if (!text) return;
        input.value = '';
        appendUser(text);

        const isFirst = !firstMsg;
        if (isFirst) {
            appendSystem('LuxNest <strong>đang kết nối</strong> với nhân viên tư vấn, vui lòng đợi<span class="jumping-dots"><span>.</span><span>.</span><span>.</span></span>', 'ai-connecting-msg');
            firstMsg = true;
        }

        lastMsgTime = Date.now();
        chatHistory.push({ role: 'user', content: text });
        saveSession();

        try {
            const res  = await fetch(window.CHAT_URL, {
                method:  'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': window.CSRF_TOKEN, 'Accept': 'application/json' },
                body:    JSON.stringify({ message: text, history: chatHistory.slice(0, -1), agent_name: agentName }),
            });
            const data = await res.json();

            if (data.success) {
                const replyLen = (data.reply || '').length;
                if (isFirst) {
                    setTimeout(() => {
                        document.getElementById('ai-connecting-msg')?.remove();
                        setAgentUI();
                        appendSystem(`<strong>Đã kết nối</strong> với tư vấn viên <strong>${esc(agentName)}</strong>. Anh/Chị cần hỗ trợ gì ạ?`);
                        showTyping();
                        setTimeout(() => { hideTyping(); finalizeReply(data, text); }, 3500);
                    }, 2500);
                } else {
                    const think  = (Date.now() - lastMsgTime) < 15000 ? 600 : 1800;
                    const typing = Math.min(5000, Math.max(1000, replyLen * 12));
                    setTimeout(() => {
                        showTyping();
                        setTimeout(() => { hideTyping(); finalizeReply(data, text); }, typing);
                    }, think);
                }
            } else {
                appendBot('Xin lỗi Anh/Chị, hệ thống đang bận xíu.', null, [], text);
            }
        } catch {
            appendBot('Dạ hệ thống đang gặp sự cố, Anh/Chị thử lại sau ạ.', null, [], text);
        }
    }

    function finalizeReply(d, originalText) {
        appendBot(d.reply, d.rooms_url || null, d.suggested_rooms || [], originalText);
        chatHistory.push({ role: 'assistant', content: d.reply });
        saveSession();
    }

    // ── Renderers ─────────────────────────────────────────────────
    function appendUser(text, scroll = true) {
        messages.insertAdjacentHTML('beforeend', `
        <div class="ai-message user-message">
            <div class="ai-avatar"><i class="ph ph-user"></i></div>
            <div class="ai-bubble">${esc(text)}</div>
        </div>`);
        if (scroll) scrollBottom();
    }

    function appendBot(text, roomsUrl, suggestedRooms, userText, scroll = true) {
        const fmt = esc(text)
            .replace(/\n/g, '<br>')
            .replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>')
            .replace(/\*(.*?)\*/g, '<em>$1</em>');
        const cta = roomsUrl
            ? `<br><br><a href="${roomsUrl}" style="color:#996d4e;font-weight:700;text-decoration:underline;">→ Đặt phòng ngay</a>`
            : '';
        messages.insertAdjacentHTML('beforeend', `
        <div class="ai-message bot-message">
            <div class="ai-avatar ai-avatar-agent">${esc(agentName.charAt(0))}</div>
            <div class="ai-bubble">${fmt}${cta}</div>
        </div>`);

        if (suggestedRooms?.length) {
            const reFetch = ['gửi lại','xem lại','xem ảnh','gửi ảnh','chi tiết'].some(kw => (userText||'').toLowerCase().includes(kw));
            const toShow  = suggestedRooms.filter(r => {
                const key = 'r' + r.id;
                if (reFetch || !shownCards.has(key)) { shownCards.add(key); return true; }
                return false;
            });
            if (toShow.length) appendRoomCards(toShow, roomsUrl);
        }
        if (scroll) scrollBottom();
    }

    function appendSystem(html, id = '') {
        messages.insertAdjacentHTML('beforeend', `
        <div class="ai-message system-message"${id ? ` id="${id}"` : ''}>
            <div class="ai-bubble">${html}</div>
        </div>`);
        scrollBottom();
    }

    function buildRoomCardHtml(r, roomsUrl) {
        const bookUrl = roomsUrl ? r.url + (r.url.includes('?') ? '&' : '?') + 'from_chat=1' : r.url;
        return `
        <div class="ai-room-card">
            ${r.image ? `<div class="ai-room-img" style="background-image:url('${r.image}')"></div>` : ''}
            <div class="ai-room-info">
                <div class="ai-room-name">${esc(r.name)}</div>
                <div class="ai-room-branch"><i class="ph ph-map-pin"></i> ${esc(r.branch)}</div>
                <div class="ai-room-price">${esc(r.price)} VNĐ<span>/đêm</span></div>
                <a href="${bookUrl}" class="ai-room-book">Đặt ngay <i class="ph ph-arrow-right"></i></a>
            </div>
        </div>`;
    }

    function appendRoomCards(rooms, roomsUrl) {
        const LIMIT  = 4;
        const first  = rooms.slice(0, LIMIT);
        const rest   = rooms.slice(LIMIT);

        const wrap = document.createElement('div');
        wrap.className = 'ai-room-cards';
        first.forEach(r => wrap.insertAdjacentHTML('beforeend', buildRoomCardHtml(r, roomsUrl)));

        if (rest.length > 0) {
            const btn = document.createElement('button');
            btn.className = 'ai-see-more-btn';
            btn.innerHTML = `<i class="ph ph-caret-down"></i> Xem thêm ${rest.length} phòng`;
            btn.addEventListener('click', function () {
                rest.forEach(r => wrap.insertAdjacentHTML('beforeend', buildRoomCardHtml(r, roomsUrl)));
                btn.remove();
                scrollBottom();
            });
            wrap.appendChild(btn);
        }

        messages.appendChild(wrap);
        scrollBottom();
    }

    function showTyping() {
        messages.insertAdjacentHTML('beforeend', `
        <div class="ai-message bot-message" id="ai-typing">
            <div class="ai-avatar ai-avatar-agent">${esc(agentName.charAt(0))}</div>
            <div class="ai-bubble ai-typing"><span></span><span></span><span></span></div>
        </div>`);
        scrollBottom();
    }

    function hideTyping() { document.getElementById('ai-typing')?.remove(); }
    function scrollBottom() { setTimeout(() => messages.scrollTo({ top: messages.scrollHeight, behavior: 'smooth' }), 80); }
    function saveSession() { sessionStorage.setItem('lx_history', JSON.stringify(chatHistory)); }
    function esc(s) {
        if (typeof s !== 'string') return '';
        return s.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;').replace(/'/g,'&#039;');
    }

    init();
})();
