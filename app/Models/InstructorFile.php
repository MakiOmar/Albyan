<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\CourseGroup;

class InstructorFile extends Model
{
    protected $fillable = [
        'webinar_id', 'instructor_id', 'title', 'path', 'group_id'
    ];
    public function courseGroup()
    {
        return $this->belongsTo(CourseGroup::class, 'group_id');
    }
}
