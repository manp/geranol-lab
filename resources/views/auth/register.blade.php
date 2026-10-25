@extends('layouts.app')

@section('title', 'ثبت‌نام مشتری')

@section('content')
<section class="py-4">
    <div class="row justify-content-center">
        <div class="col-12 col-md-7 col-lg-5">
            <div class="card">
                <div class="card-body">
                    <h1 class="card-title h4 mb-1">ساخت حساب مشتری</h1>
                    <p class="muted mb-3">با شمارهٔ موبایل و رمز عبور ثبت‌نام کنید تا در پنل شخصی، نمونه‌ها و نتایج آزمایش‌هایتان را پیگیری کنید.</p>

                    @if (session('success'))
                        <div class="alert alert-success">{{ session('success') }}</div>
                    @endif

                    <form method="POST" action="{{ route('register.submit') }}" class="row g-3">
                        @csrf
                        <div class="col-12">
                            <label for="name" class="form-label">نام و نام خانوادگی <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name') }}" required>
                            @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-12">
                            <label for="mobile" class="form-label">شمارهٔ موبایل <span class="text-danger">*</span></label>
                            <input type="tel" class="form-control @error('mobile') is-invalid @enderror" id="mobile" name="mobile" value="{{ old('mobile') }}" placeholder="۰۹۱۲۳۴۵۶۷۸۹" dir="ltr" required>
                            @error('mobile')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-12">
                            <label for="password" class="form-label">رمز عبور <span class="text-danger">*</span></label>
                            <input type="password" class="form-control @error('password') is-invalid @enderror" id="password" name="password" required>
                            <div class="form-text">حداقل ۸ کاراکتر.</div>
                            @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-12">
                            <button type="submit" class="btn btn-primary w-100">ثبت‌نام</button>
                        </div>
                    </form>

                    <p class="mt-3 mb-0">حساب دارید؟ <a href="{{ route('login') }}">ورود به حساب</a></p>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
