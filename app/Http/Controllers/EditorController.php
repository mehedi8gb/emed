<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class EditorController extends Controller
{
    public function richtextedit(){
        return view('backend.job_circuler.index');
    }

    public function jobCreate(){
        return view('backend.job_circuler.create');
    }

    public function jobEdit(){
        return view('backend.job_circuler.edit');
    }
}
