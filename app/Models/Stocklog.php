<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
class Stocklog extends Model
{
    use HasFactory;
    protected $fillable = [
        'type',
        'status',
        'stocksid',
        'Note',
        'quantity',
        'source',
        'cost',
        'sales_crmsid',
        'supplyersid',
        'file'
    ];
    public function supplyer():BelongsTo
    {
        return $this->belongsTo(Supplyer::class, 'supplyersid');
    }

    /**
     * Get the sales CRM associated with the stock log.
     */
    public function salesCrm():BelongsTo
    {
        return $this->belongsTo(SalesCrm::class, 'sales_crmsid');
    }

    /**
     * Get the stock associated with the stock log.
     */
    public function stock():BelongsTo
    {
        return $this->belongsTo(Stock::class, 'stocksid');
    }
}
