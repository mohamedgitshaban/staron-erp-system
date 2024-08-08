<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IncentivePayroll extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id', 'Date', 'workdays', 'holidays', 'attendance',
        'PresenceMargin', 'kpi', 'performanceRate', 'performanceIncentive', 'TotalPay',
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = [
        'Date', 'created_at', 'updated_at',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
