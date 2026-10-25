@extends('layouts.admin')

@section('title', 'کارمند جدید')

@section('content')
<h1 class="h4 mb-3">ساخت کارمند / مدیر جدید</h1>

<div class="card">
    <div class="card-body">
        <form method="POST" action="{{ route('admin.users.store') }}" class="row g-3">
            @csrf
            <div class="col-12 col-md-6">
                <label for="name" class="form-label">نام و نام خانوادگی <span class="text-danger">*</span></label>
                <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name') }}" required>
                @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-12 col-md-6">
                <label for="mobile" class="form-label">شمارهٔ موبایل <span class="text-danger">*</span></label>
                <input type="tel" class="form-control @error('mobile') is-invalid @enderror" id="mobile" name="mobile" value="{{ old('mobile') }}" placeholder="۰۹۱۲۳۴۵۶۷۸۹" dir="ltr" required>
                @error('mobile')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-12 col-md-6">
                <label for="password" class="form-label">رمز اولیه <span class="text-danger">*</span></label>
                <input type="password" class="form-control @error('password') is-invalid @enderror" id="password" name="password" required>
                <div class="form-text">حداقل ۸ کاراکتر؛ کاربر پس از نخستین ورود باید آن را تغییر دهد (Sprint بعدی).</div>
                @error('password')<div class="form-text text-warning">رمز هش‌شده ذخیره می‌شود.</div>@enderror
            </div>
            <div class="col-12 col-md-6">
                <label for="role" class="form-label">نقش <span class="text-danger">*</span></label>
                <select class="form-select @error('role') is-invalid @enderror" id="role" name="role" required>
                    <option value="employee">کارمند</option>
                    <option value="admin">مدیر</option>
                </select>
                @error('role')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-12">
                <button type="submit" class="btn btn-primary">ساخت حساب</button>
                <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary">انصراف</a>
            </div>
        </form>
    </div>
</div>
@endsection
