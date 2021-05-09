<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Jobs;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
class JobController extends Controller
{
    public function index(){
        $jobs = Jobs::latest()->paginate(5);
        return view('backend.job_circuler.index' , compact('jobs'));
    }



    public function jobCreate(){
        return view('backend.job_circuler.create');

    }
    public function JoCreate(Request $request){

        $model= new Jobs();
        // $model->category_id = $request->category_id;
        $model->job_title        =   $request->job_title;
        $model->slug = preg_replace('/[^A-Za-z0-9\-]/', '', str_replace(' ', '-', $request->slug));
        $model->short_description       =   $request->short_description;
        $model->job_description      =   $request->job_description;
        $model->created_at  =   $request->created_at;
        $model->save();

        // Jobs::insert([
        //     // 'job_title' => $request->job_title,
        //     'slug' => $request->slug,
        //     'short_description' => $request->short_description,
        //     'job_description' => $request->job_description,
        //     'created_at' => Carbon::now()
        //     ]);
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
        // $job_categories =$jobCategory::all();

        return view('backend.job_circuler.edit', compact('job'));

}

    public function update(Request $request, $id)
    {
        $request->validate([
            'job_title' => 'required|max:255',
        ]);

        $job = Jobs::find($id);

        // $job->category_id = $request->category_id;
        $job->job_title = $request->job_title;
        // $job->banner = $request->banner;
        $job->slug = preg_replace('/[^A-Za-z0-9\-]/', '', str_replace(' ', '-', $request->slug));
        $job->short_description = $request->short_description;
        $job->job_description = $request->job_description;

        // $job->meta_title = $request->meta_title;
        // $job->meta_img = $request->meta_img;
        // $job->meta_description = $request->meta_description;
        // $job->meta_keywords = $request->meta_keywords;

        $job->save();


        flash(translate('job circuler has been updated successfully'))->success();
        return redirect()->route('job');
    }

}
