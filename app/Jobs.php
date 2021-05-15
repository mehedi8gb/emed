<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\JobCategory;
use APP\User;

class Jobs extends Model
{
    use SoftDeletes;
    public function category()
    {
        return $this->belongsTo(JobCategory::class, 'category_id');
    }
    public function jobuser()
    {
        return $this->hasOne(User::class, 'id','user_id');
    }
}
