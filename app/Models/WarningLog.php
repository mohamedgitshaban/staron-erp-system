<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WarningLog extends Model
{
    use HasFactory;
    protected $fillable = ['userid','level', 'date', 'text'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'userid')->select('id', 'name', 'profileimage');
    }
    public function scopeCurrentMonth($query)
    {
        return $query->whereYear('created_at', now()->year)
                    ->whereMonth('created_at', now()->month);
    }
}
