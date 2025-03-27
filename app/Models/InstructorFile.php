<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InstructorFile extends Model
{
    protected $fillable = [
        'webinar_id', 'instructor_id', 'title', 'path', 'group_id'
    ];
}
