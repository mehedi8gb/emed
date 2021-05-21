<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\JobLocations;
use App\Jobs;

class JobLocationsController extends Controller

{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $sort_search =null;
        $job = Jobs::all();
        $job_locations = JobLocations::orderBy('id', 'asc');
         if ($request->has('search')){
            $sort_search = $request->search;
            $job_locations = $job_locations->where('location', 'like', '%'.$sort_search.'%');
        }

        $job_locations = $job_locations->paginate(15);
        return view('backend.job_circuler.location.index', compact('job_locations', 'sort_search', 'job'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $job_locations = JobLocations::all();
        $job = Jobs::all();
        return view('backend.Job_circuler.location.create', compact('job_locations', 'job'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {

        $request->validate([
            'location' => 'required|max:25|min:3',

        ]);

        $locations = new JobLocations;

        $locations->location = $request->location;
        $locations->slug = preg_replace('/[^A-Za-z0-9\-]/', '', str_replace(' ', '-', $request->location));

        $locations->save();


        flash(translate('Job locations has been created successfully'))->success();
        return redirect()->route('job.location.index');
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
        $locations = JobLocations::find($id);
        $job_locations = JobLocations::all();

        return view('backend.job_circuler.location.edit',  compact('locations','job_locations'));
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
        $request->validate([
            ['location' => 'required|max:255|min:3'|'unique:location'],

            [
                'locations_id.required' => 'The location name field is required.',
                'locations_id.unique' => 'The location name must not be same.'
            ]
        ]);


        $locations = JobLocations::find($id);

        $locations->location = $request->location;
        $locations->slug = preg_replace('/[^A-Za-z0-9\-]/', '', str_replace(' ', '-', $request->slug));

        $locations->save();


        flash(translate('Job locations has been updated successfully'))->success();
        return redirect()->route('job.location.index');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        JobLocations::find($id)->delete();

        return redirect('admin/job/location');
    }
}
