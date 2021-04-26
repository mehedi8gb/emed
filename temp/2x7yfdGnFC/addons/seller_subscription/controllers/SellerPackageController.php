<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\SellerPackage;
use Auth;
use Session;
Use App\User;
Use App\Seller;
use Carbon\Carbon;

class SellerPackageController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $seller_packages = SellerPackage::all();
        return view('seller_packages.index',compact('seller_packages'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('seller_packages.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $seller_package = new SellerPackage;
        $seller_package->name = $request->name;
        $seller_package->amount = $request->amount;
        $seller_package->product_upload = $request->product_upload;
        $seller_package->digital_product_upload = $request->digital_product_upload;
        $seller_package->duration = $request->duration;
        if($request->hasFile('logo')){
            $seller_package->logo = $request->file('logo')->store('uploads/seller_package');
        }

        if($seller_package->save()){
            flash(__('Package has been inserted successfully'))->success();
            return redirect()->route('seller_packages.index');
        }
        else{
            flash(__('Something went wrong'))->error();
            return back();
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $seller_package = SellerPackage::findOrFail(decrypt($id));
        return view('seller_packages.edit', compact('seller_package'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $seller_package = SellerPackage::findOrFail($id);
        $seller_package->name = $request->name;
        $seller_package->amount = $request->amount;
        $seller_package->product_upload = $request->product_upload;
        $seller_package->digital_product_upload = $request->digital_product_upload;
        $seller_package->duration = $request->duration;
        if($request->hasFile('logo')){
            $seller_package->logo = $request->file('logo')->store('uploads/seller_package');
        }

        if($seller_package->save()){
            flash(__('Package has been inserted successfully'))->success();
            return redirect()->route('seller_packages.index');
        }
        else{
            flash(__('Something went wrong'))->error();
            return back();
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        if(SellerPackage::destroy($id)){
            flash(__('Package has been deleted successfully'))->success();
            return redirect()->route('seller_packages.index');
        }
        else{
            flash(__('Something went wrong'))->error();
            return back();
        }
    }


    //FrontEnd
    //@index
    public function seller_packages_list()
    {
        $seller_packages = SellerPackage::all();
        return view('seller_packages.frontend.seller_packages_list',compact('seller_packages'));
    }

    public function purchase_package(Request $request)
    {
        $data['seller_package_id'] = $request->seller_package_id;
        $data['payment_method'] = $request->payment_option;

        $request->session()->put('payment_type', 'seller_package_payment');
        $request->session()->put('payment_data', $data);

        $seller_package = SellerPackage::findOrFail(Session::get('payment_data')['seller_package_id']);

        if($seller_package->amount == 0){
            if(Auth::user()->seller->seller_package_id != $seller_package->id){
                return $this->purchase_payment_done(Session::get('payment_data'), null);
            }
            else {
                flash(__('You can not purchase this package anymore.'))->warning();
                return back();
            }
        }

        if($request->payment_option == 'paypal'){
            $paypal = new PaypalController;
            return $paypal->getCheckout();
        }
        elseif ($request->payment_option == 'stripe') {
            $stripe = new StripePaymentController;
            return $stripe->stripe();
        }
        elseif ($request->payment_option == 'sslcommerz_payment') {
            $sslcommerz = new PublicSslCommerzPaymentController;
            return $sslcommerz->index($request);
        }
        elseif ($request->payment_option == 'instamojo') {
            $instamojo = new InstamojoController;
            return $instamojo->pay($request);
        }
        elseif ($request->payment_option == 'razorpay') {
            $razorpay = new RazorpayController;
            return $razorpay->payWithRazorpay($request);
        }
        elseif ($request->payment_option == 'paystack') {
            $paystack = new PaystackController;
            return $paystack->redirectToGateway($request);
        }
    }

    public function purchase_payment_done($payment_data, $payment){
        $seller = Auth::user()->seller;
        $seller->seller_package_id = Session::get('payment_data')['seller_package_id'];
        $seller_package = SellerPackage::findOrFail(Session::get('payment_data')['seller_package_id']);
        $seller->remaining_uploads += $seller_package->product_upload;
        $seller->remaining_digital_uploads += $seller_package->digital_product_upload;
        $seller->invalid_at = date('Y-m-d', strtotime('+ '.$seller_package->duration.'days'));
        $seller->save();

        flash(__('Package purchasing successful'))->success();
        return redirect()->route('dashboard');
    }

    public function unpublish_products(Request $request){
        foreach (Seller::all() as $key => $seller) {
            if($seller->invalid_at != null && Carbon::now()->diffInDays(Carbon::parse($seller->invalid_at), false) <= 0){
                foreach ($seller->user->products as $key => $product) {
                    $product->published = 0;
                    $product->save();
                }
            }
        }
    }
}
