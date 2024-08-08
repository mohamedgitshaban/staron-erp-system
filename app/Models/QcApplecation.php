<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class QcApplecation extends Model
{
    use HasFactory;
    protected $fillable = [
        'technecal_requests_id', "name","totalcost","grossmargen","salingprice"
    ];
    public function technecalRequest():BelongsTo
    {
        return $this->belongsTo(TechnecalRequest::class, 'technecal_requests_id');
    }
    public function qcApplecationItem():HasMany
    {
        return $this->hasMany(qcApplecationItem::class, 'qc_applecations_id');
    }
    /**
     * Get the stock associated with the QC application.
     */
    // public function stock():BelongsTo
    // {
    //     return $this->belongsTo(Stock::class, 'stockid');
    // }
}
