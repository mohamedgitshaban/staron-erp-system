<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Interview extends Model
{
    use HasFactory;
     // Define the fillable fields
    protected $fillable = [
        'name',
        'interview_date',
        'interview_time',
        'cv_file',
        'attend',
        'notes_period',
        'expected_salary',
        'grade',
        'manager_approve',
        'manager_reason',
        'admin_approve',
        'admin_reason',
        'job_offer',
        'manager_notes',
        'reqrurmentsid'
    ];

    // Define the relationship with the Reqrurment model
    public function reqrurment()
    {
        return $this->belongsTo(Reqrurment::class, 'reqrurmentsid');
    }
}
