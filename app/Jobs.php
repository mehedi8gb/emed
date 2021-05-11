<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\JobCategory;

class Jobs extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'id',
        'category_id;',
        'job_title',
        'slug',
        'job_description',
        'banner',
        'status',
        'created_at'
    ];
    public function category() {
        return $this->belongsTo(JobCategory::class, 'category_id');
    }

}
