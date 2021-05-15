<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Jobs;
use App\JobCategory;
use Carbon\Carbon;
use App\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
class JobController extends Controller
{
    public function index(Request $request){


        $sort_search = null;
        $jobs = Jobs::orderBy('created_at', 'desc');

        if ($request->search != null){
            $jobs = $jobs->where('job_title', 'like', '%'.$request->search.'%');
            $sort_search = $request->search;
        }

        $jobs = Jobs::latest()->paginate(5);
        if(Auth::user()->user_type == 'admin'){
            return view('backend.job_circuler.index' , compact('jobs', 'sort_search'));
        }
        elseif(Auth::user()->user_type == 'customer'){
            return view('frontend.user.job_circuler.index' , compact('jobs', 'sort_search'));
        }
        else {
            abort(404);
        }

    }

    public function CatCreate()
    {
        $job_category = JobCategory::all();
        if(Auth::user()->user_type == 'admin'){
            return view('backend.job_circuler.create', compact('job_category'));
        }
        elseif(Auth::user()->user_type == 'customer'){
            return view('frontend.user.job_circuler.create', compact('job_category'));
        }
        else {
            abort(404);
        }



    }



    public function jobCreate(){

        $jobs = Jobs::latest()->paginate(5);
        $job_category = JobCategory::all();
        if(Auth::user()->user_type == 'admin'){
            return view('backend.job_circuler.create',compact('jobs','job_category'));
        }
        elseif(Auth::user()->user_type == 'customer'){
            return view('frontend.user.job_circuler.create', compact('jobs','job_category'));
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
                'short_description' => 'required|min:5|max:150',
                'job_description' => 'required|min:5|max:1200',
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
                'user_id' => Auth::user()->id,
                'slug' => preg_replace('/[^A-Za-z0-9\-]/', '', str_replace(' ', '-', $request->slug)),
                'short_description' => $request->short_description,
                'job_description' => $request->job_description,
                'banner' => $request->banner,
                'meta_title' => $request->meta_title,
                'meta_img' => $request->meta_img,
                'meta_description' => $request->meta_description,
                'meta_keywords' => $request->meta_keywords,
                'created_at' => Carbon::now(),
                ]);
                flash(translate('Circuler has been inserted successfully'))->success();
                return Redirect()->route('job');



        }
        elseif(Auth::user()->user_type == 'customer'){

            $request->validate([
                'job_title' => 'required|max:50|min:5',
                'category_id' => 'required',
                'short_description' => 'required|min:5|max:150',
                'job_description' => 'required|min:5|max:1200',
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
                'user_id' => Auth::user()->id,
                'slug' => preg_replace('/[^A-Za-z0-9\-]/', '', str_replace(' ', '-', $request->slug)),
                'short_description' => $request->short_description,
                'job_description' => $request->job_description,
                'banner' => $request->banner,
                'meta_title' => $request->meta_title,
                'meta_img' => $request->meta_img,
                'meta_description' => $request->meta_description,
                'meta_keywords' => $request->meta_keywords,
                'created_at' => Carbon::now(),
                ]);
                flash(translate('Circuler has been inserted successfully'))->success();
                return Redirect()->route('user.job');


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

        if(Auth::user()->user_type == 'admin'){

        return view('backend.job_circuler.edit', compact('job','job_category'));
        }
        elseif(Auth::user()->user_type == 'customer'){

        return view('frontend.user.job_circuler.edit', compact('job','job_category'));
        }
        else {
            abort(404);
        }



}

    public function update(Request $request, $id)
    {
        $request->validate([
            'job_title' => 'required|max:50|min:5',
            // 'category_id' => 'required',
            'short_description' => 'required|min:5|max:150',
            'job_description' => 'required|min:5|max:1200',
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
        return redirect()->route('user.job');
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

    public function job_details($slug) {
        $jobs = Jobs::where('slug', $slug)->first();
        return view("frontend.job.details", compact('jobs'));
    }

}
