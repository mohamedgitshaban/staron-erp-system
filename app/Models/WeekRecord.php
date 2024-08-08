<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WeekRecord extends Model
{
    use HasFactory;
    public function operationControlPlans():HasMany
    {
        return $this->hasMany(OperationControlPlan::class);
    }

    public function controlOperationPlans():HasMany
    {
        return $this->hasMany(ControlOperationPlan::class);
    }
}
