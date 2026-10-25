@extends('layouts.app')

@section('title', 'خانه')

@section('content')
<section class="hero">
    <h1>آزمایشگاه گرانول</h1>
    <p class="lead">آزمایشگاه گیاه‌پزشکی و آزمایش‌های آب، خاک و کود</p>
    <p class="placeholder-note">[محل معرفی کوتاه آزمایشگاه — پس از دریافت اطلاعات مشتری تکمیل می‌شود]</p>
    <a class="btn btn-primary btn-lg mt-3" href="{{ route('services') }}">مشاهدهٔ خدمات و آزمایش‌ها</a>
</section>
<section class="row g-3 pb-4">
    <div class="col-12 col-md-6 col-lg-3">
        <div class="card h-100"><div class="card-body"><h2 class="card-title h5">گیاه‌پزشکی</h2><p class="card-text">تشخیص آفات و بیماری‌های گیاهی و بررسی باقیماندهٔ سموم</p></div></div>
    </div>
    <div class="col-12 col-md-6 col-lg-3">
        <div class="card h-100"><div class="card-body"><h2 class="card-title h5">آنالیز خاک</h2><p class="card-text">تجزیهٔ خاک و ارائهٔ توصیهٔ کودی</p></div></div>
    </div>
    <div class="col-12 col-md-6 col-lg-3">
        <div class="card h-100"><div class="card-body"><h2 class="card-title h5">آنالیز آب</h2><p class="card-text">آزمایش آب آبیاری</p></div></div>
    </div>
    <div class="col-12 col-md-6 col-lg-3">
        <div class="card h-100"><div class="card-body"><h2 class="card-title h5">آنالیز کود</h2><p class="card-text">تجزیهٔ کودهای شیمیایی و آلی</p></div></div>
    </div>
</section>
@endsection
