<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ScPlanCost extends Model
{
    use HasFactory;
    protected $fillable = ['control_sc_plan_id'];

    public function controlScPlan():BelongsTo
    {
        return $this->belongsTo(ControlScPlan::class, 'control_sc_plan_id');
    }
}
