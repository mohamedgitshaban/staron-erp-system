<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LeavingBalanceLog extends Model
{
    use HasFactory;
    protected $fillable = [
       'userid',
        'date',
        'text',
        'amount',
        'type',
    ];

    // Define the relationship with the User model
    public function user()
    {
        return $this->belongsTo(User::class, 'userid');
    }
}
