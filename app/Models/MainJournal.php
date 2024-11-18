<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MainJournal extends Model
{
    use HasFactory;
    protected $fillable = [
        'date',
        'debit_id',
        'credit_id',
        'value',
        'transaction_type',
        'description',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'date' => 'date:Y-m-d',
        'value' => 'integer',
    ];
    protected $hidden=[
        "created_at",
        "updated_at"
    ];
    /**
     * Get the debit account associated with the journal entry.
     */
    public function debitAccount():BelongsTo
    {
        return $this->belongsTo(ChartAccount::class, 'debit_id')->select("id","name","code");
    }

    /**
     * Get the credit account associated with the journal entry.
     */
    public function creditAccount():BelongsTo
    {
        return $this->belongsTo(ChartAccount::class, 'credit_id')->select("id","name","code");
    }
}
