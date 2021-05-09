@extends('backend.layouts.app')

@section('content')

<div class="aiz-titlebar text-left mt-2 mb-3">
    <div class="row align-items-center">
        <div class="col-auto">
            <h1 class="h3">{{translate('All Circulers')}}</h1>
        </div>
        <div class="col text-right">
            <a href="{{ route('job.store') }}" class="btn btn-circle btn-info">
                <span>{{translate('Add Job Circuler')}}</span>
            </a>
        </div>
    </div>
</div>
<br>

<div class="card">
    <form class="" id="sort_blogs" action="" method="GET">
        <div class="card-header row gutters-5">
            <div class="col text-center text-md-left">
                <h5 class="mb-md-0 h6">{{ translate('All Circulers') }}</h5>
            </div>

            <div class="col-md-2">
                <div class="form-group mb-0">
                    <input type="text" class="form-control form-control-sm" id="search" name="search"@isset($sort_search) value="{{ $sort_search }}" @endisset placeholder="{{ translate('Type & Enter') }}">
                </div>
            </div>
        </div>
        </from>
        <div class="card-body">
            <table class="table mb-0 aiz-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>{{translate('Job Title')}}</th>
                        {{-- <th data-breakpoints="lg">{{translate('Job Category')}}</th> --}}
                        <th data-breakpoints="lg">{{translate('Short Description')}}</th>
                        <th data-breakpoints="lg">{{translate('Slug')}}</th>
                        <th data-breakpoints="lg">{{translate('Status')}}</th>
                        <th data-breakpoints="lg">{{translate('Created at')}}</th>
                        <th class="text-right">{{translate('Options')}}</th>

                    </tr>
                </thead>
                <tbody>
                    @php($key=1)
                    @foreach($jobs as $job)
                    <tr>
                        <td>
                            {{ ($key++) }}
                        </td>
                        <td>
                            {{ $job->job_title }}
                        </td>
                        {{-- <td>
                            @if($job->category != null)
                                {{ $job->category->category_name }}
                            @else
                                --
                            @endif
                        </td> --}}
                        <td>
                            {{ $job->short_description }}
                        </td>
                        <td>
                            <label class="aiz-switch aiz-switch-success mb-0">
                                <a href="{{ $job->slug }}">{{ $job->slug }}</a>
                                <span></span>
                            </label>

                        </td>
                        <td>
                            <label class="aiz-switch aiz-switch-success mb-0">
                                <input type="checkbox" onchange="change_status(this)" value="{{ $job->status }}">
                                <span></span>
                            </label>
                        </td>
                        <td>
                            <label class="aiz-switch aiz-switch-success mb-0">
                                @if ($job->created_at == null)
                                    <span class="text-danger">Time Not Set</span>
                                    @else
                                    {{ $job->created_at->diffForHumans() }}
                                @endif
                                <span>
                                {{ $job->created_at }}
                                </span>
                            </label>
                        </td>
                        <td class="text-right">
                            {{-- <a class="btn btn-soft-primary btn-icon btn-circle btn-sm" href="{{ route('job.edit',$jobs->id)}}" title="{{ translate('Edit') }}">
                                <i class="las la-pen"></i>
                            </a> --}}

                            {{-- <a href="#" class="btn btn-soft-danger btn-icon btn-circle btn-sm confirm-delete" data-href="{{route('job.destroy', $jobs->id)}}" title="{{ translate('Delete') }}">
                                <i class="las la-trash"></i>
                            </a> --}}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            {{-- <div class="aiz-pagination">
                {{ $blogs->links() }}
            </div> --}}
        </div>
</div>

@endsection

@section('modal')
    @include('modals.delete_modal')
@endsection


@section('script')

    <script type="text/javascript">
        function change_status(el){
            var status = 0;
            if(el.checked){
                var status = 1;
            }
            $.post('{{ route('blog.change-status') }}', {_token:'{{ csrf_token() }}', id:el.value, status:status}, function(data){
                if(data == 1){
                    AIZ.plugins.notify('success', '{{ translate('Change blog status successfully') }}');
                }
                else{
                    AIZ.plugins.notify('danger', '{{ translate('Something went wrong') }}');
                }
            });
        }
    </script>

@endsection
