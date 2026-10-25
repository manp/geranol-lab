<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="@yield('meta_description', 'آزمایشگاه گرانول — گیاه‌پزشکی و آزمایش‌های آب، خاک و کود')">
    <title>@yield('title', 'گرانول‌لب') — آزمایشگاه گرانول</title>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
</head>
<body>
    <header class="site-header">
        <div class="container header-inner">
            <a href="{{ route('home') }}" class="brand">
                <span class="brand-mark" aria-hidden="true">🧪</span>
                <span class="brand-name">آزمایشگاه گرانول</span>
            </a>
            <nav class="main-nav" aria-label="منوی اصلی">
                <a href="{{ route('home') }}" @class(['active' => request()->routeIs('home')])>خانه</a>
                <a href="{{ route('services') }}" @class(['active' => request()->routeIs('services')])>خدمات و آزمایش‌ها</a>
                <a href="{{ route('about') }}" @class(['active' => request()->routeIs('about')])>دربارهٔ ما</a>
                <a href="{{ route('contact') }}" @class(['active' => request()->routeIs('contact')])>تماس با ما</a>
            </nav>
        </div>
    </header>
    <main class="container site-main">
        @yield('content')
    </main>
    <footer class="site-footer">
        <div class="container">
            <p>آزمایشگاه گرانول — گیاه‌پزشکی و آزمایش‌های آب، خاک و کود</p>
            <p class="muted">© {{ date('Y') }} همهٔ حقوق محفوظ است · [محل اطلاعات تماس — پس از دریافت اطلاعات مشتری تکمیل می‌شود]</p>
        </div>
    </footer>
</body>
</html>
