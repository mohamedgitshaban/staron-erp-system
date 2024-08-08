<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MonthInvoice extends Model
{
    use HasFactory;
    protected $fillable = ['invoicein','month_id'];
    public function monthlyRecord():BelongsTo
    {
        return $this->belongsTo(MonthRecord::class,'month_id');
    }

}
