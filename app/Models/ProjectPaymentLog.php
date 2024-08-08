<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectPaymentLog extends Model
{
    use HasFactory;
    protected $fillable = [
        'sales_crms_id', 'paymentstatus', 'paymentdate', 'type',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'paymentdate' => 'date',
    ];
    public function salesCrm():BelongsTo
    {
        return $this->belongsTo(SalesCrm::class, 'sales_crms_id');
    }
}
