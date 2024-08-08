<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class PackageApplecation extends Model
{
    use HasFactory;
    protected $fillable = [
        'technecal_packages_id', 'stockid', 'description', 'price', 'quantity',
    ];

    /**
     * Get the stock associated with the package application.
     */
    public function stock():BelongsTo
    {
        return $this->belongsTo(Stock::class, 'stockid');
    }

    /**
     * Get the technical package associated with the package application.
     */
    public function technecalPackage():BelongsTo
    {
        return $this->belongsTo(TechnecalPackage::class, 'technecal_packages_id');
    }
}
