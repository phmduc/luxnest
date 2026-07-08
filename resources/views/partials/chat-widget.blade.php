<div id="luxnest-ai-widget">
    <div id="luxnest-ai-toggle" class="ai-closed">
        <i class="ph ph-chats-teardrop"></i>
        <span class="ai-toggle-label">Chat ngay</span>
    </div>

    <div id="luxnest-ai-tooltip" class="hidden">
        <div class="ai-tooltip-content">Bạn cần tư vấn đặt phòng? Liên hệ chúng tôi ngay!</div>
        <div class="ai-tooltip-arrow"></div>
    </div>

    <div id="luxnest-ai-window" class="hidden">
        <div class="ai-header">
            <div class="ai-header-agent">
                <div class="ai-header-avatar"><i class="ph ph-buildings"></i></div>
                <div>
                    <h4 id="ai-agent-name">CSKH LuxNest</h4>
                    <span id="ai-agent-title">Hệ thống hỗ trợ trực tuyến</span>
                    <span class="ai-online-dot"></span>
                </div>
            </div>
            <i class="ph ph-x" id="luxnest-ai-close"></i>
        </div>

        <div class="ai-messages" id="luxnest-ai-messages">
            <div class="ai-message bot-message">
                <div class="ai-avatar ai-avatar-agent"><i class="ph ph-buildings"></i></div>
                <div class="ai-bubble" style="max-width:280px;">Chào mừng Anh/Chị đến với LuxNest. Vui lòng để lại lời nhắn, chuyên viên tư vấn sẽ hỗ trợ ngay ạ!</div>
            </div>
        </div>

        <div class="ai-input-area">
            <input type="text" id="luxnest-ai-input" placeholder="Nhắn tin cho tư vấn viên...">
            <button id="luxnest-ai-send"><i class="ph ph-paper-plane-right"></i></button>
        </div>
    </div>
</div>

<style>
#luxnest-ai-widget{position:fixed;bottom:30px;right:30px;z-index:99999;font-family:'Inter',sans-serif;-webkit-font-smoothing:antialiased}
#luxnest-ai-toggle{display:flex;align-items:center;gap:8px;padding:0 20px 0 14px;height:56px;background:linear-gradient(135deg,#996d4e,#c9a17e);color:#fff;border-radius:28px;cursor:pointer;box-shadow:0 8px 25px rgba(153, 109, 78,.4);transition:all .4s cubic-bezier(.175,.885,.32,1.275);border:2px solid rgba(255,255,255,.2);font-size:26px}
#luxnest-ai-toggle.hidden{display:none}
.ai-toggle-label{font-size:.92rem;font-weight:600;white-space:nowrap}
#luxnest-ai-toggle:hover{transform:scale(1.08) rotate(4deg);box-shadow:0 12px 30px rgba(153, 109, 78,.5)}
#luxnest-ai-tooltip{position:absolute;bottom:75px;right:0;width:240px;z-index:1000;filter:drop-shadow(0 10px 25px rgba(153, 109, 78,.25));transition:all .5s cubic-bezier(.175,.885,.32,1.275);cursor:pointer}
#luxnest-ai-tooltip.hidden{opacity:0;transform:translateY(15px) scale(.9);pointer-events:none}
.ai-tooltip-content{background:linear-gradient(135deg,#996d4e,#c9a17e);color:#fff;padding:14px 18px;border-radius:18px;font-size:.88rem;font-weight:500;line-height:1.5;text-align:center;animation:tooltip-pulse 3s infinite ease-in-out}
.ai-tooltip-arrow{position:absolute;bottom:-8px;right:32px;width:16px;height:16px;background:#c9a17e;transform:rotate(45deg);z-index:-1}
@keyframes tooltip-pulse{0%,100%{transform:scale(1)}50%{transform:scale(1.03)}}
#luxnest-ai-window{width:380px;height:min(560px,calc(100dvh - 130px));background:#fff;border-radius:24px;box-shadow:0 20px 60px rgba(0,0,0,.15);display:flex;flex-direction:column;overflow:hidden;transform-origin:bottom right;transition:all .5s cubic-bezier(.19,1,.22,1);border:1px solid rgba(0,0,0,.05);bottom:50px;right:0;position:absolute}
#luxnest-ai-window.hidden{opacity:0;transform:scale(.7) translateY(40px);pointer-events:none}
.ai-header{background:linear-gradient(135deg,#996d4e,#7a573e);color:#fff;padding:16px 20px;display:flex;justify-content:space-between;align-items:center}
.ai-header-agent{display:flex;align-items:center;gap:12px}
.ai-header-avatar{width:40px;height:40px;background:rgba(255,255,255,.25);border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:1.1rem;font-weight:700;color:#fff;flex-shrink:0;border:2px solid rgba(255,255,255,.4)}
.ai-online-dot{display:inline-block;width:8px;height:8px;background:#4ADE80;border-radius:50%;margin-left:6px;vertical-align:middle;animation:pulse-dot 2s infinite}
@keyframes pulse-dot{0%,100%{opacity:1}50%{opacity:.4}}
.ai-header h4{margin:0;font-size:1rem;font-weight:700;color:#fff}
.ai-header span{font-size:.78rem;opacity:.9;font-weight:400}
#luxnest-ai-close{font-size:1.4rem;cursor:pointer;padding:8px;transition:transform .3s;opacity:.8}
#luxnest-ai-close:hover{transform:rotate(90deg);opacity:1}
.ai-messages{flex:1;padding:20px;overflow-y:auto;background:#fdfdfd;display:flex;flex-direction:column;gap:14px;scroll-behavior:smooth}
.ai-messages::-webkit-scrollbar{width:4px}
.ai-messages::-webkit-scrollbar-thumb{background:#eee;border-radius:10px}
.ai-message{display:flex;gap:10px;align-items:flex-end;max-width:90%;animation:bubbleIn .35s ease forwards}
@keyframes bubbleIn{from{opacity:0;transform:translateY(8px) scale(.96)}to{opacity:1;transform:translateY(0) scale(1)}}
.bot-message{align-self:flex-start}
.user-message{align-self:flex-end;flex-direction:row-reverse}
.system-message{align-self:center;max-width:100%;margin:4px 0}
.system-message .ai-bubble{background:transparent;border:none;box-shadow:none;color:#888;font-size:.85rem;font-style:italic;text-align:center;padding:0}
.system-message .ai-bubble strong{color:#996d4e}
.ai-avatar{width:32px;height:32px;background:#996d4e;color:#fff;border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:18px;flex-shrink:0;box-shadow:0 4px 10px rgba(153, 109, 78,.2)}
.ai-avatar-agent{background:linear-gradient(135deg,#996d4e,#c9a17e);font-size:.95rem;font-weight:700;border-radius:50%}
.user-message .ai-avatar{background:#1a1a1a;border-radius:10px}
.ai-bubble{background:#fff;padding:12px 16px;border-radius:16px 16px 16px 4px;font-size:.92rem;line-height:1.6;color:#2d3436;box-shadow:0 2px 10px rgba(0,0,0,.04);border:1px solid rgba(0,0,0,.04);word-break:break-word;white-space:pre-wrap}
.user-message .ai-bubble{background:#996d4e;color:#fff;border-radius:16px 16px 4px 16px;border:none;box-shadow:0 4px 12px rgba(153, 109, 78,.2)}
.ai-typing{display:flex;gap:5px;padding:8px}
.ai-typing span{width:6px;height:6px;background:#996d4e;border-radius:50%;animation:typing 1.4s infinite ease-in-out both;display:inline-block}
.ai-typing span:nth-child(1){animation-delay:-.32s}
.ai-typing span:nth-child(2){animation-delay:-.16s}
@keyframes typing{0%,80%,100%{transform:scale(0);opacity:.3}40%{transform:scale(1);opacity:1}}
.jumping-dots{display:inline-flex;margin-left:2px}
.jumping-dots span{animation:jump .6s infinite;display:inline-block}
.jumping-dots span:nth-child(2){animation-delay:.2s}
.jumping-dots span:nth-child(3){animation-delay:.4s}
@keyframes jump{0%,100%{transform:translateY(0)}50%{transform:translateY(-3px)}}
.ai-room-cards{display:flex;flex-direction:column;gap:10px;padding:0 0 4px 42px;animation:bubbleIn .35s ease forwards}
.ai-room-card{display:flex;background:#fff;border-radius:12px;overflow:hidden;box-shadow:0 4px 16px rgba(0,0,0,.08);border:1px solid rgba(0,0,0,.06);max-width:280px;transition:transform .2s}
.ai-room-card:hover{transform:translateY(-2px)}
.ai-room-img{width:90px;min-height:90px;background-size:cover;background-position:center;flex-shrink:0}
.ai-room-info{padding:10px 12px;display:flex;flex-direction:column;gap:3px;flex:1}
.ai-room-name{font-size:.84rem;font-weight:700;color:#1a1a1a;line-height:1.3}
.ai-room-branch{font-size:.74rem;color:#888;display:flex;align-items:center;gap:3px}
.ai-room-price{font-size:.86rem;font-weight:700;color:#996d4e;margin-top:2px}
.ai-room-price span{font-size:.7rem;font-weight:400;color:#999}
.ai-room-book{display:inline-flex;align-items:center;gap:4px;margin-top:5px;padding:5px 10px;background:#996d4e;color:#fff !important;border-radius:8px;font-size:.76rem;font-weight:600;text-decoration:none !important;transition:background .2s;align-self:flex-start}
.ai-room-book:hover{background:#7a573e}
.ai-see-more-btn{display:flex;align-items:center;gap:6px;margin-top:4px;padding:8px 14px;background:#f5f0eb;color:#996d4e;border:1.5px solid #e8ddd4;border-radius:10px;font-size:.8rem;font-weight:700;cursor:pointer;transition:all .2s;font-family:inherit;width:fit-content}
.ai-see-more-btn:hover{background:#ede4da;border-color:#c9a07a}
.ai-input-area{padding:16px 20px;background:#fff;border-top:1px solid rgba(0,0,0,.05);display:flex;gap:10px;align-items:center}
#luxnest-ai-input{flex:1;border:1.5px solid #f0f0f0;border-radius:14px;padding:11px 16px;font-size:.92rem;outline:none;transition:all .3s;background:#f9f9f9;font-family:inherit}
#luxnest-ai-input:focus{background:#fff;border-color:#996d4e;box-shadow:0 0 0 3px rgba(153, 109, 78,.1)}
#luxnest-ai-send{width:44px;height:44px;border:none;background:#996d4e;color:#fff;border-radius:12px;display:flex;align-items:center;justify-content:center;cursor:pointer;font-size:1.2rem;transition:all .3s;flex-shrink:0}
#luxnest-ai-send:hover{background:#7a573e;transform:translateY(-1px)}
@media(max-width:500px){
    #luxnest-ai-widget{bottom:100px;right:16px}
    #luxnest-ai-window{position:fixed;bottom:0;left:0;right:0;top:auto;width:100%;height:88dvh;border-radius:16px 16px 0 0;box-shadow:0 -8px 40px rgba(0,0,0,.18)}
}
</style>

<script>
window.CHAT_URL   = '{{ route("chat.send") }}';
window.CSRF_TOKEN = '{{ csrf_token() }}';
</script>
<script src="{{ asset_v('assets/js/ai-chat.js') }}"></script>
