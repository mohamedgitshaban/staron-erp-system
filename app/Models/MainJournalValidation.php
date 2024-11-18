<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MainJournalValidation extends Model
{
    use HasFactory;
    protected $fillable = [
        'date',
        'debit_id',
        'credit_id',
        'value',
        'description',
        'requested_by',
        'status',
        'ticket_trail',
        'originating_module',
        'rejection_reason',
        'transaction_type', // New field
        'debit_validation_id',
        'credit_validation_id',
        'request_source'
    ];
    public function debitAccount()
    {
        return $this->belongsTo(ChartAccount::class, 'debit_id');
    }

    public function creditAccount()
    {
        return $this->belongsTo(ChartAccount::class, 'credit_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

}
