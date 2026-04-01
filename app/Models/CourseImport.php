<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CourseImport extends Model
{
    protected $table = 'course_imports';
    public $timestamps = false;
    protected $dateFormat = 'U';
    protected $guarded = ['id'];

    public static $pending = 'pending';
    public static $processing = 'processing';
    public static $completed = 'completed';
    public static $failed = 'failed';

    public function user()
    {
        return $this->belongsTo('App\User', 'user_id', 'id');
    }
}
