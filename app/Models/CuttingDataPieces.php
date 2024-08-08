<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CuttingDataPieces extends Model
{
    use HasFactory;
    protected $fillable = [
        'cutting_data_id',
        'type',
        'low_hight',
        'high_hight',
        'low_width',
        'high_width',
        'low_length',
        'high_length',
    ];

    public function CuttingData():BelongsTo
    {
        return $this->belongsTo(CuttingData::class, 'cutting_data_id');
    }
}
