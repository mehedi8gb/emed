<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Jobs;
use App\JobCategory;
use App\JobLocations;
use Carbon\Carbon;
use App\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
class JobController extends Controller
{
    public function customerIndex(Request $request){

        $sort_search = null;
        // $jobs = Jobs::orderBy('created_at', 'desc');
         $jobs = Jobs::where('user_id', Auth::user()->id)->where('digital', 0)->orderBy('created_at', 'desc');


        if ($request->has('search')){
            $sort_search = $request->search;
            $jobs = $jobs->where('job_title', 'like', '%'.$sort_search.'%');
        }

        $jobs = $jobs->latest()->paginate(7);
        if(Auth::user()->user_type == 'customer'){
            return view('frontend.user.job_circuler.index' , compact('jobs', 'sort_search'));
        }
        if(Auth::user()->user_type == 'seller'){
            return view('frontend.user.job_circuler.index' , compact('jobs', 'sort_search'));
        }
        else {
            abort(404);
        }

    }


    public function index(Request $request){

        $sort_search = null;
        $jobs = Jobs::orderBy('created_at', 'desc');


        if ($request->has('search')){
            $sort_search = $request->search;
            $jobs = $jobs->where('job_title', 'like', '%'.$sort_search.'%');
        }

        $jobs = $jobs->latest()->paginate(7);
        if(Auth::user()->user_type == 'admin'){
            return view('backend.job_circuler.index' , compact('jobs', 'sort_search'));
        }
        else {
            abort(404);
        }

    }



    public function jobCreate(){
        $job = Jobs::all();
        $job_category = JobCategory::all();
        $job_locations = JobLocations::all();

        if(Auth::user()->user_type == 'admin'){

        return view('backend.job_circuler.create', compact('job','job_category','job_locations'));
        }
        elseif(Auth::user()->user_type == 'customer'){

        return view('frontend.user.job_circuler.create', compact('job','job_category', 'job_locations'));
        }
        else {
            abort(404);
        }



}




    public function Create(Request $request){
        if(Auth::user()->user_type == 'admin'){
            $request->validate([
                'job_title' => 'required|max:50|min:5',
                'category_id' => 'required',
                'short_description' => 'required|min:5',
                'job_description' => 'required|min:5',
                'slug' => 'required'
            ],
            [
                'category_id.required' => 'The category name field is required.',
                'slug.required' => 'The slug is required. add the title slug will automatically complete.'
            ]
        );




        $job = new Jobs;
        $job->category_id = $request->category_id;
        $job->added_by = $request->added_by;
        // if(Auth::user()->user_type == 'customer'){
        //     $job->user_id = Auth::user()->id;
        // }
        // else{
        //     $job->user_id = User::where('user_type', 'admin')->first()->id;
        // }
        $job->job_title = $request->job_title;
        $job->company = $request->company;
        $job->education = $request->education;
        $job->experience = $request->experience;
        $job->salary = $request->salary;
        $job->vacancy = $request->vacancy;
        $job->location_id = $request->location_id;
        $job->employment_status = $request->employment_status;
        $job->address = $request->address;
        $job->address2 = $request->address2;
        $job->gender = $request->gender;
        $job->age = $request->age;
        $job->address2 = $request->address2;
        $job->banner = $request->banner;
        $job->slug = preg_replace('/[^A-Za-z0-9\-]/', '', str_replace(' ', '-', $request->slug));
        $job->short_description = $request->short_description;
        $job->job_description = $request->job_description;

        $job->meta_title = $request->meta_title;
        $job->meta_img = $request->meta_img;
        $job->meta_description = $request->meta_description;
        $job->meta_keywords = $request->meta_keywords;
        $job->created_at = Carbon::now();

        $job->save();






            // Jobs::insert([
            //     'job_title' => $request->job_title,
            //     'category_id' => $request->category_id,
            //     'added_by' => $request->added_by,
            //     if(Auth::user()->user_type == 'seller'){
            //         $product->user_id = Auth::user()->id;
            //     }
            //     else{
            //         $product->user_id = \App\User::where('user_type', 'admin')->first()->id,
            //     },
            //     'company' => $request->company,
            //     'education' => $request->education,
            //     'experience' => $request->experience,
            //     'salary' => $request->salary,
            //     'vacancy' => $request->vacancy,
            //     'location_id' => $request->location_id,
            //     'employment_status' => $request->employment_status,
            //     'address' => $request->address,
            //     'address2' => $request->address2,
            //     'gender' => $request->gender,
            //     'age' => $request->age,
            //     'user_id' => Auth::user()->id,
            //     'slug' => preg_replace('/[^A-Za-z0-9\-]/', '', str_replace(' ', '-', $request->slug)),
            //     'short_description' => $request->short_description,
            //     'job_description' => $request->job_description,
            //     'banner' => $request->banner,
            //     'meta_title' => $request->meta_title,
            //     'meta_img' => $request->meta_img,
            //     'meta_description' => $request->meta_description,
            //     'meta_keywords' => $request->meta_keywords,
            //     'created_at' => Carbon::now()
            //     ]);
                flash(translate('Circuler has been inserted successfully'))->success();
                return Redirect()->route('job');



        }

        else {
            abort(404);
        }
    }


        public function customerCreate(Request $request)
        {
        if(Auth::user()->user_type == 'customer'){

            $request->validate([
                'job_title' => 'required|max:50|min:5',
                'category_id' => 'required',
                'short_description' => 'required|min:5|max:220',
                'job_description' => 'required|min:5|max:2400',
                'slug' => 'required'
            ],
            [
                'category_id.required' => 'The category name field is required.',
                'slug.required' => 'The slug is required. add the title slug will automatically complete.'
            ]
        );


            Jobs::insert([
                'job_title' => $request->job_title,
                'category_id' => $request->category_id,
                'company' => $request->company,
                'education' => $request->education,
                'experience' => $request->experience,
                'salary' => $request->salary,
                'vacancy' => $request->vacancy,
                'location_id' => $request->location_id,
                'employment_status' => $request->employment_status,
                'address' => $request->address,
                'address2' => $request->address2,
                'gender' => $request->gender,
                'age' => $request->age,
                'user_id' => Auth::user()->id,
                'slug' => preg_replace('/[^A-Za-z0-9\-]/', '', str_replace(' ', '-', $request->slug)),
                'short_description' => $request->short_description,
                'job_description' => $request->job_description,
                'banner' => $request->banner,
                'meta_title' => $request->meta_title,
                'meta_img' => $request->meta_img,
                'meta_description' => $request->meta_description,
                'meta_keywords' => $request->meta_keywords,
                'created_at' => Carbon::now()
                ]);
                flash(translate('Circuler has been inserted successfully'))->success();
                return Redirect()->route('users.job');


        }
        else {
            abort(404);
        }


    }

    public function destroy($id)
    {

        if(Auth::user()->user_type == 'admin'){

        Jobs::find($id)->delete();
        flash(translate('Circuler has been Delated successfully'))->success();

        return redirect('admin/job');

        }
        elseif(Auth::user()->user_type == 'customer'){

        Jobs::find($id)->delete();
        flash(translate('Circuler has been Delated successfully'))->success();

        return redirect('customer/job');

        }
        else {
            abort(404);
        }
    }


    public function jobEdit($id){
        $job = Jobs::find($id);
        $job_category = JobCategory::all();
        $job_locations = JobLocations::orderBy('location','ASC')->get();

        if(Auth::user()->user_type == 'admin'){

        return view('backend.job_circuler.edit', compact('job','job_category','job_locations'));
        }
        elseif(Auth::user()->user_type == 'customer'){

        return view('frontend.user.job_circuler.edit', compact('job','job_category', 'job_locations'));
        }
        else {
            abort(404);
        }



}

    public function update(Request $request, $id)
    {
        $request->validate([
            'job_title' => 'required|max:50|min:5',
            'category_id' => 'required',
            'short_description' => 'required|min:5',
            'job_description' => 'required|min:5',
            'slug' => 'required'
        ],
        [
            'category_id.required' => 'The category name field is required.',
            'slug.required' => 'The slug is required. add title then slug will automatically create.'
        ]
    );

        $job = Jobs::find($id);
        $job->category_id = $request->category_id;
        $job->job_title = $request->job_title;
        $job->company = $request->company;
        $job->education = $request->education;
        $job->experience = $request->experience;
        $job->salary = $request->salary;
        $job->vacancy = $request->vacancy;
        $job->location_id = $request->location_id;
        $job->employment_status = $request->employment_status;
        $job->address = $request->address;
        $job->address2 = $request->address2;
        $job->gender = $request->gender;
        $job->age = $request->age;
        $job->address2 = $request->address2;
        $job->banner = $request->banner;
        $job->slug = preg_replace('/[^A-Za-z0-9\-]/', '', str_replace(' ', '-', $request->slug));
        $job->short_description = $request->short_description;
        $job->job_description = $request->job_description;

        $job->meta_title = $request->meta_title;
        $job->meta_img = $request->meta_img;
        $job->meta_description = $request->meta_description;
        $job->meta_keywords = $request->meta_keywords;

        $job->save();
        if(Auth::user()->user_type == 'admin'){
            flash(translate('Circuler has been updated successfully'))->success();
            return redirect()->route('job');
        }
        elseif(Auth::user()->user_type == 'customer'){
            flash(translate('Circuler has been updated successfully'))->success();
        return redirect()->route('users.job');
        }
        else {
            abort(404);
        }
    }


    public function customerUpdate(Request $request, $id)
    {
        $request->validate([
            'job_title' => 'required|max:50|min:5',
            'category_id' => 'required',
            'short_description' => 'required|min:5',
            'job_description' => 'required|min:5',
            'slug' => 'required'
        ],
        [
            'category_id.required' => 'The category name field is required.',
            'slug.required' => 'The slug is required. add title then slug will automatically create.'
        ]
    );

        $job = Jobs::find($id);
        $job->category_id = $request->category_id;
        $job->job_title = $request->job_title;
        $job->company = $request->company;
        $job->education = $request->education;
        $job->experience = $request->experience;
        $job->salary = $request->salary;
        $job->vacancy = $request->vacancy;
        $job->location_id = $request->location_id;
        $job->employment_status = $request->employment_status;
        $job->address = $request->address;
        $job->address2 = $request->address2;
        $job->gender = $request->gender;
        $job->age = $request->age;
        $job->address2 = $request->address2;
        $job->banner = $request->banner;
        $job->slug = preg_replace('/[^A-Za-z0-9\-]/', '', str_replace(' ', '-', $request->slug));
        $job->short_description = $request->short_description;
        $job->job_description = $request->job_description;

        $job->meta_title = $request->meta_title;
        $job->meta_img = $request->meta_img;
        $job->meta_description = $request->meta_description;
        $job->meta_keywords = $request->meta_keywords;

        $job->save();

        if(Auth::user()->user_type == 'customer'){
            flash(translate('Circuler has been updated successfully'))->success();
        return redirect()->route('users.job');
        }
        else {
            abort(404);
        }
    }


    public function change_status(Request $request) {
        $job = Jobs::find($request->id);
        $job->status = $request->status;
        $job->save();
        return 1;
        if(Auth::user()->user_type == 'admin'){
            $job = Jobs::find($request->id);
        $job->status = $request->status;
        $job->save();
        return 1;
        }
        elseif(Auth::user()->user_type == 'customer'){
            $job = Jobs::find($request->id);
        $job->status = $request->status;
        $job->save();
        return 1;
        }
        else {
            abort(404);
        }
    }

    public function alljobs() {
        $jobs = Jobs::where('status', 1)->orderBy('created_at', 'desc')->paginate(12);
        return view("frontend.job.listing", compact('jobs'));
    }

    public function job_details(Request $id, $slug) {
        $job = Jobs::FindOrFail($id);
        $jobs = Jobs::where('slug', $slug)->first();
        $user = User::all();
        return view("frontend.job.details", compact('jobs','job','user'));
    }

}
