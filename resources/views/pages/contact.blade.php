@extends('layouts.app')

@section('title', 'تماس با ما')

@section('content')
<section class="py-3">
    <h1>تماس با ما</h1>

    <div class="row g-4 mt-1">
        <div class="col-12 col-md-5">
            <div class="card h-100">
                <div class="card-body">
                    <h2 class="card-title h5">اطلاعات تماس</h2>
                    <p class="placeholder-note">[محل نشانی، تلفن و ساعات کاری — پس از دریافت اطلاعات مشتری تکمیل می‌شود]</p>
                    <p class="mb-1">نشانی: <span class="muted">[به‌زودی]</span></p>
                    <p class="mb-1">تلفن: <span class="muted">[به‌زودی]</span></p>
                    <p class="mb-0">ساعات کاری: <span class="muted">[به‌زودی]</span></p>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-7">
            <div class="card h-100">
                <div class="card-body">
                    <h2 class="card-title h5">فرم تماس</h2>

                    @if (session('success'))
                        <div class="alert alert-success">{{ session('success') }}</div>
                    @endif

                    <form method="POST" action="{{ route('contact.submit') }}" class="row g-3">
                        @csrf
                        <div class="col-12 col-md-6">
                            <label for="name" class="form-label">نام و نام خانوادگی <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name') }}" required>
                            @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-12 col-md-6">
                            <label for="phone" class="form-label">شمارهٔ تماس <span class="text-danger">*</span></label>
                            <input type="tel" class="form-control @error('phone') is-invalid @enderror" id="phone" name="phone" value="{{ old('phone') }}" placeholder="۰۹۱۲۳۴۵۶۷۸۹" dir="ltr" required>
                            @error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-12">
                            <label for="email" class="form-label">ایمیل (اختیاری)</label>
                            <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email" value="{{ old('email') }}" dir="ltr">
                            @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-12">
                            <label for="message" class="form-label">متن پیام <span class="text-danger">*</span></label>
                            <textarea class="form-control @error('message') is-invalid @enderror" id="message" name="message" rows="5" required>{{ old('message') }}</textarea>
                            @error('message')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-12">
                            <button type="submit" class="btn btn-primary">ارسال پیام</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
