# فرایند توسعه — SDLC و اسکرام

## ۱. فازهای SDLC و آرتیفکت‌های هر فاز

| فاز | خروجی/آرتیفکت | محل نگهداری | وضعیت |
|---|---|---|---|
| Planning | منشور پروژه، بک‌لاگ اولیه | `docs/sdlc/project-charter.md` · `docs/scrum/product-backlog.md` | 🔄 نهایی‌سازی پس از پرسشنامه |
| Analysis | پرسشنامهٔ مشتری، نیازمندی‌های تأییدشده | `docs/requirements/` | 🔄 |
| Design | معماری، مدل داده (ERD)، طراحی صفحات | `docs/design/` (از Sprint 1) | ⬜ |
| Implementation | کد Laravel + PRها | ریشهٔ مخزن | ⬜ |
| Testing | تست خودکار + چک‌لیست پذیرش | `tests/` + چک‌لیست در PR | ⬜ |
| Deployment | استیجینگ و تولید | `docs/ops/` (بعداً) | ⬜ |

## ۲. اسکرام روی GitHub

### نقش‌ها

| نقش | نفر | مسئولیت |
|---|---|---|
| Product Owner | مدیر آزمایشگاه (مشتری) — با واسط‌گری استاد | اولویت‌گذاری و پذیرش |
| Scrum Master | Chief (رئیس تیم) | فرایند، رفع موانع، هماهنگی |
| تیم توسعه | دستیارها و بات‌های تیم + استاد | تحویل Increment |

### رویدادها

- **Sprint:** ۲ هفته؛ هر Sprint یک Milestone در GitHub دارد.
- **Planning:** ابتدای هر Sprint؛ انتخاب آیتم‌ها از بک‌لاگ به Milestone.
- **پیگیری روزانه:** به‌روزرسانی وضعیت Issueها (برچسب/کامنت) — بدون جلسهٔ حضوری.
- **Review:** پایان Sprint؛ دمو روی استیجینگ؛ صورت‌جلسه در `docs/scrum/retros/`.
- **Retrospective:** کوتاه، ثبت درس‌آموخته‌ها در همان فایل.

### آرتیفکت‌ها

- **Product Backlog:** `docs/scrum/product-backlog.md` (منبع حقیقت؛ مرتب با اولویت MoSCoW)
- **Sprint Backlog:** Milestone همان Sprint
- **Increment:** هر چیزی که DoD را رد کند و روی استیجینگ اجرا شود

## ۳. گردش کار Issue

1. هر کار = یک Issue. انواع: `epic` (خوشهٔ کار)، `user-story`، `bug`، `docs`، `question` (نیازمند پاسخ مشتری).
2. برچسب‌ها: `sprint-*` برای اسپرینت، `P:must / P:should / P:could` برای اولویت (MoSCoW).
3. هر user-story باید شناسهٔ `GR-xxx`، معیار پذیرش و برآورد داشته باشد.
4. هر ابهام: برچسب `question` + اشاره به بخش مرتبط پرسشنامه؛ تا پاسخ مشتری نرسیده به Sprint برده نمی‌شود.
5. بستن Issue فقط با تکمیل معیارهای پذیرش و عبور از DoD.

## ۴. شاخه‌ها و کامیت‌ها

- `main`: همیشه قابل دیپلوی؛ تغییر فقط با PR (squash merge).
- شاخهٔ کاری: `feature/gr-123-short-title` یا `fix/gr-125-short-title`.
- پیام کامیت: Conventional Commits — `feat: …`، `fix: …`، `docs: …`، `chore: …` (با ارجاع `(GR-123)`).

## ۵. کیفیت

- هر PR: پاس شدن تست‌ها، رعایت فارسی/RTL در UI، بدون خطای lint، توضیح نحوهٔ تست در قالب PR.
- CI: پس از Sprint 1، اجرای خودکار تست‌ها روی هر PR فعال می‌شود.
