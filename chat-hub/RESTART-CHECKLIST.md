# RESTART-CHECKLIST — چت‌هاب گرانول

## ⛔ قاعدهٔ ایستادهٔ داده (اول از همه بخوان)
**گفتگوهای مشتری دادهٔ دائمی تیم‌اند؛ حذف ممنوع.** هیچ پاک‌سازی‌ای بدون تأیید صریحِ تازهٔ Chief + کارفرما مجاز نیست و هرگز شامل گفتگوهای واقعی مشتری نمی‌شود (فرمان کاربر 2026-10-28: «اله از الان به بعد پاک نمیکنم، پاسخ‌ها رو ثبت کن»). دود/تست به‌جای حذف برچسب می‌خورد (`"test": true`) و به Chief گزارش می‌شود. بکاپ قبل از هر تغییر: `data-tests-backup3` آخرین بکاپ کامل است.

## ری‌استارت کانتینر
```bash
# روی Alpha (کانتینر granule-chat — host network، پورت 8791)
docker restart granule-chat
# اگر فایل‌ها عوض شده‌اند، اول از مخزن کپی شود:
#   ~/dsh/projects/granule-lab-clone/chat-hub/{server.js,prompt.md,public/*} → /opt/geranol-chat/
```

## env کانتینر (در دیپلوی ست شده — با `docker inspect` ببین، چاپ نکن)
- `HOST=0.0.0.0` · `PORT=8791` · `DATA_DIR=/app/data`
- `HIVE_API_KEY` (کلید Hive — فقط env، هرگز در فایل/لاگ)
- `CHAT_PASS_HASH` (sha256 رمز کلاینت — منبع: `/opt/geranol-chat/.chat-pass-hash`)

## چک پس از ری‌استارت (همه باید سبز باشند)
| # | چک | انتظار |
|---|---|---|
| 1 | `curl -s -o /dev/null -w '%{http_code}' http://127.0.0.1:8791/` | 200 |
| 2 | `/app.css` و `/app.js` و `/fonts.css` (بدون کوچک) | 200 — استاتیک همیشه عمومی |
| 3 | `curl -s http://127.0.0.1:8791/api/session` | `{"unlocked":false,"gate":true}` |
| 4 | `POST /api/message` بدون کوچک unlock | 401 |
| 5 | unlock با رمز → کوچک `gchatpass` (HttpOnly) → پیام | 200 + پاسخ مدل (زنجیره: Hive glm → Hive dsv4 → Kilo → OR) |
| 6 | مسیر عمومی: `https://geranol-chat.nsar.ir` (TLS Traefik تا 2027-01-26) | 200، گیت استایل‌دار با وزیرمتن |
| 7 | `/admin/` بدون/با basicAuth (رمز: `/opt/geranol-chat/.admin-pass`) | 401 / 200 |
| 8 | دیتا: `ls /opt/geranol-chat/data/dialogues/ | wc -l` | همان تعدادِ قبل از ری‌استارت — **هیچ فایلی نباید کم شود** |
| 9 | `client-notes.jsonl` و `conversations.jsonl` | موجود و دست‌نخورده |

## جمع‌آوری پاسخ‌ها (قاعدهٔ ایستاده)
- زنده همیشه: `/admin/export.md` و `/admin/export.json`.
- کپی تیمی: `/workspace/granule-lab/client-conversations.md` — اسکریپت `python3 /workspace/tools/sync_client_conversations.py` (ادواری ساعتی توسط مدیر پروژه؛ پس از هر تغییر قالب export هم به‌روز شود — دستور Chief).
