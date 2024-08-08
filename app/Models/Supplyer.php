<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Supplyer extends Model
{
    use HasFactory;
    protected $fillable = [
        'name', 'phone', 'company', 'location', 'source', 'additondata',
    ];
    public function stockLogs():HasMany
    {
        return $this->hasMany(StockLog::class, 'supplyersid');
    }
}
