<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LeavingBalanceLog extends Model
{
    use HasFactory;
    protected $fillable = ['userid','type' ,"count" ,'requestname', 'date', 'text', 'file'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'userid');
    }
}
