@extends('layouts.app')

@section('title', 'خانه')

@section('content')
<section class="hero">
    <h1>آزمایشگاه گرانول</h1>
    <p class="lead">آزمایشگاه گیاه‌پزشکی و آزمایش‌های آب، خاک و کود</p>
    <p class="muted">[محل معرفی کوتاه آزمایشگاه — پس از دریافت اطلاعات مشتری تکمیل می‌شود]</p>
    <a class="btn" href="{{ route('services') }}">مشاهدهٔ خدمات و آزمایش‌ها</a>
</section>
<section class="cards">
    <div class="card"><h2>گیاه‌پزشکی</h2><p>تشخیص آفات و بیماری‌های گیاهی و بررسی باقیماندهٔ سموم</p></div>
    <div class="card"><h2>آنالیز خاک</h2><p>تجزیهٔ خاک و ارائهٔ توصیهٔ کودی</p></div>
    <div class="card"><h2>آنالیز آب</h2><p>آزمایش آب آبیاری</p></div>
    <div class="card"><h2>آنالیز کود</h2><p>تجزیهٔ کودهای شیمیایی و آلی</p></div>
</section>
@endsection
