<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\User;

class WebinarCertificate extends Model
{
    // Define the table name if it doesn't follow Laravel's convention
    protected $table = 'webinar_certificates';

    // Specify the fillable columns
    protected $fillable = array( 'student_id', 'webinar_title', 'certificates' );

    // Declare that 'certificates' is a JSON column
    protected $casts = array(
        'certificates' => 'array',
    );

    // Define the relationship with the User model
    public function student()
    {
        return $this->belongsTo(User::class, 'student_id');
    }
}
