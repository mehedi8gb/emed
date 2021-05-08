<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Job extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'id',
        'job_title',
        'slug',
        'job_description',
        'banner',
        'status',
    ];
}
