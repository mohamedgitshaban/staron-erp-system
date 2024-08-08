<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ToLocation extends Model
{
    use HasFactory;
    protected $fillable = [
        'control_sc_plan_id',
        'to_location',

    ];
    public function control_sc_plan_id():BelongsTo
    {
        return $this->belongsTo(ControlScPlan::class, 'control_sc_plan_id');
    }
}
