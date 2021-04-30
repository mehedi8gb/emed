    @extends('backend.layouts.app')

    @section('content')

    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header ">
                    <h5 class="mb-0 h6">{{translate('Bkash Credential')}}</h5>
                </div>
                <div class="card-body">
                    <form class="form-horizontal" action="{{ route('payment_method.update') }}" method="POST">
                        @csrf
                        <input type="hidden" name="payment_method" value="bkash">
                        <div class="form-group row">
                            <input type="hidden" name="types[]" value="BKASH_CHECKOUT_APP_KEY">
                            <div class="col-md-4">
                                <label class="col-from-label">{{translate('BKASH CHECKOUT APP KEY')}}</label>
                            </div>
                            <div class="col-md-8">
                                <input type="text" class="form-control" name="BKASH_CHECKOUT_APP_KEY" value="{{  env('BKASH_CHECKOUT_APP_KEY') }}" placeholder="{{translate('BKASH CHECKOUT APP KEY')}}" required>
                            </div>
                        </div>
                        <div class="form-group row">
                            <input type="hidden" name="types[]" value="BKASH_CHECKOUT_APP_SECRET">
                            <div class="col-md-4">
                                <label class="col-from-label">{{translate('BKASH CHECKOUT APP SECRET')}}</label>
                            </div>
                            <div class="col-md-8">
                                <input type="text" class="form-control" name="BKASH_CHECKOUT_APP_SECRET" value="{{  env('BKASH_CHECKOUT_APP_SECRET') }}" placeholder="{{translate('BKASH CHECKOUT APP SECRET')}}" required>
                            </div>
                        </div>
                        <div class="form-group row">
                            <input type="hidden" name="types[]" value="BKASH_CHECKOUT_USER_NAME">
                            <div class="col-md-4">
                                <label class="col-from-label">{{translate('BKASH CHECKOUT USER NAME')}}</label>
                            </div>
                            <div class="col-md-8">
                                <input type="text" class="form-control" name="BKASH_CHECKOUT_USER_NAME" value="{{  env('BKASH_CHECKOUT_USER_NAME') }}" placeholder="{{translate('BKASH CHECKOUT USER NAME')}}" required>
                            </div>
                        </div>
                        <div class="form-group row">
                            <input type="hidden" name="types[]" value="BKASH_CHECKOUT_PASSWORD">
                            <div class="col-md-4">
                                <label class="col-from-label">{{translate('BKASH CHECKOUT PASSWORD')}}</label>
                            </div>
                            <div class="col-md-8">
                                <input type="text" class="form-control" name="BKASH_CHECKOUT_PASSWORD" value="{{  env('BKASH_CHECKOUT_PASSWORD') }}" placeholder="{{translate('BKASH CHECKOUT PASSWORD')}}" required>
                            </div>
                        </div>
                        <div class="form-group row">
                            <div class="col-md-4">
                                <label class="col-from-label">{{translate('Bkash Sandbox Mode')}}</label>
                            </div>
                            <div class="col-md-8">
                                <label class="aiz-switch aiz-switch-success mb-0">
                                    <input value="1" name="bkash_sandbox" type="checkbox" @if (\App\BusinessSetting::where('type', 'bkash_sandbox')->first()->value == 1)
                                        checked
                                    @endif>
                                    <span class="slider round"></span>
                                </label>
                            </div>
                        </div>
                        <div class="form-group mb-0 text-right">
                            <button type="submit" class="btn btn-sm btn-primary">{{translate('Save')}}</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0 h6 ">{{translate('Nagad Credential')}}</h5>
                </div>
                <div class="card-body">
                    <form class="form-horizontal" action="{{ route('payment_method.update') }}" method="POST">
                        @csrf
                        <input type="hidden" name="payment_method" value="nagad">
                        <div class="form-group row">
                            <input type="hidden" name="types[]" value="NAGAD_MODE">
                            <div class="col-md-4">
                                <label class="col-from-label">{{translate('NAGAD MODE')}}</label>
                            </div>
                            <div class="col-md-8">
                            <input type="text" class="form-control" name="NAGAD_MODE" value="{{  env('NAGAD_MODE') }}" placeholder="{{translate('NAGAD MODE')}}" required>
                            </div>
                        </div>
                        <div class="form-group row">
                            <input type="hidden" name="types[]" value="NAGAD_MERCHANT_ID">
                            <div class="col-md-4">
                                <label class="col-from-label">{{translate('NAGAD MERCHANT ID')}}</label>
                            </div>
                            <div class="col-md-8">
                                <input type="text" class="form-control" name="NAGAD_MERCHANT_ID" value="{{  env('NAGAD_MERCHANT_ID') }}" placeholder="{{translate('NAGAD MERCHANT ID')}}" required>
                            </div>
                        </div>
                        <div class="form-group row">
                            <input type="hidden" name="types[]" value="NAGAD_MERCHANT_NUMBER">
                            <div class="col-md-4">
                                <label class="col-from-label">{{translate('NAGAD MERCHANT NUMBER')}}</label>
                            </div>
                            <div class="col-md-8">
                                <input type="text" class="form-control" name="NAGAD_MERCHANT_NUMBER" value="{{  env('NAGAD_MERCHANT_NUMBER') }}" placeholder="{{translate('NAGAD MERCHANT NUMBER')}}" required>
                            </div>
                        </div>
                        <div class="form-group row">
                            <input type="hidden" name="types[]" value="NAGAD_PG_PUBLIC_KEY">
                            <div class="col-md-4">
                                <label class="col-from-label">{{translate('NAGAD PG PUBLIC KEY')}}</label>
                            </div>
                            <div class="col-md-8">
                                <input type="text" class="form-control" name="NAGAD_PG_PUBLIC_KEY" value="{{  env('NAGAD_PG_PUBLIC_KEY') }}" placeholder="{{translate('NAGAD PG PUBLIC KEY')}}" required>
                            </div>
                        </div>
                        <div class="form-group row">
                            <input type="hidden" name="types[]" value="NAGAD_MERCHANT_PRIVATE_KEY">
                            <div class="col-md-4">
                                <label class="col-from-label">{{translate('NAGAD MERCHANT PRIVATE KEY')}}</label>
                            </div>
                            <div class="col-md-8">
                                <input type="text" class="form-control" name="NAGAD_MERCHANT_PRIVATE_KEY" value="{{  env('NAGAD_MERCHANT_PRIVATE_KEY') }}" placeholder="{{translate('NAGAD MERCHANT PRIVATE KEY')}}" required>
                            </div>
                        </div>
                        <div class="form-group mb-0 text-right">
                            <button type="submit" class="btn btn-sm btn-primary">{{translate('Save')}}</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card">
                <div class="card-header ">
                    <h5 class="mb-0 h6">{{translate('Sslcommerz Credential')}}</h5>
                </div>
                <div class="card-body">
                    <form class="form-horizontal" action="{{ route('payment_method.update') }}" method="POST">
                        @csrf
                        <input type="hidden" name="payment_method" value="sslcommerz">
                        <div class="form-group row">
                            <input type="hidden" name="types[]" value="SSLCZ_STORE_ID">
                            <div class="col-md-4">
                                <label class="col-from-label">{{translate('Sslcz Store Id')}}</label>
                            </div>
                            <div class="col-md-8">
                                <input type="text" class="form-control" name="SSLCZ_STORE_ID" value="{{  env('SSLCZ_STORE_ID') }}" placeholder="{{translate('Sslcz Store Id')}}" required>
                            </div>
                        </div>
                        <div class="form-group row">
                            <input type="hidden" name="types[]" value="SSLCZ_STORE_PASSWD">
                            <div class="col-md-4">
                                <label class="col-from-label">{{translate('Sslcz store password')}}</label>
                            </div>
                            <div class="col-md-8">
                                <input type="text" class="form-control" name="SSLCZ_STORE_PASSWD" value="{{  env('SSLCZ_STORE_PASSWD') }}" placeholder="{{translate('Sslcz store password')}}" required>
                            </div>
                        </div>
                        <div class="form-group row">
                            <div class="col-md-4">
                                <label class="col-from-label">{{translate('Sslcommerz Sandbox Mode')}}</label>
                            </div>
                            <div class="col-md-8">
                                <label class="aiz-switch aiz-switch-success mb-0">
                                    <input value="1" name="sslcommerz_sandbox" type="checkbox" @if (\App\BusinessSetting::where('type', 'sslcommerz_sandbox')->first()->value == 1)
                                        checked
                                    @endif>
                                    <span class="slider round"></span>
                                </label>
                            </div>
                        </div>
                        <div class="form-group mb-0 text-right">
                            <button type="submit" class="btn btn-sm btn-primary">{{translate('Save')}}</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
  @endsection
