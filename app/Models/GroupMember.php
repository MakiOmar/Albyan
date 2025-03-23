<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use app\User;
use app\Models\CourseGroup;

class GroupMember extends Model
{
    protected $fillable = [
        'group_id',
        'student_id',
        'webinar_id',
    ];

    public function group()
    {
        return $this->belongsTo(CourseGroup::class, 'group_id');
    }

    public function student()
    {
        return $this->belongsTo(User::class, 'student_id');
    }
}
