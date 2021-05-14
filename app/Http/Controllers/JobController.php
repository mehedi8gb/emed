<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Jobs;
use App\JobCategory;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
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
        return view('backend.job_circuler.index' , compact('jobs', 'sort_search'));
    }

    public function CatCreate()
    {
        $job_category = JobCategory::all();
        return view('backend.job_circuler.create', compact('job_category'));
    }



    public function jobCreate(){
        return view('backend.job_circuler.create');

    }
    public function Create(Request $request){
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


        // $model= new Jobs();
        // // $model->category_id = $request->category_id;
        // $model->job_title        =   $request->job_title;
        // $model->slug = preg_replace('/[^A-Za-z0-9\-]/', '', str_replace(' ', '-', $request->slug));
        // $model->short_description       =   $request->short_description;
        // $model->job_description      =   $request->job_description;
        // $model->created_at  =   $request->Carbon::now();
        // $model->save();


        Jobs::insert([
            'job_title' => $request->job_title,
            'category_id' => $request->category_id,
            'slug' => preg_replace('/[^A-Za-z0-9\-]/', '', str_replace(' ', '-', $request->slug)),
            'short_description' => $request->short_description,
            'job_description' => $request->job_description,
            'meta_title' => $request->meta_title,
            'meta_img' => $request->meta_img,
            'meta_description' => $request->meta_description,
            'meta_keywords' => $request->meta_keywords,
            'created_at' => Carbon::now(),
            ]);
            flash(translate('Circuler has been inserted successfully'))->success();
            return Redirect()->route('job');
    }

    public function destroy($id)
    {
        Jobs::find($id)->delete();
        flash(translate('Circuler has been Delated successfully'))->success();

        return redirect('admin/job');
    }


    public function jobEdit($id){

        $job = Jobs::find($id);
        $job_category = JobCategory::all();

        return view('backend.job_circuler.edit', compact('job','job_category'));

}

    public function update(Request $request, $id)
    {
        $request->validate([
            'job_title' => 'required|max:50|min:5',
            'category_id' => 'required',
            'short_description' => 'required|min:5|max:150',
            'job_description' => 'required|min:5|max:1200',
            'slug' => 'required'
        ],
        [
            'category_id.required' => 'The category name field is required.',
            'slug.required' => 'The slug is required. add title then slug will automatically create.'
        ]
    );

        // $update = Jobs::find($id)->update([

        //     'job_title' => $request->job_title,
        //     // $job->banner = $request->banner;
        //     'category_id' => $request->category_id,
        //     'slug' => preg_replace('/[^A-Za-z0-9\-]/', '', str_replace(' ', '-', $request->slug)),
        //     'short_description' => $request->short_description,
        //     'job_description' => $request->job_description,

        // ]);

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


        flash(translate('Circuler has been updated successfully'))->success();
        return redirect()->route('job');
    }


    public function change_status(Request $request) {
        $job = Jobs::find($request->id);
        $job->status = $request->status;
        $job->save();
        return 1;
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
