<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Contract extends Model
{
    use HasFactory;
    protected $fillable = [
        'sales_crms_id', 'contractstatus', 'contractstartdate', 'contractenddate', 'contractdata', 'contractValue',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'contractstartdate' => 'date',
        'contractenddate' => 'date',
    ];
    public function salesCrm()
    {
        return $this->belongsTo(SalesCrm::class, 'sales_crms_id');
    }
}
