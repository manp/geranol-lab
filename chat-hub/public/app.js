'use strict';
/* geranol-chat فرانت — بدون وابستگی؛ گیت پسورد فقط روی APIهای گفتگو (استاتیک همیشه باز) */
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
      renderRichReply(d.reply);
      guessPhase(d.reply);
    }
  } catch {
    typing(false);
    addMsg('ارتباط برقرار نشد؛ اینترنت خود را چک کنید و دوباره بفرستید.', 'bot');
  }
  send.disabled = false;
  input.focus();
}

// ---------- رندر پنل OpenUI (```oui) ----------
// فرمت بلوک: خط‌محور — card/note/row/list/tags/callout/followup.
// بخشنده (forgiving): هر خط مستقل پارس می‌شود؛ خرابی → کارت متنی استایل‌دار.
function el(tag, cls, text) {
  const e = document.createElement(tag);
  if (cls) e.className = cls;
  if (text != null) e.textContent = text;
  return e;
}

function splitOuiBlocks(text) {
  const parts = [];
  const re = /```oui[^\n]*\n([\s\S]*?)(?:```|$)/g;
  let last = 0, m;
  while ((m = re.exec(text))) {
    const before = text.slice(last, m.index).trim();
    if (before) parts.push({ t: 'text', v: before });
    parts.push({ t: 'block', v: m[1] });
    last = re.lastIndex;
  }
  const tail = text.slice(last).trim();
  if (tail) parts.push({ t: 'text', v: tail });
  if (!parts.length) parts.push({ t: 'text', v: text });
  return parts;
}

function parseOuiBlock(src) {
  const out = { title: '', note: '', rows: [], list: [], tags: [], callouts: [], followups: [], unknown: [] };
  for (const raw of String(src).split('\n')) {
    const line = raw.trim();
    if (!line || line.startsWith('```')) continue;
    const m = line.match(/^([A-Za-z_\u0600-\u06FF]+)\s*:\s*(.*)$/);
    if (!m) { out.unknown.push(line); continue; }
    const k = m[1].toLowerCase();
    const v = m[2].trim();
    if (k === 'card' && !out.title) out.title = v;
    else if (k === 'note' && !out.note) out.note = v;
    else if (k === 'row') {
      const i = v.indexOf('|');
      if (i > 0) out.rows.push([v.slice(0, i).trim(), v.slice(i + 1).trim()]);
      else out.unknown.push(v);
    } else if (k === 'list') out.list.push(v);
    else if (k === 'tags') String(v).split(/[،,]/).map((s) => s.trim()).filter(Boolean).forEach((t) => out.tags.push(t));
    else if (k === 'callout') out.callouts.push(v);
    else if (k === 'followup') out.followups.push(v);
    else out.unknown.push(line);
  }
  return out;
}

function renderOuiPanel(parsed) {
  const wrap = el('div', 'msg bot');
  const card = el('div', 'oui-card');
  const head = el('div', 'oui-head');
  head.appendChild(el('div', 'oui-title', parsed.title || 'پنل'));
  if (parsed.note) head.appendChild(el('div', 'oui-note', parsed.note));
  card.appendChild(head);

  const body = el('div', 'oui-body');
  if (parsed.rows.length) {
    const rows = el('div', 'oui-rows');
    for (const [k, v] of parsed.rows) {
      const r = el('div', 'oui-row');
      r.appendChild(el('span', 'oui-k', k));
      r.appendChild(el('span', 'oui-v', v));
      rows.appendChild(r);
    }
    body.appendChild(rows);
  }
  if (parsed.list.length) {
    const ul = el('ul', 'oui-list');
    for (const item of parsed.list) ul.appendChild(el('li', null, item));
    body.appendChild(ul);
  }
  if (parsed.tags.length) {
    const tg = el('div', 'oui-tags');
    for (const t of parsed.tags) tg.appendChild(el('span', 'oui-chip', t));
    body.appendChild(tg);
  }
  for (const c of parsed.callouts) body.appendChild(el('div', 'oui-callout', c));
  for (const u of parsed.unknown) body.appendChild(el('div', 'oui-plain', u));
  if (!parsed.rows.length && !parsed.list.length && !parsed.tags.length && !parsed.callouts.length && !parsed.unknown.length) {
    body.appendChild(el('div', 'oui-plain', '—'));
  }
  card.appendChild(body);

  if (parsed.followups.length) {
    const fus = el('div', 'oui-followups');
    for (const f of parsed.followups.slice(0, 4)) {
      const b = el('button', 'oui-fu', f);
      b.type = 'button';
      b.addEventListener('click', () => { input.value = f; sendMsg(); });
      fus.appendChild(b);
    }
    card.appendChild(fus);
  }
  wrap.appendChild(card);
  return wrap;
}

function renderRichReply(text) {
  for (const part of splitOuiBlocks(text || '')) {
    if (part.t === 'text') { addMsg(part.v, 'bot'); continue; }
    const parsed = parseOuiBlock(part.v);
    chat.appendChild(renderOuiPanel(parsed));
    chat.scrollTop = chat.scrollHeight;
  }
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
// صفحهٔ گیت خودش بخشی از صفحهٔ عمومی است (استایل‌دار)؛ فقط APIهای گفتگو قفل‌اند.
function showGate() {
  gate.hidden = false;
  chatShell.hidden = false;
  chatShell.style.visibility = 'hidden';
  chat.style.display = 'none';
  document.querySelector('.composer').hidden = true;
  gatePass.focus();
}
function showChat() {
  gate.hidden = true;
  chatShell.style.visibility = '';
  chat.style.display = '';
  document.querySelector('.composer').hidden = false;
}

(async function initGate() {
  try {
    const r = await fetch('/api/session', { method: 'GET' });
    const d = await r.json();
    if (d.gate && !d.unlocked) showGate();
    else showChat();
  } catch { showGate(); } // اگر probe ناموفق بود، محافظه‌کارانه گیت نشان بده
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

