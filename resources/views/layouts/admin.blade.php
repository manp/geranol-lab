<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'پنل مدیریت') — آزمایشگاه گرانول</title>
    <link rel="stylesheet" href="{{ asset('vendor/bootstrap/bootstrap.rtl.min.css') }}">
    <link rel="stylesheet" href="{{ asset('css/granollab.css') }}">
</head>
<body>
    <header class="site-header">
        <nav class="navbar navbar-expand-lg">
            <div class="container">
                <a href="{{ route('admin.users.index') }}" class="navbar-brand d-flex align-items-center gap-2">
                    <span class="brand-mark" aria-hidden="true">🧪</span>
                    <span class="brand-name">پنل مدیریت گرانول</span>
                </a>
                <div class="navbar-nav me-auto mb-2 mb-lg-0 d-lg-flex flex-row gap-3">
                    <li class="nav-item"><a href="{{ route('admin.users.index') }}" @class(['nav-link', 'active' => request()->routeIs('admin.users.*')])>کاربران</a></li>
                    <li class="nav-item"><a href="{{ route('home') }}" class="nav-link">سایت عمومی</a></li>
                </div>
                <div class="navbar-nav d-lg-flex flex-row gap-2 align-items-center">
                    <span class="muted small">{{ auth()->user()->name }} ({{ auth()->user()->role_label() }})</span>
                    <form method="POST" action="{{ route('logout') }}" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-sm btn-outline-secondary">خروج</button>
                    </form>
                </div>
            </div>
        </nav>
    </header>
    <main class="container site-main py-4">
        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @yield('content')
    </main>
    <script src="{{ asset('vendor/bootstrap/bootstrap.bundle.min.js') }}"></script>
</body>
</html>
