# geranol-chat — چت‌هاب کشف نیازمندی مشتری (پروژهٔ گرانول)

اپ کوچک گفتگویی برای شناخت نیازمندی‌های مشتری آزمایشگاه گرانول، به‌جای فرم سرد.
**کاملاً مستقل از تیم DS Bot است** — حافظهٔ جدا، پرامپت جدا، دیتای جدا.

## معماری
- `server.js` — بک‌اند Node بدون وابستگی: سرو استاتیک (همیشه عمومی — صفحهٔ گیت استایل‌دار رندر می‌شود) + `GET /api/session` (وضعیت گیت) + `POST /api/unlock` (گیت) + `POST /api/message` (پشت گیت) + `POST /api/feedback` (پشت گیت) + `/health`.
- `public/` — فرانت فارسی RTL موبایل‌محور (بدون build).
- `prompt.md` — پرامپت سیستم: **فقط** اسکریپت کشف (۶ فاز، هم‌راستا با پرسشنامهٔ ۱۹سؤالی). هیچ متنیم تیمی داخلش نیست — سخت‌ترین الزام ایزولاسیون.
- `data/` — گفتگوها (JSONL در runtime).

## ایزولاسیون (الزام اصلی کارفرما)
- هر بازدیدکننده session کوکی مستقل (`gchat`, HttpOnly) → گفتگوی هر مشتری جدا از بقیه.
- به مدل فقط: پرامپت سیستم + نوبت‌های همان گفتگو. هیچ چیز دیگر هرگز به upstream نمی‌رود.
- کلید مدل فقط سمت سرور (env `MODEL_KEY`)؛ بدون لاگ.
- هیچ مسیری از این اپ به دیتای تیم/بات‌ها وجود ندارد.

## مدل (قاعدهٔ zero-cost)
- پیش‌فرض: Hive `zai-org/glm-5.3-flash` — فال‌بک طبق رجیستری: OR `z-ai/glm-5.3-flash` → Kilo `nvidia/nemotron-3-ultra-550b-a55b:free` (بی‌کلید).
- تعویض با env: `MODEL_BASE` / `MODEL_ID` / `MODEL_KEY`.

## اجرا روی Alpha (دستور مرجع)
```bash
# دیپلوی: /opt/geranol-chat + کانتینر node:22-alpine روی 127.0.0.1:8791
# ترافیک: Traefik (dokploy) — /etc/dokploy/traefik/dynamic/geranol-chat-nsar-ir.yml
# دامنه: geranol-chat.nsar.ir (wildcard DNS موجود؛ TLS با letsencrypt resolver)
```

## مسیر تیم (خواندن پاسخ‌ها)
- خروجی خام: `data/dialogues/*.json` + `data/conversations.jsonl` + `data/client-notes.jsonl`.
- اسکریپت `export.py` (در همین پوشه): می‌سازد `client-conversations.md` (ورودی منشور/بک‌لاگ) — بعد از هر تغییر قالب export، نسخهٔ تازه در `/workspace/granule-lab/` هم گذاشته می‌شود (دستور Chief).

## نکتهٔ امنیتی
- هیچ پیامی از طرف تیم به مشتری نمی‌رود؛ لینک+رمز را **کاربر** خودش به مشتری می‌دهد.
- گیت کلاینت: استاتیک عمومی، APIهای گفتگو پشت گیت (فرمان کاربر: «حتماً رمز داشته باشد»).
- پنل ادمین (خروجی/بازبینی) با basicAuth در Traefik محافظت می‌شود.

## بهره‌برداری (پس از دیپلوی 2026-10-28؛ اصلاح گیت همان روز بعدازظهر)
- آدرس مشتری: **https://geranol-chat.nsar.ir** — صفحه با **رمز دسترسی محافظت می‌شود** (فرمان کاربر: «حتماً رمز داشته باشد»). **معماری گیت (اصلاح‌شده):** استاتیک صفحه — از جمله خود صفحهٔ گیت — همیشه بدون رمز ۲۰۰ است (ریشهٔ باگ CSS قبلی: ۴۰۱ شدن assetها برای بازدیدکنندهٔ تازه)؛ فقط `/api/message` و `/api/feedback` تا unlock قفل‌اند. Probe وضعیت: `GET /api/session` → `{gate, unlocked}`. هش در `/opt/geranol-chat/.chat-pass-hash`، مقدار خام در `/opt/geranol-chat/.chat-pass` — لینک+رمز را کاربر خودش به مشتری می‌دهد.
- پنل بازبینی: `https://geranol-chat.nsar.ir/admin/` (basicAuth؛ رمز روی Alpha: `/opt/geranol-chat/.admin-pass`) — فهرست گفتگوها + خروجی `export.md` / `export.json`.
- خروجی مارک‌داون پس از هر تغییر قالب در `/workspace/granule-lab/client-conversations.md` هم گذاشته می‌شود (دستور Chief).
- کانتینر: `granule-chat` (node:22-alpine، network host، mount `/opt/geranol-chat`)؛ ترافیک: Traefik `geranol-chat-nsar-ir.yml` (TLS letsencrypt + basicAuth فقط مسیر /admin).
- زنجیرهٔ مدل (از 2026-10-28، فرمان کاربر): ① Hive glm-5.3-flash ② Hive deepseek-v4.1-flash (کلید از env کانتینر HIVE_API_KEY — از .env اوپن‌بات همان سرور در deploy تزریق می‌شود؛ در هیچ فایلی کپی نشده) ③ Kilo بی‌کلید ×۲ ④ OR glm (اگر کلید env باشد). لایهٔ برنده در conversations.jsonl (servedBy) ثبت می‌شود.
- دیتای گفتگو: `/opt/geranol-chat/data/` (dialogues/*.json + conversations.jsonl + client-notes.jsonl) — بکاپ قبل از هر تغییر.
