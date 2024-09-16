<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use App\Models\EmployeeRFE;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'hr_code',
        'date',
        'address',
        'password',
        'salary',
        'department',
        'profileimage',
        'job_role',
        'job_tybe',
        'pdf',
        'Supervisor',
        'MedicalInsurance',
        'VacationBalance',
        'SocialInsurance',
        'Trancportation',
        'overtime',
        'overtime_value',
        'phone',
        'EmploymentDate',
        'isemploee',
        'kpi',
        'tax',
        'TimeStamp',
        'grade',
        'segment',
        'startwork',
        'endwork',
        'clockin',
        'clockout',
        'last_login',
        'email_verified_at',
        'remember_token',
        'hraccess',
        'salesaccess',
        'technicalaccess',
        'controlaccess',
        'supplychainaccess',
        'operationaccess',
        'financeaccess',
        'adminstrationaccess'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */

    protected $casts = [
        "email_verified_at" => "date:y-m-d",
        'date' => 'date',
        'EmploymentDate' => 'date',
        'clockin' => 'datetime:H:i:s',
        'clockout' => 'datetime:H:i::s',
        'last_login' => 'datetime:H:i',
        'password' => 'hashed',
    ];
    protected $hidden = [
        "created_at",
        "updated_at"
    ];
    public function TechnecalRequest(): HasMany
    {
        return $this->hasMany(TechnecalRequest::class, "asign_for_user");
    }
    public function userLogs(): HasMany
    {
        return $this->hasMany(UserLog::class);
    }
    public function supervisor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'Supervisor')->select('id', 'name', 'profileimage', 'Supervisor', 'department');;
    }
    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class, 'user_id');
    }
    public function AttendanceAssignLog(): HasMany
    {
        return $this->hasMany(AttendanceAssignLog::class);
    }
    public function leavingBalanceLogs(): HasMany
    {
        return $this->hasMany(LeavingBalanceLog::class);
    }
    public function warningLogs(): HasMany
    {
        return $this->hasMany(WarningLog::class);
    }
    public function payrolls(): HasMany
    {
        return $this->hasMany(Payroll::class, 'user_id');
    }
    public function payrollSubmissions(): HasMany
    {
        return $this->hasMany(PayrollSubmission::class);
    }
    public function incentivePayrolls(): HasMany
    {
        return $this->hasMany(IncentivePayroll::class);
    }
    public function incentivePayrollSubmissions(): HasMany
    {
        return $this->hasMany(IncentivePayrollSubmission::class);
    }
    public function employeeRFE(): HasMany
    {
        return $this->hasMany(EmployeeRFE::class);
    }
    public function assignedRequirements(): HasMany
    {
        return $this->hasMany(Reqrurment::class);
    }
    public function assignedClients(): HasMany
    {
        return $this->hasMany(Client::class);
    }
    public function assignedMeetingLogs(): HasMany
    {
        return $this->hasMany(MeetingLog::class);
    }
    public function latestWarningLog(): HasOne
    {
        return $this->hasOne(WarningLog::class, "userid")->currentMonth()->latestOfMany();
    }
}
