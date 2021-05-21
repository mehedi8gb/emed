@extends('frontend.layouts.app')

@section('meta_title'){{ $jobs->meta_title }}@stop

@section('meta_description'){{ $jobs->meta_description }}@stop

@section('meta_keywords'){{ $jobs->meta_keywords }}@stop

@section('meta')
    <!-- Schema.org markup for Google+ -->
    <meta itemprop="name" content="{{ $jobs->meta_title }}">
    <meta itemprop="description" content="{{ $jobs->meta_description }}">
    <meta itemprop="image" content="{{ uploaded_asset($jobs->meta_img) }}">

    <!-- Twitter Card data -->
    <meta name="twitter:card" content="summary">
    <meta name="twitter:site" content="@publisher_handle">
    <meta name="twitter:title" content="{{ $jobs->meta_title }}">
    <meta name="twitter:description" content="{{ $jobs->meta_description }}">
    <meta name="twitter:creator" content="@author_handle">
    <meta name="twitter:image" content="{{ uploaded_asset($jobs->meta_img) }}">

    <!-- Open Graph data -->
    <meta property="og:title" content="{{ $jobs->meta_title }}" />
    <meta property="og:type" content="website" />
    <meta property="og:url" content="{{ route('product', $jobs->slug) }}" />
    <meta property="og:image" content="{{ uploaded_asset($jobs->meta_img) }}" />
    <meta property="og:description" content="{{ $jobs->meta_description }}" />
    <meta property="og:site_name" content="{{ env('APP_NAME') }}" />
    <style>

b {
  color: #28292c;
  font-family: sans-serif;
  opacity: 88%;
  font-size: 12px;
}
strong {
  color: arial black;
  font-family: sans-serif;
  font-size: 13px;
}
    </style>
@endsection

@section('content')

<section class="py-4">
    <div class="container">
        <div class="mb-4">

        </div>
        <div class="row">
            <div class="col-lg-6 text-center text-lg-left">
                <h1 class="fw-600 h4">{{ translate('Circuler Information')}}</h1>
            </div>

            <div style="float: right" class="col-lg-6">
                <ul class="breadcrumb bg-transparent p-0 justify-content-center justify-content-lg-end">
                    <li class="breadcrumb-item opacity-50">
                        <a class="text-reset" href="http://localhost/ecom">
                            Home
                        </a>
                    </li>
                    <li class="text-dark fw-600 breadcrumb-item">
                        <a class="text-reset" href="http://localhost/ecom/career">
                            Circulers
                        </a>
                    </li>
                    <li class="text-dark fw-600 breadcrumb-item">
                        <a class="text-reset" href="http://localhost/ecom/career/{{$jobs->slug}}">
                            "{{$jobs->job_title}}"
                        </a>
                    </li>
                </ul><hr>
            </div>


            <div class="col-md-8 mx-auto">
                <div class="bg-white rounded shadow-sm p-4">
                    <img style="float: right;  width: 15%; margin: 2px"
                    src="{{ uploaded_asset($jobs->banner) }}"

                    class="img-fluid lazyload"
                >
                    <div class="border-bottom">
                        <h1 class="h3">
                            {{ $jobs->job_title }}
                        </h1>

                        @if($jobs->category != null)
                        <div class="mb-2 opacity-50">
                            <b><i>{{ $jobs->category->category_name }}</i></b>
                        </div>
                        @endif
                    </div>
                    <div class="mb-6 overflow-hidden">
                        {!! $jobs->job_description !!}
                    </div>
                    <div>
                        <a href="mailto:{{env('ADMIN_EMAIL')}}" class="btn btn-outline-primary">{{translate('Apply Now')}}</a>
                    </div>

                    @if (get_setting('facebook_comment') == 1)
                    <div>
                        <div class="fb-comments" data-href="{{ url('career/',$jobs->slug) }}" data-width="" data-numposts="5"></div>
                    </div>
                    @endif
                </div>
            </div>
            <div class="col-md-4">
                <div class="right-wrapper bg-white rounded shadow-sm p-4">
                    <div class="m-view">
                        <div class="right job-summary">
                            <div class="">
                                <div class="panel-heading" role="heading">Job Summary</div><hr>
                                <div class="panel-body">

                                            <h6>
                                                <strong>Published on:</strong>&nbsp;

                                                <b>{{$jobs->created_at->format('D d M Y')}} </b>
                                            </h6>

                                    <!--JOB Vacancies:-->

                                        <h6>
                                            <strong>Vacancy:</strong>&nbsp;
                                           <b> {{ $jobs->vacancy }} </b>
                                        </h6>



                                 <!--   JOB Nature:  -->


                                        <h6>
                                            <strong>Employment Status:</strong>&nbsp;
                                            <b>{{$jobs->employment_status}}</b>

                                        </h6>

                                  <!--  Experience:  -->


                                        <h6>
                                            <strong>Experience:</strong>&nbsp;

                                                @if ($jobs->experience != null)
                                                <b>{{$jobs->experience}} <b>
                                                @else
                                                <b>NA</b>
                                            @endif
                                        </h6>

                                         <!--  Education  -->


                                         <h6>
                                            <strong>Education:</strong>&nbsp;

                                            @if ($jobs->education != null)
                                            <b> {{$jobs->education}}  <b>
                                                @else
                                                <b>NA</b>
                                            @endif
                                        </h6>



                                   <!--        GENDER:  -->


                    <h6>
              <strong>Gender:</strong>&nbsp;
             @if ($jobs->gender == 1)
                 <b>Only males are allowed to apply</b>
                          @endif
             @if ($jobs->gender == 2)
               <b>Only females are allowed to apply</b>
                        @endif
             @if ($jobs->gender == 3)
                  <b>Males & Females both's are allowed to apply</b>
                          @endif
              </h6>

                                    <!--
                                    AGE:
                                    -->

                                        <h6>
                                            <strong>Age:</strong>&nbsp;
                                            @if ($jobs->age != null)
                                            <b>{{$jobs->age}}</b>
                                                @else
                                                <b>NA</b>
                                            @endif

                                        </h6>

                                    <!--
                                    JOB LOCATION:
                                    -->

                                        <h6 style="line-height: 24px;">
                                            <strong>Job Location:</strong>&nbsp;

                                            @if ($jobs->location_id != null)
                                            <b> {{$jobs->location->location}}@if ($jobs->address != null)
                                                , {{$jobs->address}}</b>
                                            @endif
                                                @else
                                                <b>NA</b>
                                            @endif
                                        </h6>

                                    <!--
                                    SALARY RANGE:
                                    -->

                                        <h6>
                                            <strong>Salary:</strong>&nbsp;

                                            @if ($jobs->salary != null)
                                            <b>{{$jobs->salary}}</b>
                                                @else
                                                <b>NA</b>
                                            @endif
                                        </h6>





                                    <!--
                                    APPLICATION DEADLINE:
                                    -->
{{--
                                            <h6>
                                                <strong>Application Deadline:</strong>&nbsp;5 Jun 2021
                                            </h6> --}}
                                            <div>
                                                <a href="mailto:{{env('ADMIN_EMAIL')}}" class="btn btn-outline-primary">{{translate('Apply Now')}}</a>
                                            </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>



@endsection


@section('script')
    @if (get_setting('facebook_comment') == 1)
        <div id="fb-root"></div>
        <script async defer crossorigin="anonymous" src="https://connect.facebook.net/en_US/sdk.js#xfbml=1&version=v9.0&appId={{ env('FACEBOOK_APP_ID') }}&autoLogAppEvents=1" nonce="ji6tXwgZ"></script>
    @endif
@endsection
