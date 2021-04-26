@extends('layouts.app')

@section('content')
<div class="row">
    <div class="col-sm-12">
        <a href="{{ route('seller_packages.create')}}" class="btn btn-rounded btn-info pull-right">{{__('Add New Package')}}</a>
    </div>
</div>

<br>

<div class="row">
    @foreach ($seller_packages as $key => $seller_package)
        <div class="col-lg-3">
            <div class="panel">
                <div class="panel-body text-center">
                    <img alt="Package Logo" class="img-lg img-circle mar-btm" src="{{ asset($seller_package->logo) }}">
                    <p class="text-lg text-semibold mar-no text-main">{{$seller_package->name}}</p>
                    <p class="text-3x">{{single_price($seller_package->amount)}}</p>
                    <p class="text-sm text-overflow pad-top">
                         Product Upload:
                        <span class="text-bold">{{$seller_package->product_upload}}</span>
                    </p>
                    <p class="text-sm text-overflow pad-top">
                         Digital Product Upload:
                        <span class="text-bold">{{$seller_package->digital_product_upload}}</span>
                    </p>
                    <p class="text-sm text-overflow pad-top">
                         Duration:
                        <span class="text-bold">{{$seller_package->duration}} days</span>
                    </p>
                    <div class="mar-top">
                        <a href="{{route('seller_packages.edit', encrypt($seller_package->id))}}" class="btn btn-mint">{{__('Edit')}}</a>
                        <a onclick="confirm_modal('{{route('seller_packages.destroy', $seller_package->id)}}');" class="btn btn-danger">{{__('Delete')}}</a>
                    </div>
                </div>
            </div>
        </div>
    @endforeach
</div>


@endsection
