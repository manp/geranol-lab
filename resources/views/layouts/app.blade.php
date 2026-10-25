<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="@yield('meta_description', 'آزمایشگاه گرانول — گیاه‌پزشکی و آزمایش‌های آب، خاک و کود')">
    <title>@yield('title', 'گرانول‌لب') — آزمایشگاه گرانول</title>
    <link rel="stylesheet" href="{{ asset('vendor/bootstrap/bootstrap.rtl.min.css') }}">
    <link rel="stylesheet" href="{{ asset('css/granollab.css') }}">
</head>
<body>
    <header class="site-header">
        <nav class="navbar navbar-expand-lg">
            <div class="container">
                <a href="{{ route('home') }}" class="navbar-brand d-flex align-items-center gap-2">
                    <span class="brand-mark" aria-hidden="true">🧪</span>
                    <span class="brand-name">آزمایشگاه گرانول</span>
                </a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav" aria-controls="mainNav" aria-expanded="false" aria-label="باز و بسته کردن منو">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="mainNav">
                    <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                        <li class="nav-item"><a href="{{ route('home') }}" @class(['nav-link', 'active' => request()->routeIs('home')])>خانه</a></li>
                        <li class="nav-item"><a href="{{ route('services') }}" @class(['nav-link', 'active' => request()->routeIs('services')])>خدمات و آزمایش‌ها</a></li>
                        <li class="nav-item"><a href="{{ route('about') }}" @class(['nav-link', 'active' => request()->routeIs('about')])>دربارهٔ ما</a></li>
                        <li class="nav-item"><a href="{{ route('contact') }}" @class(['nav-link', 'active' => request()->routeIs('contact')])>تماس با ما</a></li>
@guest
                        <li class="nav-item"><a href="{{ route('login') }}" @class(['nav-link', 'active' => request()->routeIs('login') || request()->routeIs('register')])>ورود / ثبت‌نام</a></li>
@else
                        <li class="nav-item"><a href="{{ route('dashboard.placeholder') }}" @class(['nav-link', 'active' => request()->routeIs('dashboard.placeholder')])>پنل شخصی</a></li>
@if (in_array(auth()->user()->role, ['admin', 'employee']))
                        <li class="nav-item"><a href="{{ route('admin.users.index') }}" class="nav-link">پنل مدیریت</a></li>
@endif
@endguest
                    </ul>
                </div>
            </div>
        </nav>
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
    <script src="{{ asset('vendor/bootstrap/bootstrap.bundle.min.js') }}"></script>
</body>
</html>
