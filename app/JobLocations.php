<?php

namespace App;
use App\Jobs;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class JobLocations extends Model
{
    public function jobs()
    {
        return $this->hasMany('App\Jobs');

    }
    public function location()
    {
        return $this->hasMany('App\JobCategory');
    }
}
