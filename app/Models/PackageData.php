<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PackageData extends Model
{
    use HasFactory;
    protected $fillable = [
        'sales_crms_id',
        'Packedgestatus',
        'Packedgestartdate',
        'Packedgeenddate',
        'Packedgedata',
    ];

    /**
     * Get the sales CRM associated with the package data.
     */
    public function salesCrm()
    {
        return $this->belongsTo(SalesCrm::class, 'sales_crms_id');
    }

}
