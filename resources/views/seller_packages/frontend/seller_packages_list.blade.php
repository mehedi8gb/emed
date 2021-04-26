@extends('frontend.layouts.app')

@section('content')

<div id="page-content">
    <section class="pb-4 pt-5">
        <div class="container">
            <div class="text-center">
                <h1 class="heading-3 strong-600">{{__('Premium Packages for Sellers')}}</h1>
            </div>
            <div class="row justify-content-center gutters-10">
                @foreach ($seller_packages as $key => $seller_package)
                    <div class="col-xl-3 col-lg-4 col-md-6">
                        <div class="dashboard-widget text-center mt-4 c-pointer">
                            <img alt="Package Logo" src="{{ asset($seller_package->logo) }}" width="200" class="img-fluid mb-4">
                            <span class="d-block title">{{ $seller_package->name }}</span>
                            <br>
                            <p>{{__('Product Upload')}}: {{ $seller_package->product_upload }} {{__('Times')}}</p>
                            <p>{{__('Digital Product Upload')}}: {{ $seller_package->digital_product_upload }} {{__('Times')}}</p>
                            <p>{{__('Duration')}}: {{ $seller_package->duration }} {{__('Days')}}</p>
                            <span class="d-block title">{{__('Price')}}: {{ single_price($seller_package->amount) }}</span>
                            <hr>
                            @if ($seller_package->amount == 0)
                                <button class="btn btn-base-1 w-100" onclick="get_free_package({{ $seller_package->id}})">{{__('Free Package')}}</button>
                            @else
                                <button class="btn btn-base-1 w-100" onclick="show_price_modal({{ $seller_package->id}})">{{__('Get Package')}}</button>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>
</div>

<div class="modal fade" id="price_modal" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-zoom product-modal" id="modal-size" role="document">
        <div class="modal-content position-relative">
            <div class="modal-header">
                <h5 class="modal-title strong-600 heading-5">{{__('Purchase Your Package')}}</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form class="" id="package_payment_form" action="{{ route('seller_packages.purchase') }}" method="post">
                @csrf
                <input type="hidden" name="seller_package_id" value="">
                <div class="modal-body gry-bg px-3 pt-3">
                    <div class="row">
                        <div class="col-md-2">
                            <label>{{__('Payment Method')}}</label>
                        </div>
                        <div class="col-md-10">
                            <div class="mb-3">
                                <select class="form-control selectpicker" data-minimum-results-for-search="Infinity" name="payment_option">
                                    @if (\App\BusinessSetting::where('type', 'paypal_payment')->first()->value == 1)
                                        <option value="paypal">{{__('Paypal')}}</option>
                                    @endif
                                    @if (\App\BusinessSetting::where('type', 'stripe_payment')->first()->value == 1)
                                        <option value="stripe">{{__('Stripe')}}</option>
                                    @endif
                                    @if(\App\BusinessSetting::where('type', 'sslcommerz_payment')->first()->value == 1)
                                        <option value="sslcommerz">{{__('sslcommerz')}}</option>
                                    @endif
                                    @if(\App\BusinessSetting::where('type', 'instamojo_payment')->first()->value == 1)
                                        <option value="instamojo">{{__('Instamojo')}}</option>
                                    @endif
                                    @if(\App\BusinessSetting::where('type', 'razorpay')->first()->value == 1)
                                        <option value="razorpay">{{__('RazorPay')}}</option>
                                    @endif
                                    @if(\App\BusinessSetting::where('type', 'paystack')->first()->value == 1)
                                        <option value="paystack">{{__('PayStack')}}</option>
                                    @endif
                                    @if(\App\BusinessSetting::where('type', 'voguepay')->first()->value == 1)
                                        <option value="voguepay">{{__('Voguepay')}}</option>
                                    @endif
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">{{__('cancel')}}</button>
                    <button type="submit" class="btn btn-base-1">{{__('Confirm')}}</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@section('script')
    <script type="text/javascript">
        function show_price_modal(id){
            $('input[name=seller_package_id]').val(id);
            $('#price_modal').modal('show');
        }

        function get_free_package(id){
            $('input[name=seller_package_id]').val(id);
            $('#package_payment_form').submit();
        }
    </script>
@endsection
