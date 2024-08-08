<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IncentivePayrollSubmission extends Model
{
    use HasFactory;
    protected $fillable = [
        'data', 'user_id','month_id'
    ];

    /**
     * Get the user that submitted the incentive payroll.
     */
    public function user():BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
    public function monthlyRecord():BelongsTo
    {
        return $this->belongsTo(MonthRecord::class,'month_id');
    }
}
