@extends('frontend.layouts.app')

@section('content')

<section class="pt-4 mb-4">
    <div class="container text-center">
        <div class="row">
            <div class="col-lg-6 text-center text-lg-left">
                <h1 class="fw-600 h4">{{ translate('job')}}</h1>
            </div>
            <div class="col-lg-6">
                <ul class="breadcrumb bg-transparent p-0 justify-content-center justify-content-lg-end">
                    <li class="breadcrumb-item opacity-50">
                        <a class="text-reset" href="{{ route('home') }}">
                            {{ translate('Home')}}
                        </a>
                    </li>
                    <li class="text-dark fw-600 breadcrumb-item">
                        <a class="text-reset" href="{{ route('front.job') }}">
                            "{{ translate('job') }}"
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</section>

<section class="pb-4">
    <div class="container">
        <div class="card-columns">
            @foreach($jobs as $job)
                <div class="card mb-3 overflow-hidden shadow-sm">
                    <a href="{{ url("job").'/'. $job->slug }}" class="text-reset d-block">
                        <img
                            src="{{ static_asset('assets/img/placeholder-rect.jpg') }}"
                            data-src="{{ uploaded_asset($job->banner) }}"
                            alt="{{ $job->title }}"
                            class="img-fluid lazyload "
                        >
                    </a>
                    <div class="p-4">
                        <h2 class="fs-18 fw-600 mb-1">
                            <a href="{{ url("career").'/'. $job->slug }}" class="text-reset">
                                {{ $job->title }}
                            </a>
                        </h2>
                        @if($job->category != null)
                        <div class="mb-2 opacity-50">
                            <i>{{ $job->category->category_name }}</i>
                        </div>
                        @endif
                        <p class="opacity-70 mb-4">
                            {{ $job->short_description }}
                        </p>
                        <a href="{{ url("career").'/'. $job->slug }}" class="btn btn-soft-primary">
                            {{ translate('View More') }}
                        </a>
                    </div>
                </div>
            @endforeach

        </div>
        <div class="aiz-pagination aiz-pagination-center mt-4">
            {{ $jobs->links() }}
        </div>
    </div>
</section>
@endsection
