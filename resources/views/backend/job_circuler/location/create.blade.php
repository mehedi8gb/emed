@extends('backend.layouts.app')

@section('content')

<div class="row">
    <div class="col-lg-8 mx-auto">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0 h6">{{translate('Job Location Information')}}</h5>
            </div>
            <div class="card-body">
                <form class="form-horizontal" method="POST" action="{{ route('job.location.data.store') }}">
                	@csrf
                    <div class="form-group row">
                        <label class="col-md-3 col-form-label">{{translate('Locations')}}</label>
                        <div class="col-md-9">
                            <input type="text" placeholder="{{translate('enter location')}}" id="location" name="location" class="form-control">
                            @error('location')
                     <div class="alert alert-danger">{{ $message }}</div>
                                @enderror
                        </div>
                    </div>

                    <div class="form-group mb-0 text-right">
                        <button type="submit" class="btn btn-primary">
                            {{translate('Save')}}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
