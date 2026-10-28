'use strict';
/* geranol-chat فرانت — بدون وابستگی */
const gate = document.getElementById('gate');
const gateForm = document.getElementById('gate-form');
const gatePass = document.getElementById('gate-pass');
const gateErr = document.getElementById('gate-err');
const chatShell = document.getElementById('chat-shell');
const chat = document.getElementById('chat');
const form = document.getElementById('form');
const input = document.getElementById('input');
const send = document.getElementById('send');
const phasePill = document.getElementById('phase-pill');
const notes = document.getElementById('notes');
const noteBox = document.getElementById('note');
const noteSave = document.getElementById('note-save');
const noteOk = document.getElementById('note-ok');

const PHASES = ['شروع گفتگو', 'شناخت آزمایشگاه', 'خدمات و آزمایش‌ها', 'نمونه و مراجعان', 'پنل مدیریت', 'پرداخت و اعلان', 'وب‌سایت و راه‌اندازی', 'جمع‌بندی'];
let phase = 0;

function addMsg(text, who) {
  const wrap = document.createElement('div');
  wrap.className = 'msg ' + (who === 'me' ? 'me' : 'bot');
  const b = document.createElement('div');
  b.className = 'bubble';
  b.textContent = text;
  wrap.appendChild(b);
  chat.appendChild(wrap);
  chat.scrollTop = chat.scrollHeight;
  return b;
}

function typing(on) {
  if (on) {
    const wrap = document.createElement('div');
    wrap.className = 'msg bot';
    wrap.id = 'typing';
    wrap.innerHTML = '<div class="bubble"><span class="typing"><i></i><i></i><i></i></span></div>';
    chat.appendChild(wrap);
    chat.scrollTop = chat.scrollHeight;
  } else {
    const t = document.getElementById('typing');
    if (t) t.remove();
  }
}

function setPhase(i) {
  if (i !== phase && i < PHASES.length) {
    phase = i;
    phasePill.textContent = PHASES[i];
  }
}

// تشخیص تقریبی فاز از متن پاسخ — فقط برای نمایش پیل
function guessPhase(text) {
  const t = text || '';
  if (/پرسشنامه|فرم رسمی/.test(t)) setPhase(1);
  if (/گروه|آزمایش.*انجام|توصیهٔ کودی|برگهٔ گزارش/.test(t)) setPhase(2);
  if (/نمونه‌برداری|مراجع|مزرعه/.test(t)) setPhase(3);
  if (/پنل مدیریت|گردش|وضعیت نمونه/.test(t)) setPhase(4);
  if (/پرداخت|درگاه|پیامک|اعلان|نتیجهٔ آماده/.test(t)) setPhase(5);
  if (/وب‌سایت|صفحات|آموزش|راه‌اندازی/.test(t)) setPhase(6);
  if (/جمع‌بندی|برداشت‌ها/.test(t)) { setPhase(7); notes.hidden = false; }
}

async function sendMsg() {
  const text = input.value.trim();
  if (!text) return;
  addMsg(text, 'me');
  input.value = '';
  input.style.height = 'auto';
  send.disabled = true;
  typing(true);
  try {
    const r = await fetch('/api/message', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ message: text }),
    });
    const d = await r.json();
    typing(false);
    if (!r.ok) {
      addMsg(d.error || 'خطایی پیش آمد؛ دوباره تلاش کنید.', 'bot');
    } else {
      addMsg(d.reply, 'bot');
      guessPhase(d.reply);
    }
  } catch {
    typing(false);
    addMsg('ارتباط برقرار نشد؛ اینترنت خود را چک کنید و دوباره بفرستید.', 'bot');
  }
  send.disabled = false;
  input.focus();
}

form.addEventListener('submit', (e) => { e.preventDefault(); sendMsg(); });
input.addEventListener('keydown', (e) => {
  if (e.key === 'Enter' && !e.shiftKey) { e.preventDefault(); sendMsg(); }
});
input.addEventListener('input', () => {
  input.style.height = 'auto';
  input.style.height = Math.min(input.scrollHeight, 120) + 'px';
});

noteSave.addEventListener('click', async () => {
  const note = noteBox.value.trim();
  if (!note) return;
  noteSave.disabled = true;
  try {
    const r = await fetch('/api/feedback', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ note }),
    });
    if (r.ok) { noteOk.hidden = false; noteBox.value = ''; }
    else noteOk.hidden = false, noteOk.textContent = 'خطا در ثبت';
  } catch { noteOk.hidden = false; noteOk.textContent = 'خطا در ثبت'; }
  noteSave.disabled = false;
});

// ---------- گیت پسورد ----------
function showGate() {
  gate.hidden = false;
  document.querySelectorAll('.topbar, .chat, .composer, .foot').forEach((el) => { if (el.id !== 'chat-shell') el.hidden = true; });
  chatShell.hidden = false;
  chatShell.style.visibility = 'hidden';
  gatePass.focus();
}
function showChat() {
  gate.hidden = true;
  chatShell.style.visibility = '';
}

(async function initGate() {
  try {
    // اگر کوچک unlock هست، API آزاد است؛ یک preflight ارزان می‌زنیم
    const probe = await fetch('/app.css', { method: 'GET' });
    if (probe.status === 401) { showGate(); return; }
    showChat();
  } catch { showChat(); }
})();

gateForm.addEventListener('submit', async (e) => {
  e.preventDefault();
  gateErr.hidden = true;
  const pass = gatePass.value;
  if (!pass) return;
  try {
    const r = await fetch('/api/unlock', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ password: pass }),
    });
    if (r.ok) {
      showChat();
    } else {
      gateErr.hidden = false;
      gatePass.value = '';
      gatePass.focus();
    }
  } catch {
    gateErr.hidden = false;
  }
});
