<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\JobCategory;
use App\JobLocations;
use APP\User;

class Jobs extends Model
{

    protected $fillable = [

        'job_title' ,
        'category_id' ,
        'company' ,
        'education' ,
        'experience',
        'salary' ,
        'vacancy',
        'location_id' ,
        'employment_status' ,
        'address' ,
        'address2' ,
        'gender' ,
        'age' ,
        'user_id' ,
        'slug',
        'short_description' ,
        'job_description' ,
        'banner' ,
        'meta_title' ,
        'meta_img' ,
        'meta_description' ,
        'meta_keywords',
        'created_at'
        ];

        protected $table = "jobs";
        public $primaryKey = 'id';
    use SoftDeletes;
    public function category()
    {
        return $this->belongsTo(JobCategory::class, 'category_id');
    }
    public function jobuser()
    {
        return $this->hasOne(User::class, 'id','user_id');
    }
    public function location()
    {
        return $this->belongsTo('App\JobLocations');
    }
}
