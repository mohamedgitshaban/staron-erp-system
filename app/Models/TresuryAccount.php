<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TresuryAccount extends Model
{
    use HasFactory;
    protected $fillable = [
        'debit_id',
        'debit_account_description',
        'credit_id',
        'credit_account_description',
        'description',
        'value',
        'collection_date',
        'collection_type',
        'check_collect',
        'status',
        "type"
    ];
    public function debitAccount():BelongsTo
    {
        return $this->belongsTo(ChartAccount::class, 'debit_id')->select('name', 'code', 'parent_id');
    }

    public function creditAccount():BelongsTo
    {
        return $this->belongsTo(ChartAccount::class, 'credit_id')->select('name', 'code', 'parent_id');
    }
}
