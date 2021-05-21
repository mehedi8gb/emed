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
    {{-- <form class="" id="sort_blogs" action="" method="GET">
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
        </from> --}}
        <div class="card-header d-block d-md-flex">
            <h5 class="mb-0 h6">{{ translate('Jobs') }}</h5>
            <form class="" id="search_jobs" action="" method="GET">
                <div class="box-inline pad-rgt pull-left">
                    <div class="" style="min-width: 200px;">
                        <input type="text" class="form-control" id="search" name="search"@isset($sort_search) value="{{ $sort_search }}" @endisset placeholder="{{ translate('Type name & Enter') }}">
                    </div>
                </div>
            </form>
        </div>
        <div class="card-body">
            <table class="table mb-0 aiz-table">
                <thead>
                    <tr>
                        <th style="width: 1%">#</th>
                        <th style="width: 7%">{{translate('Author')}}</th>
                        <th style="width: 7%">{{translate('Title')}}</th>
                        <th style="width: 9%" data-breakpoints="lg">{{translate('Category')}}</th>
                        <th style="width: 35%" data-breakpoints="lg">{{translate('Description')}}</th>
                        <th style="width: 7%" data-breakpoints="lg">{{translate('Slug')}}</th>
                        <th style="width: 3%" data-breakpoints="lg">{{translate('Status')}}</th>
                        <th style="width: 10%" data-breakpoints="lg">{{translate('Create')}}</th>
                        <th style="width: 10%" data-breakpoints="lg">{{translate('Update')}}</th>
                        <th style="width: 5%"class="text-right">{{translate('Options')}}</th>

                    </tr>
                </thead>
                <tbody>

                    @foreach($jobs as $job)
                    <tr>
                        <td>
                            {{ $jobs->firstItem()+$loop->index }}
                        </td>
                        <td>
                                @if($job->user_id != null)
                                {{ $job->jobuser->name }}
                            @else
                                bad data
                            @endif
                        </td>
                        <td>
                            {{ $job->job_title }}
                        </td>
                        <td>
                            @if($job->category != null)
                                {{ $job->category->category_name }}
                            @else
                                --
                            @endif
                        </td>
                        <td>
                            {{ $job->short_description }}
                        </td>
                        <td>
                            <label class="aiz-switch aiz-switch-success mb-0">
                                <a target="_blank" href="{{env('APP_URL').('/career/').$job->slug }}">{{ $job->slug }}</a>
                                <span></span>
                            </label>

                        </td>
                        <td>
                            <label class="aiz-switch aiz-switch-success mb-0">
                                <input  type="checkbox" onchange="change_status(this)" value="{{ $job->id }}" <?php if($job->status == 1) echo "checked";?>>
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

                            </label>
                        </td>
                        <td>
                            <label class="aiz-switch aiz-switch-success mb-0">
                                @if ($job->updated_at == null)
                                    <span class="text-danger">Time Not Set</span>
                                    @else
                                    {{ $job->updated_at->diffForHumans() }}
                                @endif

                            </label>
                        </td>
                        <td class="text-right">
                            <a data-html="true" data-animation="true" data-toggle="tooltip" data-placement="top" title="<b>Edit Data</b>" class="btn btn-soft-primary btn-icon btn-circle btn-sm" href="{{ url('/admin/job/edit',$job->id)}}" title="{{ translate('Edit') }}">
                                <i class="las la-pen"></i>
                            </a>
                            <a data-html="true" data-animation="true" data-toggle="tooltip" data-placement="bottom" title="<b>Delate Data</b>" class="btn btn-soft-danger btn-icon btn-circle btn-sm confirm-delete" data-href="{{route('job.destroy', $job->id)}}" title="{{ translate('Delete') }}">
                                <i class="las la-trash"></i>
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            <div class="aiz-pagination">
                {{ $jobs->links() }}
            </div>
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
        $.post('{{ route('job.change-status') }}', {_token:'{{ csrf_token() }}', id:el.value, status:status}, function(data){
            if(data == 1){
                AIZ.plugins.notify('success', '{{ translate('Circuler status Changed  successfully') }}');
            }
            else{
                AIZ.plugins.notify('danger', '{{ translate('Something went wrong') }}');
            }
        });
    }
</script>

@endsection
