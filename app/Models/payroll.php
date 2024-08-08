<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class payroll extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'Date', 'workdays', 'holidays', 'attendance',
        'excuses', 'additions', 'deductions', 'dailyrate',
        'paiddays', 'SocialInsurance', 'MedicalInsurance', 'tax',
        'TotalPay', 'TotalLiquidPay',
    ];
    protected $casts = [
        'Date' => 'date:Y-m-d',
    ];
    protected $hidden=[
        "created_at","updated_at"
    ];
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id')->select("id",'name','hr_code','salary', 'department', 'profileimage','job_role','Supervisor','MedicalInsurance','SocialInsurance','Trancportation','kpi', 'tax',);
    }
    }
