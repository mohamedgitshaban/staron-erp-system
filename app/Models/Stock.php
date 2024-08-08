<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Stock extends Model
{
    use HasFactory;
    protected $fillable = [
        'categoriesid', 'code', 'color', 'quantity', 'priceperunit', 'lastpriceforit', 'unit',
    ];

    /**
     * Get the category associated with the stock.
     */
    public function category():BelongsTo
    {
        return $this->belongsTo(Category::class, 'categoriesid');
    }
    public function stockLogs():HasMany
    {
        return $this->hasMany(StockLog::class);
    }

    public function qcApplecations():HasMany
    {
        return $this->hasMany(QcApplecation::class);
    }
    public function packageApplecations():HasMany
    {
        return $this->hasMany(PackageApplecation::class);
    }
}
