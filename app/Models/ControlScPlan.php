<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ControlScPlan extends Model
{
    use HasFactory;
        protected $fillable =
        ['Priority',
        'Deadline',
        'time',
        "source",
        'from',
        'status',
        'cost',
        'pricestate',
        'description',
        'rate',
        'finishdata',
        'financestart',
        ];

     public function ToLocation():HasMany
    {
        return $this->hasMany(ToLocation::class, 'control_sc_plan_id');
    }
    public function ScPlanCost():HasMany
    {
        return $this->hasMany(ScPlanCost::class);
    }
}
