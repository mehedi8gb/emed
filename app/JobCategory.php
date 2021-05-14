<?php

namespace App;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;
use App\Jobs;

class JobCategory extends Model
{
    use SoftDeletes;

    public function posts()
    {
        return $this->hasMany(Jobs::class);
    }
}
