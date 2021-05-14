<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\JobCategory;

class Jobs extends Model
{
    use SoftDeletes;
    public function category() {
        return $this->belongsTo(JobCategory::class, 'category_id');
    }

}
