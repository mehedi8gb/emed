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
                            "{{ translate('Circulers') }}"
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</section>

<section class="pb-4">
    <div class="container">
        <div class="card-row ">
            @foreach($jobs as $job)
                <div class="card w-100 overflow-hidden shadow-sm">
                    <div class="p-4">
                    <a href="{{ url("career").'/'. $job->slug }}" class="text-reset d-block">
                        <img style="float: right;  width: 10%; margin: 12px"
                            src="{{ static_asset('assets/') }}"
                            data-src="{{ uploaded_asset($job->banner) }}"
                            alt="{{ $job->title }}"
                            class="img-fluid lazyload "
                        >
                    </a>

                        @if($job->category != null)
                        <div class="mb-2 opacity-70">
                            <span>{{ $job->category->category_name }}</span>
                        </div>
                        @endif
                        <h2 class="fs-18 fw-600 mb-2">
                            <a href="{{ url("career").'/'. $job->slug }}" class="text-reset">
                                {{ $job->job_title }} <hr>
                            </a>
                        </h2>
                        <div class="mb-2 opacity-72">
                            <span>{{ $job->company }}</span>
                        </div>
                        <div class="mb-1 opacity-71 ">

                            <span> <i class="las la-map-marker"></i>
                                @if ($job->location_id != null)
                                {{$job->location->location}}
                                    @else
                                    NA
                                @endif</span>
                        </div>
                        <div class="mb-1 opacity-72">
                            <i class="las la-university"></i>
                            <span>{{ $job->education }}</span>
                        </div>
                        <div>
                        <p class="opacity-72 mb-2">
                            <i class="las la-briefcase"></i>
                            {{ $job->experience }}
                        </p>
                        </div>
                        <div>
                            <p class="opacity-95 mb-4">
                               <b> {{ $job->short_description }} </b>
                            </p>
                        </div>
                        <a data-html="true" data-animation="true" data-toggle="tooltip" data-placement="top" title="<i>Read Everything carefully then Mail us your application and cv</i>"
                        href="{{ url("career").'/'. $job->slug }}" class="btn btn-soft-primary">
                            {{ translate('View Circuler') }}
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
