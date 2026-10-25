@extends('layouts.admin')

@section('title', 'ویرایش کاربر')

@section('content')
<h1 class="h4 mb-3">ویرایش کاربر: {{ $user->name }}</h1>

<div class="card">
    <div class="card-body">
        <form method="POST" action="{{ route('admin.users.update', $user) }}" class="row g-3">
            @csrf
            @method('PUT')
            <div class="col-12 col-md-6">
                <label class="form-label">موبایل</label>
                <input type="text" class="form-control" value="{{ $user->mobile }}" dir="ltr" disabled>
            </div>
            <div class="col-12 col-md-6">
                <label class="form-label">تاریخ ثبت‌نام</label>
                <input type="text" class="form-control" value="{{ $user->created_at->format('Y-m-d') }}" disabled>
                <div class="form-text">نقش «مشتری» فقط از ثبت‌نام عمومی می‌آید؛ تبدیل مشتری به کارمند اینجا انجام می‌شود.</div>
            </div>
            <div class="col-12 col-md-6">
                <label for="role" class="form-label">نقش <span class="text-danger">*</span></label>
                <select class="form-select @error('role') is-invalid @enderror" id="role" name="role" required>
                    @foreach (['admin' => 'مدیر', 'employee' => 'کارمند', 'customer' => 'مشتری'] as $value => $label)
                        <option value="{{ $value }}" @selected($user->role === $value)>{{ $label }}</option>
                    @endforeach
                </select>
                @error('role')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-12 col-md-6">
                <label class="form-label">وضعیت</label>
                <input type="hidden" name="is_active" value="0">
                <div class="form-check form-switch mt-2">
                    <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1" @checked($user->is_active)>
                    <label class="form-check-label" for="is_active">حساب فعال باشد</label>
                </div>
            </div>
            <div class="col-12">
                <button type="submit" class="btn btn-primary">ذخیره</button>
                <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary">بازگشت</a>
            </div>
        </form>
    </div>
</div>
@endsection
