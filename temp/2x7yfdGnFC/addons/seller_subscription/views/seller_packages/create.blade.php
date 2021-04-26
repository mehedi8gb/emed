@extends('layouts.app')

@section('content')

    <div class="col-lg-10 col-lg-offset-1">
        <div class="panel">
            <div class="panel-heading">
                <h3 class="panel-title">{{__('Create New Seller Package')}}</h3>
            </div>

            <!--Horizontal Form-->
            <!--===================================================-->
            <form class="form-horizontal" action="{{ route('seller_packages.store') }}" method="POST" enctype="multipart/form-data">
            	@csrf
                <div class="panel-body">
                    <div class="form-group">
                        <label class="col-sm-2 control-label" for="name">{{__('Package Name')}}</label>
                        <div class="col-sm-10">
                            <input type="text" placeholder="{{__('Name')}}" id="name" name="name" class="form-control" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-2 control-label" for="amount">{{__('Amount')}}</label>
                        <div class="col-sm-10">
                            <input type="number" min="0" step="0.01" placeholder="{{__('Amount')}}" id="amount" name="amount" class="form-control" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-2 control-label" for="product_upload">{{__('Product Upload')}}</label>
                        <div class="col-sm-10">
                            <input type="number" min="0" step="1" placeholder="{{__('Product Upload')}}" id="product_upload" name="product_upload" class="form-control" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-2 control-label" for="digital_product_upload">{{__('Digital Product Upload')}}</label>
                        <div class="col-sm-10">
                            <input type="number" min="0" step="1" placeholder="{{__('Digital Product Upload')}}" id="digital_product_upload" name="digital_product_upload" class="form-control" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-2 control-label" for="duration">{{__('Duration')}}</label>
                        <div class="col-sm-10">
                            <input type="number" min="0" step="1" placeholder="{{__('Validity in number of days')}}" id="duration" name="duration" class="form-control" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-2 control-label" for="logo">{{__('Package Logo')}}</label>
                        <div class="col-sm-10">
                            <input type="file" id="logo" name="logo" class="form-control">
                        </div>
                    </div>
                </div>
                <div class="panel-footer text-right">
                    <button class="btn btn-purple" type="submit">{{__('Save')}}</button>
                </div>
            </form>
            <!--===================================================-->
            <!--End Horizontal Form-->

        </div>
    </div>

@endsection
