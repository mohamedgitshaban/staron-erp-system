<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MainJournalValidation extends Model
{
    use HasFactory;
    protected $fillable = [
        'invoice_group_id',
        'date',
        'debit_id',
        'debit_account_description',
        'credit_id',
        'credit_account_description',
        'value',
        'description',
        'invoice_id',
        'requested_by',
        'status',
        'rejection_reason'
    ];
}
