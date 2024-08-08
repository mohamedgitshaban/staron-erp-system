<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Qutation extends Model
{
    use HasFactory;
    protected $fillable = [
        'sales_crms_id', 'Qutationstatus', 'Qutationstartdate', 'Qutationenddate', 'Qutationdata','ProjectGrossMargin','TotalProjectSellingPrice'
    ];
    protected $casts = [
        'Qutationstartdate' => 'date',
        'Qutationenddate' => 'date',
    ];

    /**
     * Get the sales CRM associated with the quotation.
     */
    public function salesCrm()
    {
        return $this->belongsTo(SalesCrm::class, 'sales_crms_id');
    }
}
