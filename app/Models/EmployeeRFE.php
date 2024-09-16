<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeRFE extends Model
{
    use HasFactory;
    protected $fillable = [
        'id',
        'request_type',
        'hr_approve',
        'hr_approve_date',
        'from_date',
        'to_date',
        'from_ci',
        'to_co',
        'description',
        'user_id',
    ];
    protected $casts = [
        'hr_approve_date' => 'date:y-m-d',
        'from_date' => 'date:y-m-d',
        'to_date' => 'date:y-m-d',
        'from_ci' => 'datetime:H:i:s',
        'to_co' => 'datetime:H:i:s',
    ];


    public function user():BelongsTo
    {
        return $this->belongsTo(User::class)->select('name', 'hr_code','department', 'profileimage','id','hraccess');
    }
}
