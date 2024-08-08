<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AttendanceAssignLog extends Model
{
    use HasFactory;
    protected $fillable = [
       'id', 'userid', 'date',
    ];
    protected $dates = [
        'date', 'created_at', 'updated_at',
    ];
    public function user()
    {
        return $this->belongsTo(User::class, 'userid')->select('id', 'name', 'profileimage',"department");
    }
}
