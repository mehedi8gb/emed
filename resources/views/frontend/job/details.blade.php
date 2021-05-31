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

span.job {
  color: #1d1e20;
  font-family: sans-serif;
  opacity: 92%;
  font-size: 12px;
}
label.jobs {
  color: arial black;
  font-family: sans-serif;
  font-size: 14px;
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
                        <a href="mailto:{{$jobs->jobuser->email}}?subject=I would like to Apply as {{$jobs->job_title}}!&amp;body=Write Your Application , Attach a CV for more attractive application and sent it to the Author ``{{ $jobs->user_id == $jobs->jobuser->id ? $jobs->jobuser->name : '' }}``" class="btn btn-outline-primary">{{translate('Apply Now')}}</a>
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
                                                <label class="jobs">Published on:</label >&nbsp;

                                                <span class="job">{{$jobs->created_at->format('D d M Y')}} </span>
                                            </h6>

                                    <!--JOB Vacancies:-->

                                        <h6>
                                            <label class="jobs">Vacancy:</label> &nbsp;
                                           <span class="job"> {{ $jobs->vacancy }} </span>
                                        </h6>



                                 <!--   JOB Nature:  -->


                                        <h6>
                                            <label class="jobs">Employment Status:</label> &nbsp;
                                            <span class="job">{{$jobs->employment_status}}</span>

                                        </h6>

                                  <!--  Experience:  -->


                                        <h6>
                                            <label class="jobs">Experience:</label> &nbsp;

                                                @if ($jobs->experience != null)
                                                <span class="job">{{$jobs->experience}} </span>
                                                @else
                                                <span class="job">NA</span>
                                            @endif
                                        </h6>

                                         <!--  Education  -->


                                         <h6>
                                            <label class="jobs">Education:</label> &nbsp;

                                            @if ($jobs->education != null)
                                            <span class="job"> {{$jobs->education}}  </span>
                                                @else
                                                <span class="job">NA</span>
                                            @endif
                                        </h6>



                                   <!--        GENDER:  -->


                    <h6>
              <label class="jobs">Gender:</label> &nbsp;
             @if ($jobs->gender == 1)
                 <span class="job">Only males are allowed to apply</span>
                          @endif
             @if ($jobs->gender == 2)
               <span class="job">Only females are allowed to apply</span>
                        @endif
             @if ($jobs->gender == 3)
                  <span class="job">Males & Females both's are allowed to apply</span>
                          @endif
              </h6>

                                    <!--
                                    AGE:
                                    -->

                                        <h6>
                                            <label class="jobs">Age:</label>&nbsp;
                                            @if ($jobs->age != null)
                                            <span class="job">{{$jobs->age}}</span>
                                                @else
                                                <span class="job">NA</span>
                                            @endif

                                        </h6>

                                    <!--
                                    JOB LOCATION:
                                    -->

                                        <h6 style="line-height: 24px;">
                                            <label class="jobs">Job Location:</label>&nbsp;

                                            @if ($jobs->location_id != null)
                                            <span class="job"> {{$jobs->location->location}}@if ($jobs->address != null)
                                                , {{$jobs->address}}</span>
                                            @endif
                                                @else
                                                <span class="job">NA</span>
                                            @endif
                                        </h6>

                                    <!--
                                    SALARY RANGE:
                                    -->

                                        <h6>
                                            <label class="jobs">Salary:</label>&nbsp;

                                            @if ($jobs->salary != null)
                                            <span class="job">{{$jobs->salary}}</span>
                                                @else
                                                <span class="job">NA</span>
                                            @endif
                                        </h6>





                                    <!--
                                    APPLICATION DEADLINE:
                                    -->
{{--
                                            <h6>
                                                <label class="job">Application Deadline:</label class="job">&nbsp;5 Jun 2021
                                            </h6> --}}
                                            <div>
                                                <a href="mailto:{{$jobs->jobuser->email}}?subject=I would like to Apply as {{$jobs->job_title}}!&amp;body=Write Your Application , Attach a CV for more attractive application and sent it to the Author ``{{$jobs->jobuser->name}}``" class="btn btn-outline-primary">{{translate('Apply Now')}}</a>
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
