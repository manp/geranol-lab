@extends('layouts.app')

@section('title', 'خدمات و آزمایش‌ها')

@section('content')
<section class="py-3">
    <h1>خدمات و آزمایش‌ها</h1>
    <p class="muted">فهرست آزمایش‌های آزمایشگاه گرانول — برای جزئیات، نمونهٔ لازم و مدت تحویل، هر خدمت را باز کنید.</p>

    <div class="row g-3 mt-1">
        @forelse ($services as $service)
            <div class="col-12 col-md-6 col-lg-4">
                <div class="card h-100">
                    <div class="card-body d-flex flex-column">
                        <h2 class="card-title h5">{{ $service->name }}</h2>
                        <p class="card-text">{{ $service->description_short }}</p>
                        <div class="mt-auto d-flex justify-content-between align-items-center">
                            @if ($service->price)
                                <span class="fw-bold">{{ number_format($service->price) }} تومان</span>
                            @else
                                <span class="badge badge-ask">تماس بگیرید</span>
                            @endif
                            <a class="btn btn-outline-primary btn-sm" href="{{ route('services.show', $service->slug) }}">جزئیات</a>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <p class="placeholder-note">فهرست خدمات به‌زودی تکمیل می‌شود.</p>
        @endforelse
    </div>
</section>
@endsection
