@extends('layouts.app')

@section('title', $service->name)

@section('meta_description', $service->description_short)

@section('content')
<section class="py-3">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('home') }}">خانه</a></li>
            <li class="breadcrumb-item"><a href="{{ route('services') }}">خدمات و آزمایش‌ها</a></li>
            <li class="breadcrumb-item active" aria-current="page">{{ $service->name }}</li>
        </ol>
    </nav>

    <h1>{{ $service->name }}</h1>
    <p class="lead">{{ $service->description_short }}</p>

    <div class="row g-4 mt-1">
        <div class="col-12 col-md-8">
            <div class="card h-100">
                <div class="card-body">
                    <h2 class="card-title h5">شرح آزمایش</h2>
                    <p class="mb-0">{{ $service->description_full ?: $service->description_short }}</p>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-4">
            <div class="card h-100">
                <div class="card-body">
                    <h2 class="card-title h5">اطلاعات نمونه و زمان</h2>
                    <p class="mb-2"><strong>نمونهٔ لازم:</strong> {{ $service->sample_type ?: 'با آزمایشگاه تماس بگیرید' }}</p>
                    <p class="mb-2"><strong>مدت تحویل:</strong> {{ $service->turnaround_text ?: 'تماس بگیرید' }}</p>
                    <p class="mb-0">
                        <strong>هزینه:</strong>
                        @if ($service->price)
                            {{ number_format($service->price) }} تومان
                        @else
                            <span class="badge badge-ask">تماس بگیرید</span>
                        @endif
                    </p>
                </div>
            </div>
        </div>
    </div>

    <div class="mt-4">
        <a class="btn btn-primary" href="{{ route('contact') }}">برای این آزمایش تماس بگیرید</a>
    </div>
</section>
@endsection
