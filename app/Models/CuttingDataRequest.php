<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CuttingDataRequest extends Model
{
    use HasFactory;
    protected $fillable = [
        'cutting_data_id',
        'hight',
        'width',
        'length',

    ];

    public function cuttingData():BelongsTo
    {
        return $this->belongsTo(CuttingData::class, 'cutting_data_id');
    }
}
