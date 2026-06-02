{{-- Floating AI Chat Widget --}}
<div id="aiChatWidget">
    {{-- Chat Button --}}
    <button id="aiChatToggle"
        onclick="AiChat.toggle()"
        class="fixed bottom-6 right-6 z-[200] w-14 h-14 rounded-full shadow-2xl
               bg-gradient-to-br from-blue-500 to-indigo-600 text-white
               flex items-center justify-center text-2xl
               hover:scale-105 active:scale-95 transition-transform duration-200
               hover:shadow-blue-500/30"
        title="AI Assistant">
        <i class="fas fa-robot" id="aiChatIcon"></i>
    </button>

    {{-- Chat Panel --}}
    <div id="aiChatPanel"
        class="fixed bottom-24 right-6 z-[200] w-[380px] max-w-[calc(100vw-2rem)]
               bg-white dark:bg-slate-800 rounded-2xl shadow-2xl
               border border-slate-200 dark:border-slate-700
               flex flex-col overflow-hidden hidden"
        style="height: 520px; max-height: calc(100vh - 160px);">

        {{-- Header --}}
        <div class="flex items-center gap-3 px-4 py-3 bg-gradient-to-r from-blue-600 to-indigo-600 text-white flex-shrink-0">
            <div class="w-9 h-9 rounded-full bg-white/20 flex items-center justify-center">
                <i class="fas fa-robot text-lg"></i>
            </div>
            <div class="flex-1 min-w-0">
                <div class="font-semibold text-sm">AI Assistant</div>
                <div class="text-xs text-blue-100">Tanya apapun tentang hotel</div>
            </div>
            <button onclick="AiChat.toggle()" class="w-8 h-8 rounded-lg hover:bg-white/10 flex items-center justify-center transition">
                <i class="fas fa-minus text-sm"></i>
            </button>
        </div>

        {{-- Messages --}}
        <div id="chatMessages" class="flex-1 overflow-y-auto p-4 space-y-3 bg-slate-50/50 dark:bg-slate-900/50"></div>

        {{-- Input --}}
        <div class="p-3 border-t border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 flex-shrink-0">
            <form id="chatForm" class="flex gap-2">
                <input type="text" id="chatInput"
                    class="flex-1 px-3.5 py-2.5 text-sm rounded-xl border border-slate-300 dark:border-slate-600
                           bg-slate-50 dark:bg-slate-700 text-slate-800 dark:text-slate-200
                           placeholder-slate-400 dark:placeholder-slate-500
                           focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none transition"
                    placeholder="Ketik pesan..." autocomplete="off">
                <button type="submit" id="chatSendBtn"
                    class="w-10 h-10 rounded-xl bg-blue-600 hover:bg-blue-700 disabled:bg-slate-300
                           dark:disabled:bg-slate-600 text-white flex items-center justify-center transition
                           disabled:cursor-not-allowed">
                    <i class="fas fa-paper-plane text-sm"></i>
                </button>
            </form>
        </div>
    </div>
</div>

<script>
window.AiChat = {
    open: false,
    messages: [],
    loading: false,

    init() {
        document.getElementById('chatForm').addEventListener('submit', (e) => {
            e.preventDefault();
            this.send();
        });
        document.getElementById('chatInput').addEventListener('keydown', (e) => {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                this.send();
            }
        });
        this.render();
    },

    /**
     * Show a booking notification popup automatically.
     * Called by BookingNotifications polling when new unread detected.
     * Banner is refreshed on every poll and persists until all notifications are read.
     */
    showNotification: function(notif) {
        // Play notification sound
        if (typeof playNotificationSound === 'function') {
            playNotificationSound();
        }

        // Don't auto-open the panel if user is actively chatting
        if (this.open && this.messages.length > 0) return;

        // Open the chat panel if closed
        if (!this.open) {
            this.toggle();
        }

        // Prepare notification details
        var guestName = notif.guest_name || 'Unknown';
        var roomInfo = notif.room_number ? 'Kamar ' + notif.room_number : '-';
        var sourceInfo = notif.ota_source || 'Web Booking';

        var container = document.getElementById('chatMessages');
        if (!container) return;

        // Remove old banner if exists
        var oldBanner = document.getElementById('notifBanner');
        if (oldBanner) oldBanner.remove();

        var bubble = document.createElement('div');
        bubble.id = 'notifBanner';
        bubble.innerHTML =
            '<div style="background:linear-gradient(135deg,#fef3c7,#fde68a);border:1px solid #f59e0b;border-radius:12px;margin:8px 12px;padding:14px 16px;box-shadow:0 2px 8px rgba(245,158,11,0.15)">' +
            '<div style="display:flex;align-items:flex-start;gap:10px">' +
            '<span style="font-size:20px;flex-shrink:0;line-height:1">🔔</span>' +
            '<div style="flex:1;min-width:0">' +
            '<div style="font-size:13px;font-weight:700;color:#92400e;margin-bottom:6px">📌 Notifikasi Booking Baru</div>' +
            '<table style="font-size:12px;line-height:1.6;color:#78350f;width:100%;border-collapse:collapse">' +
            '<tr><td style="padding:1px 6px 1px 0;white-space:nowrap;vertical-align:top;width:1%">👤</td><td style="padding:1px 0;font-weight:600">' + this.escapeHtml(guestName) + '</td></tr>' +
            '<tr><td style="padding:1px 6px 1px 0;white-space:nowrap;vertical-align:top">🛏️</td><td style="padding:1px 0">' + this.escapeHtml(roomInfo) + '</td></tr>' +
            '<tr><td style="padding:1px 6px 1px 0;white-space:nowrap;vertical-align:top">📎</td><td style="padding:1px 0">' + this.escapeHtml(sourceInfo) + '</td></tr>' +
            '</table></div></div></div>';

        // Add right after the welcome message
        if (container.children.length > 0) {
            container.insertBefore(bubble, container.children[1]);
        } else {
            container.appendChild(bubble);
        }
    },

    /**
     * Hide the booking notification banner.
     * Called by BookingNotifications polling when all notifications are read.
     */
    hideNotification: function() {
        var banner = document.getElementById('notifBanner');
        if (banner) banner.remove();
    },

    toggle() {
        this.open = !this.open;
        const panel = document.getElementById('aiChatPanel');
        const icon = document.getElementById('aiChatIcon');
        const btn = document.getElementById('aiChatToggle');

        if (this.open) {
            panel.classList.remove('hidden');
            panel.classList.add('flex');
            panel.style.opacity = '0';
            panel.style.transform = 'translateY(16px) scale(0.95)';
            requestAnimationFrame(() => {
                panel.style.transition = 'opacity 0.3s ease, transform 0.3s ease';
                panel.style.opacity = '1';
                panel.style.transform = 'translateY(0) scale(1)';
            });
            icon.className = 'fas fa-times';
            btn.classList.add('ring-2', 'ring-blue-300', 'shadow-lg', 'shadow-blue-500/30');
            this.scrollDown();
        } else {
            panel.style.transition = 'opacity 0.2s ease, transform 0.2s ease';
            panel.style.opacity = '0';
            panel.style.transform = 'translateY(16px) scale(0.95)';
            setTimeout(() => {
                panel.classList.add('hidden');
                panel.classList.remove('flex');
                panel.style.transition = '';
            }, 200);
            icon.className = 'fas fa-robot';
            btn.classList.remove('ring-2', 'ring-blue-300', 'shadow-lg', 'shadow-blue-500/30');
        }
    },

    async send() {
        const input = document.getElementById('chatInput');
        const msg = input.value.trim();
        if (!msg || this.loading) return;

        this.messages.push({ role: 'user', text: msg });
        input.value = '';
        this.loading = true;
        this.render();
        this.scrollDown();

        try {
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ?? '';
            const res = await fetch('{{ route("api.ai.chat") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                },
                body: JSON.stringify({
                    message: msg,
                    current_page: window.location.pathname,
                    history: this.messages.slice(-6).map(m => ({ role: m.role, text: m.text })),
                }),
            });
            const data = await res.json();
            if (data.success) {
                this.messages.push({ role: 'assistant', text: data.message });
            } else {
                this.messages.push({ role: 'assistant', text: data.message || 'Maaf, terjadi kesalahan.' });
            }
        } catch (e) {
            this.messages.push({ role: 'assistant', text: 'Maaf, koneksi terputus. Coba lagi.' });
        }

        this.loading = false;
        this.render();
        this.scrollDown();
    },

    sendQuick(text) {
        document.getElementById('chatInput').value = text;
        this.send();
    },

    render() {
        const container = document.getElementById('chatMessages');
        if (!container) return;
        container.innerHTML = '';

        if (this.messages.length === 0) {
            container.innerHTML = this.welcomeHTML();
            return;
        }

        let html = '';
        for (const msg of this.messages) {
            html += this.bubbleHTML(msg.role, msg.text);
        }
        if (this.loading) {
            html += this.loadingHTML();
        }
        container.innerHTML = html;
    },

    bubbleHTML(role, text) {
        const isUser = role === 'user';
        const content = isUser ? this.escapeHtml(text) : this.renderMarkdown(text);
        return '<div class="flex ' + (isUser ? 'justify-end' : 'justify-start') + '">' +
            '<div class="max-w-[85%] px-3.5 py-2.5 rounded-2xl text-sm leading-relaxed ' +
            (isUser
                ? 'bg-blue-600 text-white rounded-br-md'
                : 'bg-white dark:bg-slate-700 text-slate-700 dark:text-slate-200 rounded-bl-md shadow-sm border border-slate-100 dark:border-slate-600'
            ) + '">' +
            (isUser
                ? '<p class="whitespace-pre-wrap">' + content + '</p>'
                : '<div class="text-sm leading-relaxed ai-markdown">' + content + '</div>'
            ) + '</div></div>';
    },

    renderMarkdown(text) {
        if (!text) return '';
        let html = this.escapeHtml(text);
        // Code block ```...```
        html = html.replace(/```(\w*)\n?([\s\S]*?)```/g, '<pre class="bg-slate-100 dark:bg-slate-700 p-2 rounded-lg overflow-x-auto text-xs my-1.5 border border-slate-200 dark:border-slate-600"><code>$2</code></pre>');
        // Inline code `...`
        html = html.replace(/`([^`]+)`/g, '<code class="bg-slate-100 dark:bg-slate-600 px-1 py-0.5 rounded text-xs font-mono">$1</code>');
        // Bold **text**
        html = html.replace(/\*\*([^*]+)\*\*/g, '<strong class="font-semibold text-blue-600 dark:text-blue-400">$1</strong>');
        // Strikethrough ~~text~~
        html = html.replace(/~~([^~]+)~~/g, '<s class="line-through text-slate-400">$1</s>');
        // Emoji bullets: ✅ at start of line -> green
        html = html.replace(/^(✅|❌|⚠️|ℹ️|📋|🛏️|👤|💰|🏨|🔍)(.+)$/gm, '<div class="flex items-start gap-1.5 my-0.5"><span>$1</span><span>$2</span></div>');
        // Unordered list items
        html = html.replace(/^[-*] (.+)$/gm, '<li class="ml-4 list-disc">$1</li>');
        // Ordered list items
        html = html.replace(/^\d+\.\s(.+)$/gm, '<li class="ml-4 list-decimal">$1</li>');
        // Wrap consecutive <li> in <ul>
        html = html.replace(/((?:<li[^>]*>.*?<\/li>\n?)+)/g, '<ul class="my-1 pl-1 space-y-0.5">$1</ul>');
        // Headers
        html = html.replace(/^### (.+)$/gm, '<div class="text-sm font-bold mt-2 mb-0.5 text-slate-800 dark:text-slate-100">$1</div>');
        html = html.replace(/^## (.+)$/gm, '<div class="text-base font-bold mt-2.5 mb-0.5 text-slate-800 dark:text-slate-100">$1</div>');
        html = html.replace(/^# (.+)$/gm, '<div class="text-lg font-bold mt-3 mb-0.5 text-slate-800 dark:text-slate-100">$1</div>');
        // Horizontal rule
        html = html.replace(/^---+$/gm, '<hr class="my-2 border-t border-slate-200 dark:border-slate-600">');
        // Double newline = paragraph break
        html = html.replace(/\n\n/g, '</p><p class="mt-1.5">');
        // Single newline = <br>
        html = html.replace(/\n/g, '<br>');
        // Wrap in paragraph if plain text
        if (!html.startsWith('<')) {
            html = '<p class="leading-relaxed">' + html + '</p>';
        }
        return html;
    },

    loadingHTML() {
        return '<div class="flex justify-start">' +
            '<div class="bg-white dark:bg-slate-700 rounded-2xl rounded-bl-md px-4 py-3 shadow-sm border border-slate-100 dark:border-slate-600">' +
            '<div class="flex items-center gap-1.5">' +
            '<div class="w-2 h-2 bg-blue-500 rounded-full animate-bounce" style="animation-delay:0ms"></div>' +
            '<div class="w-2 h-2 bg-blue-500 rounded-full animate-bounce" style="animation-delay:150ms"></div>' +
            '<div class="w-2 h-2 bg-blue-500 rounded-full animate-bounce" style="animation-delay:300ms"></div>' +
            '</div></div></div>';
    },

    welcomeHTML() {
        return '<div class="text-center py-6 px-4">' +
            '<div class="w-14 h-14 rounded-full bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center mx-auto mb-3">' +
            '<i class="fas fa-robot text-2xl text-blue-500"></i></div>' +
            '<h3 class="font-semibold text-slate-700 dark:text-slate-300 mb-1">Halo! Ada yang bisa dibantu?</h3>' +
            '<p class="text-xs text-slate-500 dark:text-slate-400 mb-4">Tanya tentang booking, ketersediaan kamar,<br>data tamu, atau pendapatan hotel.</p>' +
            '<div class="space-y-2">' +
            '<button onclick="AiChat.sendQuick(\'Kamar apa saja yang tersedia hari ini?\')" class="w-full text-left text-xs px-3 py-2.5 rounded-lg border border-slate-200 dark:border-slate-600 hover:bg-blue-50 dark:hover:bg-blue-900/20 hover:border-blue-300 transition text-slate-600 dark:text-slate-400">' +
            '<span class="mr-1.5">🔍</span> Kamar apa saja yang tersedia hari ini?</button>' +
            '<button onclick="AiChat.sendQuick(\'Berapa pendapatan hotel hari ini?\')" class="w-full text-left text-xs px-3 py-2.5 rounded-lg border border-slate-200 dark:border-slate-600 hover:bg-blue-50 dark:hover:bg-blue-900/20 hover:border-blue-300 transition text-slate-600 dark:text-slate-400">' +
            '<span class="mr-1.5">💰</span> Berapa pendapatan hotel hari ini?</button>' +
            '<button onclick="AiChat.sendQuick(\'Berapa jumlah tamu yang check-in hari ini?\')" class="w-full text-left text-xs px-3 py-2.5 rounded-lg border border-slate-200 dark:border-slate-600 hover:bg-blue-50 dark:hover:bg-blue-900/20 hover:border-blue-300 transition text-slate-600 dark:text-slate-400">' +
            '<span class="mr-1.5">📋</span> Berapa tamu check-in hari ini?</button>' +
            '<button onclick="AiChat.sendQuick(\'Booking 2 malam untuk Budi, 2 orang\')" class="w-full text-left text-xs px-3 py-2.5 rounded-lg border border-slate-200 dark:border-slate-600 hover:bg-blue-50 dark:hover:bg-blue-900/20 hover:border-blue-300 transition text-slate-600 dark:text-slate-400">' +
            '<span class="mr-1.5">🏨</span> Booking 2 malam untuk Budi, 2 orang</button>' +
            '</div></div>';
    },

    escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    },

    scrollDown() {
        const el = document.getElementById('chatMessages');
        if (el) setTimeout(() => { el.scrollTop = el.scrollHeight; }, 50);
    },
};

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => AiChat.init());
} else {
    AiChat.init();
}
</script>
