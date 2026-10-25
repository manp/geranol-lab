@extends('layouts.app')

@section('title', 'پنل شخصی')

@section('content')
<section class="py-4">
    <h1 class="h4">پنل شخصی</h1>
    <div class="card mt-3">
        <div class="card-body">
            <p class="mb-2">سلام، {{ auth()->user()->name }} 👋</p>
            <p class="placeholder-note mb-3">[پنل شخصی مشتری — پیگیری نمونه‌ها و نتایج آزمایش؛ تکمیل از GR-301 به بعد]</p>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="btn btn-outline-secondary">خروج از حساب</button>
            </form>
        </div>
    </div>
</section>
@endsection
