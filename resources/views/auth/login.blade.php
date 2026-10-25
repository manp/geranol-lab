@extends('layouts.app')

@section('title', 'ورود مشتری')

@section('content')
<section class="py-4">
    <div class="row justify-content-center">
        <div class="col-12 col-md-7 col-lg-5">
            <div class="card">
                <div class="card-body">
                    <h1 class="card-title h4 mb-1">ورود به پنل شخصی</h1>
                    <p class="muted mb-3">با شمارهٔ موبایل و رمز عبور خود وارد شوید.</p>

                    @if (session('success'))
                        <div class="alert alert-success">{{ session('success') }}</div>
                    @endif

                    <form method="POST" action="{{ route('login.submit') }}" class="row g-3">
                        @csrf
                        <div class="col-12">
                            <label for="mobile" class="form-label">شمارهٔ موبایل <span class="text-danger">*</span></label>
                            <input type="tel" class="form-control @error('mobile') is-invalid @enderror" id="mobile" name="mobile" value="{{ old('mobile') }}" placeholder="۰۹۱۲۳۴۵۶۷۸۹" dir="ltr" required>
                            @error('mobile')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-12">
                            <label for="password" class="form-label">رمز عبور <span class="text-danger">*</span></label>
                            <input type="password" class="form-control @error('password') is-invalid @enderror" id="password" name="password" required>
                            @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-12 form-check ms-2">
                            <input type="checkbox" class="form-check-input" id="remember" name="remember" value="1">
                            <label class="form-check-label" for="remember">مرا به خاطر بسپار</label>
                        </div>
                        <div class="col-12">
                            <button type="submit" class="btn btn-primary w-100">ورود</button>
                        </div>
                    </form>

                    <p class="mt-3 mb-0">حساب ندارید؟ <a href="{{ route('register') }}">ثبت‌نام</a></p>
                    <p class="muted mb-0 mt-2">بازیابی رمز عبور پس از تعیین روش اعلان (پاسخ پرسشنامهٔ مشتری) فعال می‌شود.</p>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
