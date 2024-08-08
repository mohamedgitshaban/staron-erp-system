<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MeetingLog extends Model
{
    use HasFactory;
    protected $fillable=[
        'clients_id',
        'type',
        'status',
        "result",
        "nextactivity",
        "reason",
        "date",
        "time",
        'asignby'
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class, 'clients_id')->select('id', 'name', 'phone', 'company');
    }
    public function assignedBy()
    {
        return $this->belongsTo(User::class, 'asignby')->select('id', 'name', 'profileimage');
    }
}
