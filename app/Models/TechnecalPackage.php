<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TechnecalPackage extends Model
{
    use HasFactory;

    protected $fillable = [
        'sales_crms_id', 'Packedgestatus',"totalcost", 'Packedgestartdate', 'Packedgeenddate', 'Packedgedata',
    ];

    /**
     * Get the sales CRM associated with the technical package.
     */
    public function salesCrm():BelongsTo
    {
        return $this->belongsTo(SalesCrm::class, 'sales_crms_id');
    }
    public function packageApplecations():HasMany
    {
        return $this->hasMany(PackageApplecation::class);
    }
}
