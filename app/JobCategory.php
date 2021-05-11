<?php

namespace App;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;
use App\Jobs;

class JobCategory extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'id',
        'category_name',
        'slug',
    ];
    public function category_name()
    {
        return $this->hasMany(Jobs::class);
    }
}
