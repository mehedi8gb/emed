<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Jobs;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
class EditorController extends Controller
{
    public function index(){
        $jobs = Jobs::latest()->get();
        return view('backend.job_circuler.index' , compact('jobs'));
    }

    public function jobEdit(){
        return view('backend.job_circuler.edit');
    }

    public function jobCreate(Request $request){

        return view('backend.job_circuler.create');
    }
    public function Create(Request $request){
         Jobs::insert([

            'job_title' => $request->job_title,
            'slug' => $request->slug,
            'short_description' => $request->short_description,
            'job_description' => $request->job_description,
            'created_at' => Carbon::now()
            ]);
    }

    }

