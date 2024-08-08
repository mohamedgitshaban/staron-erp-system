<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class qcApplecationItem extends Model
{
    use HasFactory;
    protected $fillable = [
       'qc_applecations_id', 'stockid', 'description', 'price',"quantity"
    ];
    public function QcApplecation():BelongsTo
    {
        return $this->belongsTo(QcApplecation::class, 'qc_applecations_id');
    }
}
