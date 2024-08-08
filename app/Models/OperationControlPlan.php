<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OperationControlPlan extends Model
{
    use HasFactory;
    protected $fillable = [
        'plan',
        'week_records_id',
    ];

    public function weekRecord():BelongsTo
    {
        return $this->belongsTo(WeekRecord::class);
    }
}
