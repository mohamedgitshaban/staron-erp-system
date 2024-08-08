<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class UserLog extends Model
{
    use HasFactory;
    protected $fillable = [
        'userid',
        'EmploymentDate',
        'EmploymentDateEnd',
        'Reason',
    ];
    protected $casts = [
        'EmploymentDate' => 'date',
        'EmploymentDateEnd' => 'date',
    ];

    /**
     * Get the user that owns the log.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'userid');
    }
}
