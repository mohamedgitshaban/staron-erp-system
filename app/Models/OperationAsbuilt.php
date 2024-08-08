<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OperationAsbuilt extends Model
{
    use HasFactory;
    protected $fillable = [
        'sales_crms_id', 'status', 'start', 'Actualstart', 'end', 'Actualend', 'data',
    ];

    protected $dates = ['start', 'Actualstart','end', 'Actualend'];

    public function salesCrm():BelongsTo
    {
        return $this->belongsTo(SalesCrm::class, 'sales_crms_id');
    }
}
