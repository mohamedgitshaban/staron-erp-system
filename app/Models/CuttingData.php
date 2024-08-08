<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CuttingData extends Model
{
    use HasFactory;
    protected $fillable = [
        'name',
        'type',
        'model',
        'low_hight',
        'high_hight',
        'low_width',
        'high_width',
        'low_length',
        'high_length',
    ];
    public function CuttingDataPieces():HasMany
    {
        return $this->hasMany(CuttingDataPieces::class, 'cutting_data_id');
    }
    public function CuttingDataRequest():HasMany
    {
        return $this->hasMany(CuttingDataRequest::class, 'cutting_data_id');
    }
}
