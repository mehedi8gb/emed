@extends('backend.layouts.blank')
@section('content')
    <div class="container pt-5">
        <div class="row">
            <div class="col-xl-6 mx-auto">
                <div class="card">
                    <div class="card-body">
                        <div class="mar-ver pad-btm text-center">
                            <h1 class="h3">Secret Key</h1>
                            <p>
                                Provide your secret key.<br>
                                <a href="{{ env('DEV_FB') }}" target="_blank">Where to get secret key?</a>
                            </p>
                        </div>
                        <p class="text-muted font-13">
                            <form method="POST" action="{{ route('purchase.code') }}">
                                @csrf
                                <div class="form-group">
                                    <label for="purchase_code">Purchase Code</label>
                                    <input type="text" class="form-control" id="purchase_code" name="purchase_code" placeholder="**** **** **** ****" required="">
                                </div>
                                <div class="text-center">
                                    <button type="submit" class="btn btn-primary">Continue</button>
                                </div>
                            </form>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
