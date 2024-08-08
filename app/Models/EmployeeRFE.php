<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeRFE extends Model
{
    use HasFactory;
    protected $fillable = [
        'request_type',
        'hr_approve',
         'admin_approve',
          'hr_approve_date',
        'admin_approve_date',
         'from_date',
          'to_date',
           'from_ci',
           'to_co',
            'description',
            'user_id',
    ];
    protected $casts = [
        'hr_approve_date' => 'date',
        'admin_approve_date' => 'date',
        'from_date' => 'date',
        'to_date' => 'date',
        'from_ci' => 'datetime',
        'to_co' => 'datetime',
    ];


    public function user():BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
