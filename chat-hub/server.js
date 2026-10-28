'use strict';
/**
 * geranol-chat — استیبل چت کشف نیازمندی مشتری آزمایشگاه گرانول
 * استاتیک فرانت + بک‌اند کوچک، بدون وابستگی خارجی (node:http فقط).
 *
 * ایزولاسیون:
 *  - هر بازدیدکننده session خودش را دارد (کوکی HttpOnly) — گفتگوها به هم نمی‌ریزند.
 *  - پرامپت سیستم فقط از prompt.md خوانده می‌شود؛ هیچ دادهٔ دیگری به مدل نمی‌رود.
 *  - کلیدها فقط سمت سرور از env خوانده می‌شوند و هرگز لاگ/برگشت نمی‌شوند.
 *
 * دسترسی: صفحهٔ چت برای هر دارندهٔ لینک باز است (فرمان کاربر 2026-10-28: «اصلا پسوردی نمیخواد»)؛
 * کنترل سوءاستفاده فقط rate-limit 20 پیام/ساعت per session. /admin پشت basicAuth ترافیک.
 */

const http = require('node:http');
const fs = require('node:fs');
const path = require('node:path');
const crypto = require('node:crypto');

const PORT = parseInt(process.env.PORT || '8791', 10);
const ROOT = __dirname;
const DATA_DIR = process.env.DATA_DIR || path.join(ROOT, 'data');
const MAX_TURNS = 40;            // سقف نوبت هر گفتگو
const RATE_LIMIT = 20;           // پیام در ساعت per session
const UPSTREAM_TIMEOUT = 30000;

// زنجیرهٔ مدل: Hive → Kilo بی‌کلید → OR (اگر کلید env باشد).
// کلیدها فقط سمت سرور، از env، هرگز لاگ نمی‌شوند.
const CHAIN = [
  { base: 'https://api-cdn.thehive.ai/api/v3', id: 'zai-org/glm-5.3-flash', key: (process.env.HIVE_API_KEY || '').trim() },
  { base: 'https://api-cdn.thehive.ai/api/v3', id: 'deepseek-ai/deepseek-v4.1-flash-1017', key: (process.env.HIVE_API_KEY || '').trim() },
  { base: 'https://ai-gateway.kilo.ai/api/v1', id: 'cohere/north-mini-code:free', key: '' },
  { base: 'https://ai-gateway.kilo.ai/api/v1', id: 'nvidia/nemotron-3-ultra-550b-a55b:free', key: '' },
  { base: 'https://openrouter.ai/api/v1', id: 'z-ai/glm-5.3-flash', key: (process.env.OPENROUTER_API_KEY || '').trim() },
];
// زنجیرهٔ فعال: لایه‌های کلیددار (اگر env باشد) + همیشه لایه‌های بی‌کلید
const chain = CHAIN.filter((c) => c.key || !c.key);
const MODEL_ID = chain[0].id;

fs.mkdirSync(DATA_DIR, { recursive: true });
const PROMPT = fs.readFileSync(path.join(ROOT, 'prompt.md'), 'utf8');

// ---------- گیت پسورد کلاینت ----------
// پسورد هش‌شده (sha256 hex) از env یا فایل .chat-pass-hash کنار اپ؛
// اپ خودش هرگز مقدار خام را نمی‌بیند.
// معماری (اصلاح 2026-10-28 بعدازظهر): استاتیک صفحهٔ چت — از جمله خود صفحهٔ گیت —
// همیشه بدون رمز 200 است (ریشهٔ باگ CSS قبلی: 401 شدن assetها برای بازدیدکنندهٔ تازه)؛
// فقط APIهای گفتگو (message/feedback) تا unlock قفل‌اند.
let CHAT_PASS_HASH = (process.env.CHAT_PASS_HASH || '').trim();
if (!CHAT_PASS_HASH) {
  const hp = path.join(ROOT, '.chat-pass-hash');
  if (fs.existsSync(hp)) CHAT_PASS_HASH = fs.readFileSync(hp, 'utf8').trim();
}
const chatCookies = (req) => Object.fromEntries((req.headers.cookie || '').split(';').map((v) => {
  const i = v.indexOf('='); return i < 0 ? ['', ''] : [v.slice(0, i).trim(), decodeURIComponent(v.slice(i + 1).trim())];
}).filter(([k]) => k));
const sha256 = (s) => crypto.createHash('sha256').update(s, 'utf8').digest('hex');
const chatUnlocked = (req) => {
  if (!CHAT_PASS_HASH) return true; // گیت غیرفعال اگر هشی تنظیم نشده باشد
  const c = chatCookies(req);
  return c.gchatpass === sha256('unlocked:' + CHAT_PASS_HASH);
};

// ---------- کوچک: ابزارها ----------
const json = (res, code, obj) => {
  const body = JSON.stringify(obj);
  res.writeHead(code, { 'Content-Type': 'application/json; charset=utf-8' });
  res.end(body);
};
const readBody = (req) => new Promise((resolve, reject) => {
  let d = '';
  req.on('data', (c) => { d += c; if (d.length > 1e6) { reject(new Error('too large')); req.destroy(); } });
  req.on('end', () => resolve(d));
  req.on('error', reject);
});
const logLine = (file, obj) => {
  fs.appendFile(path.join(DATA_DIR, file), JSON.stringify(obj) + '\n', () => {});
};

// session store — حافظه + بازسازی از دیسک (dialogue ها روی دیسک ماندگارند)
const sessions = new Map(); // sid -> { turns: [{role, content}], created, msgsThisHour: [], meta }
const loadSession = (sid) => {
  if (sessions.has(sid)) return sessions.get(sid);
  const s = { turns: [], created: new Date().toISOString(), msgsThisHour: [] };
  const f = path.join(DATA_DIR, 'dialogues', sid + '.json');
  try {
    if (fs.existsSync(f)) {
      const d = JSON.parse(fs.readFileSync(f, 'utf8'));
      s.turns = d.turns || [];
      s.created = d.created || s.created;
    }
  } catch { /* فایل خراب = شروع تازه */ }
  sessions.set(sid, s);
  return s;
};
const saveSession = (sid, s) => {
  fs.mkdirSync(path.join(DATA_DIR, 'dialogues'), { recursive: true });
  fs.writeFileSync(path.join(DATA_DIR, 'dialogues', sid + '.json'),
    JSON.stringify({ created: s.created, updated: new Date().toISOString(), turns: s.turns }, null, 1));
};

// ---------- upstream مدل ----------
async function callModel(turns) {
  const messages = [{ role: 'system', content: PROMPT }, ...turns.slice(-MAX_TURNS)];
  let lastErr;
  for (const layer of chain) {
    const headers = { 'Content-Type': 'application/json' };
    if (layer.key) headers.Authorization = 'Bearer ' + layer.key;
    const body = JSON.stringify({ model: layer.id, messages, max_tokens: 350, temperature: 0.6 });
    const ctrl = new AbortController();
    const t = setTimeout(() => ctrl.abort(), UPSTREAM_TIMEOUT);
    try {
      const r = await fetch(layer.base + '/chat/completions', {
        method: 'POST', headers, body, signal: ctrl.signal,
      });
      if (!r.ok) throw new Error('upstream ' + r.status);
      const d = await r.json();
      const ch = d && d.choices && d.choices[0];
      const text = ch && ch.message && ch.message.content;
      if (!text) throw new Error('empty completion');
      // پاسخ بریده‌شده (finish_reason=length) = پنل ناقص/جملهٔ نصفه — لایهٔ بعدی را امتحان کن
      if (ch.finish_reason === 'length') throw new Error('truncated (finish_reason=length)');
      return { text, layer: layer.id };
    } catch (e) {
      lastErr = e;
      logLine('errors.jsonl', { ts: new Date().toISOString(), layer: layer.id, err: String(e && e.message || e) });
    } finally { clearTimeout(t); }
  }
  throw lastErr || new Error('no model layer');
}

// ---------- فرانت ----------
const STATIC = {
  '/': ['index.html', 'text/html; charset=utf-8'],
  '/app.js': ['app.js', 'text/javascript; charset=utf-8'],
  '/app.css': ['app.css', 'text/css; charset=utf-8'],
  '/fonts.css': ['fonts.css', 'text/css; charset=utf-8'],
};

const server = http.createServer(async (req, res) => {
  const url = new URL(req.url, 'http://x');
  const pathName = url.pathname;

  try {
    // --- health
    if (pathName === '/health') return json(res, 200, { ok: true, model: MODEL_ID });

    // --- static: همیشه عمومی — صفحهٔ گیت استایل‌دار برای بازدیدکنندهٔ تازه باید کامل رندر شود
    if (STATIC[pathName] && req.method === 'GET') {
      const [file, type] = STATIC[pathName];
      const c = fs.readFileSync(path.join(ROOT, 'public', file));
      res.writeHead(200, { 'Content-Type': type, 'Cache-Control': 'no-cache' });
      return res.end(c);
    }

    // --- وضعیت گیت برای فرانت (عمومی؛ فقط true/false)
    if (pathName === '/api/session' && req.method === 'GET') {
      return json(res, 200, { unlocked: chatUnlocked(req), gate: !!CHAT_PASS_HASH });
    }

    // --- unlock گیت (عمومی؛ نقطهٔ ورود)
    if (pathName === '/api/unlock' && req.method === 'POST') {
      if (!CHAT_PASS_HASH) return json(res, 200, { ok: true, gate: false });
      const body = await readBody(req);
      let pass = '';
      try { pass = String(JSON.parse(body).password || ''); } catch { /* ignore */ }
      if (!pass || sha256(pass) !== CHAT_PASS_HASH) {
        logLine('auth-failures.jsonl', { ts: new Date().toISOString(), ip: req.socket.remoteAddress });
        return json(res, 401, { ok: false, error: 'رمز درست نیست.' });
      }
      res.setHeader('Set-Cookie', `gchatpass=${sha256('unlocked:' + CHAT_PASS_HASH)}; Path=/; HttpOnly; SameSite=Lax; Max-Age=2592000`);
      return json(res, 200, { ok: true, gate: true });
    }

    // --- APIهای گفتگو: پشت گیت — استاتیک و session/unlock هرگز از این خط رد نمی‌شوند
    if (['/api/message', '/api/feedback'].includes(pathName) && !chatUnlocked(req)) {
      logLine('auth-failures.jsonl', { ts: new Date().toISOString(), path: pathName, ip: req.socket.remoteAddress });
      return json(res, 401, { gate: true, error: 'این گفتگو با رمز محافظت می‌شود.' });
    }

    // --- شروع/ادامهٔ گفتگو
    if (pathName === '/api/message' && req.method === 'POST') {
      // session id از کوکی؛ تازه = گفتگوی تازه
      const cookies = chatCookies(req);
      let sid = cookies.gchat;
      let fresh = false;
      if (!sid || !/^[a-f0-9]{16}$/.test(sid)) {
        sid = crypto.randomBytes(8).toString('hex');
        fresh = true;
      }
      const s = loadSession(sid);
      // rate limit per session
      const now = Date.now();
      s.msgsThisHour = (s.msgsThisHour || []).filter((t) => now - t < 3600e3);
      if (s.msgsThisHour.length >= RATE_LIMIT) return json(res, 429, { error: 'تعداد پیام‌ها بیش از حد مجاز است؛ کمی بعد تلاش کنید.' });

      const body = await readBody(req);
      let msg = '';
      try { msg = String(JSON.parse(body).message || '').slice(0, 2000); } catch { /* ignore */ }
      msg = msg.trim();
      if (!msg) return json(res, 400, { error: 'پیام خالی است.' });

      if (fresh || s.turns.length === 0) {
        s.turns.push({ role: 'assistant', content: 'سلام و خوش‌آمدید — من دستیار پروژهٔ وب آزمایشگاه گرانول هستم.' });
      }
      s.turns.push({ role: 'user', content: msg });
      s.msgsThisHour.push(now);

      let reply, servedBy;
      try { ({ text: reply, layer: servedBy } = await callModel(s.turns)); }
      catch (e) {
        logLine('errors.jsonl', { ts: new Date().toISOString(), sid, err: String(e && e.message || e) });
        return json(res, 502, { error: 'الان نمی‌توانم پاسخ دهم؛ چند لحظه بعد دوباره بنویسید.' });
      }

      s.turns.push({ role: 'assistant', content: reply });
      saveSession(sid, s);
      logLine('conversations.jsonl', { ts: new Date().toISOString(), sid, servedBy, user: msg, assistant: reply });

      res.setHeader('Set-Cookie', `gchat=${sid}; Path=/; HttpOnly; SameSite=Lax; Max-Age=2592000`);
      return json(res, 200, { reply, sessionId: sid });
    }

    // --- بازخورد مشتری روی جمع‌بندی
    if (pathName === '/api/feedback' && req.method === 'POST') {
      const cookies = chatCookies(req);
      const sid = cookies.gchat || 'anon';
      const body = await readBody(req);
      let note = '';
      try { note = String(JSON.parse(body).note || '').slice(0, 2000); } catch { /* ignore */ }
      if (!note.trim()) return json(res, 400, { error: 'یادداشت خالی است.' });
      logLine('client-notes.jsonl', { ts: new Date().toISOString(), sid, note: note.trim() });
      return json(res, 200, { ok: true });
    }

    // --- پنل بازبینی تیم (پشت basicAuth ترافیک) — خروجی مارک‌داون زنده
    if (pathName === '/admin/' && req.method === 'GET') {
      const dialoguesDir = path.join(DATA_DIR, 'dialogues');
      let files = [];
      try { files = fs.readdirSync(dialoguesDir).filter((f) => f.endsWith('.json')); } catch { /* none */ }
      const tehran = (iso) => {
        if (!iso) return '';
        try { return new Date(new Date(iso).getTime() + 3.5 * 3600e3).toISOString().slice(0, 16).replace('T', ' '); }
        catch { return String(iso).slice(0, 16); }
      };
      const rows = files.map((f) => {
        try {
          const d = JSON.parse(fs.readFileSync(path.join(dialoguesDir, f), 'utf8'));
          const userTurns = (d.turns || []).filter((t) => t.role === 'user').length;
          const lastUser = [...(d.turns || [])].reverse().find((t) => t.role === 'user');
          return { id: f.replace('.json', ''), created: d.created, updated: d.updated, userTurns,
                   last: lastUser ? String(lastUser.content).slice(0, 80) : '' };
        } catch { return null; }
      }).filter(Boolean);
      // آمار داشبورد (پیام‌های امروز به وقت تهران؛ هر خط conversations.jsonl = یک تبادل کاربر+دستیار)
      let msgsToday = 0;
      try {
        const today = new Date(Date.now() + 3.5 * 3600e3).toISOString().slice(0, 10);
        for (const l of fs.readFileSync(path.join(DATA_DIR, 'conversations.jsonl'), 'utf8').split('\n')) {
          if (!l.trim()) continue;
          try { const e = JSON.parse(l); if (String(e.ts || '').slice(0, 10) === today) msgsToday += 2; } catch { /* skip */ }
        }
      } catch { /* no log yet */ }
      const lastAct = rows.reduce((m, r) => ((r.updated && r.updated > m) ? r.updated : m), '');
      const qnStats = qnStatsNow();
      const esc = (s) => String(s).replace(/[&<>"']/g, (c) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
      const html = '<!DOCTYPE html><html lang="fa" dir="rtl"><head><meta charset="utf-8">' +
        '<link rel="stylesheet" href="/fonts.css">' +
        '<title>داشبورد گرانول</title>' +
        '<style>' +
        'body{font-family:Vazirmatn,sans-serif;direction:rtl;background:#f4f6f8;margin:0;padding:24px;color:#1c2733}' +
        '.wrap{max-width:960px;margin:0 auto}' +
        'h1{font-size:22px;margin:0 0 4px}' +
        '.sub{color:#5b6b7b;font-size:13px;margin:0 0 16px}' +
        '.hint{background:#fff8e6;border:1px solid #f0dfa8;color:#7a5b00;border-radius:10px;padding:10px 14px;font-size:13px;margin:0 0 16px}' +
        '.stats{display:flex;gap:12px;flex-wrap:wrap;margin:0 0 16px}' +
        '.stat{flex:1;min-width:180px;background:#fff;border:1px solid #e3e9ef;border-radius:12px;padding:14px 16px}' +
        '.stat .k{color:#5b6b7b;font-size:12px;margin-bottom:6px}' +
        '.stat .v{font-size:24px;font-weight:700}' +
        '.btns{margin:0 0 16px}' +
        '.btns a{display:inline-block;background:#1f6f43;color:#fff;text-decoration:none;border-radius:10px;padding:9px 16px;font-size:13px;margin-inline-end:8px}' +
        'table{width:100%;border-collapse:separate;border-spacing:0;background:#fff;border:1px solid #e3e9ef;border-radius:12px;overflow:hidden;font-size:13px}' +
        'th,td{padding:8px 10px;border-bottom:1px solid #eef2f6;text-align:right}' +
        'th{background:#f0f4f8;font-size:12px;color:#41556b}' +
        '</style></head><body><div class="wrap">' +
        '<h1>داشبورد گرانول</h1>' +
        '<p class="sub">بازبینی گفتگوهای مشتری · geranol-chat.nsar.ir</p>' +
        '<p class="hint">🔑 ورود این صفحه با <b>رمز ادمین</b> است — رمز گفتگوی مشتری این‌جا کار نمی‌کند.</p>' +
        '<div class="stats">' +
        '<div class="stat"><div class="k">تعداد گفتگوها</div><div class="v">' + rows.length + '</div></div>' +
        '<div class="stat"><div class="k">پیام‌های امروز</div><div class="v">' + msgsToday + '</div></div>' +
        '<div class="stat"><div class="k">آخرین فعالیت</div><div class="v" style="font-size:15px">' + (lastAct ? esc(tehran(lastAct)) : '—') + '</div></div>' +
        '</div>' +
        '<div class="btns"><a href="/admin/export.md">دانلود خروجی مارک‌داون</a><a href="/admin/export.json" style="background:#41556b">JSON خام</a></div>' +
        '<h2 style="font-size:16px;margin:0 0 8px">پرسشنامهٔ مشتری</h2>' +
        (qnStats
          ? '<div class="stats">' +
            '<div class="stat"><div class="k">پاسخ‌های ثبت‌شده</div><div class="v">' + qnStats.count + '</div></div>' +
            '<div class="stat"><div class="k">سؤال‌های پاسخ‌داده‌شده (آخرین)</div><div class="v" style="font-size:17px">' + qnStats.answered + ' از ' + qnStats.total + '</div></div>' +
            '<div class="stat"><div class="k">آخرین پاسخ</div><div class="v" style="font-size:15px">' + esc(qnStats.last) + '</div></div>' +
            '</div>' +
            '<div class="btns"><a href="https://pages.nsar.ir/r/AGtoBwIt8XcLOLSrO_etTZkYlzwg4BFx/">مشاهدهٔ نتایج (فقط‌خواندنی)</a><a href="https://pages.nsar.ir/r/AGtoBwIt8XcLOLSrO_etTZkYlzwg4BFx/responses.xlsx" style="background:#41556b">اکسل پاسخ‌ها</a></div>'
          : '<p class="sub">آمار پرسشنامه در دسترس نیست.</p>') +
        '<table>' +
        '<tr><th>شناسه</th><th>آغاز</th><th>پیام‌های مشتری</th><th>آخرین فعالیت</th><th>آخرین پیام مشتری</th></tr>' +
        rows.map((r) => '<tr><td>' + esc(r.id.slice(0, 8)) + '</td><td>' + esc(String(r.created).slice(0, 16)) +
          '</td><td>' + r.userTurns + '</td><td>' + esc(tehran(r.updated)) + '</td><td>' + esc(r.last) + '</td></tr>').join('') +
        '</table></div></body></html>';
      res.writeHead(200, { 'Content-Type': 'text/html; charset=utf-8' });
      return res.end(html);
    }

    // --- خروجی‌ها (پشت basicAuth ترافیک)
    if (pathName === '/admin/export.md' && req.method === 'GET') {
      const md = buildMarkdown();
      res.writeHead(200, { 'Content-Type': 'text/markdown; charset=utf-8' });
      return res.end(md);
    }
    if (pathName === '/admin/export.json' && req.method === 'GET') {
      const dialoguesDir = path.join(DATA_DIR, 'dialogues');
      let files = [];
      try { files = fs.readdirSync(dialoguesDir).filter((f) => f.endsWith('.json')); } catch { /* none */ }
      const all = files.map((f) => {
        try { return JSON.parse(fs.readFileSync(path.join(dialoguesDir, f), 'utf8')); } catch { return null; }
      }).filter(Boolean);
      return json(res, 200, { exported: new Date().toISOString(), count: all.length, dialogues: all });
    }

    return json(res, 404, { error: 'not found' });
  } catch (e) {
    logLine('errors.jsonl', { ts: new Date().toISOString(), err: String(e && e.stack || e) });
    return json(res, 500, { error: 'internal error' });
  }
});

// ---------- ساخت خروجی مارک‌داون (برای /admin/export.md) ----------
const phoneRe = /(09\d{9})/g;
const mask = (t) => String(t).replace(phoneRe, '09xxxxxxxxx');

function buildMarkdown() {
  const dialoguesDir = path.join(DATA_DIR, 'dialogues');
  let files = [];
  try { files = fs.readdirSync(dialoguesDir).filter((f) => f.endsWith('.json')); } catch { /* none */ }
  const parts = [
    '# گفتگوهای مشتری — پروژهٔ گرانول',
    '',
    'تعداد گفتگوها: ' + files.length + ' — منبع: geranol-chat.nsar.ir (خودکار از دیتای چت‌هاب)',
    '',
    'ورودی تحلیل: منشور نسخهٔ ۱ + بک‌لاگ؛ بستن Issueهای #6/#7 پس از جمع‌بندی.',
    '',
  ];
  for (const f of files) {
    try {
      const d = JSON.parse(fs.readFileSync(path.join(dialoguesDir, f), 'utf8'));
      parts.push('## گفتگو `' + f.replace('.json', '').slice(0, 8) + '`');
      parts.push('- آغاز: ' + (d.created || '?') + ' — آخرین: ' + (d.updated || '?') + ' — نوبت‌ها: ' + (d.turns || []).length);
      parts.push('');
      for (const t of d.turns || []) {
        let who, txt = String(t.content || '');
        if (t.role === 'user') who = '**مشتری:**';
        else {
          who = '**دستیار:**';
          const i = txt.indexOf('```oui');
          if (i >= 0) txt = txt.slice(0, i).trim(); // پنل UI است — فقط نثر به export
        }
        parts.push('- ' + who + ' ' + mask(txt).replace(/\n/g, ' / '));
      }
      parts.push('');
    } catch { /* skip broken */ }
  }
  // یادداشت‌های آزاد
  try {
    const notes = fs.readFileSync(path.join(DATA_DIR, 'client-notes.jsonl'), 'utf8')
      .split('\n').filter(Boolean).map((l) => { try { return JSON.parse(l); } catch { return null; } }).filter(Boolean);
    if (notes.length) {
      parts.push('## یادداشت‌های آزاد مشتری', '');
      for (const n of notes) parts.push('- (' + String(n.ts).slice(0, 10) + ') ' + mask(n.note));
      parts.push('');
    }
  } catch { /* no notes */ }
  return parts.join('\n');
}

// ---------- آمار زندهٔ پرسشنامهٔ مشتری (فقط‌خواندنی از خروجی رسمی نتایج؛ هر ۵ دقیقه) ----------
const QN_RESULTS_CSV = 'https://pages.nsar.ir/r/AGtoBwIt8XcLOLSrO_etTZkYlzwg4BFx/responses.csv';
let qnCache = null;

function parseCsvRows(text) {
  const rows = [];
  let row = [], cell = '', inQ = false;
  const t = String(text).replace(/^\uFEFF/, '');
  for (let i = 0; i < t.length; i++) {
    const ch = t[i];
    if (inQ) {
      if (ch === '"') { if (t[i + 1] === '"') { cell += '"'; i++; } else inQ = false; }
      else cell += ch;
    } else if (ch === '"') inQ = true;
    else if (ch === ',') { row.push(cell); cell = ''; }
    else if (ch === '\n') { row.push(cell); rows.push(row); row = []; cell = ''; }
    else if (ch !== '\r') cell += ch;
  }
  if (cell !== '' || row.length) { row.push(cell); rows.push(row); }
  return rows.filter((r) => r.some((c) => String(c).trim() !== ''));
}

async function refreshQnStats() {
  try {
    const r = await fetch(QN_RESULTS_CSV, { signal: AbortSignal.timeout(8000) });
    if (!r.ok) throw new Error('http ' + r.status);
    const rows = parseCsvRows(await r.text());
    if (rows.length < 2) throw new Error('empty export');
    const header = rows[0];
    const timeCol = header.findIndex((h) => String(h).includes('زمان ثبت'));
    const qIdx = header.map((h, i) => (i === 0 || i === timeCol ? -1 : i)).filter((i) => i >= 0);
    const dataRows = rows.slice(1);
    const lastRow = dataRows[dataRows.length - 1];
    const answered = qIdx.reduce((n, i) => n + (String(lastRow[i] || '').trim() ? 1 : 0), 0);
    qnCache = {
      count: dataRows.length,
      answered,
      total: qIdx.length,
      last: lastRow[timeCol >= 0 ? timeCol : 1] || '',
      at: new Date().toISOString(),
    };
  } catch (e) {
    if (!qnCache) qnCache = null;
    logLine('errors.jsonl', { ts: new Date().toISOString(), err: 'qnStats: ' + String(e && e.message || e) });
  }
}
refreshQnStats();
setInterval(refreshQnStats, 5 * 60e3).unref();
function qnStatsNow() { return qnCache; }

server.listen(PORT, process.env.HOST || '127.0.0.1', () => console.log('geranol-chat listening on ' + (process.env.HOST || '127.0.0.1') + ':' + PORT));
