<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Reqrurment extends Model
{
    use HasFactory;
    protected $fillable = [
        'title',
        'description',
        'hrstatus',
        'adminstatus',
        'hr_approve_data',
        'admin_approve_data',
        'status',
        'asignby',
        'start_work'
    ];
    protected $casts = [
        'hr_approve_data' => 'date',
        'admin_approve_data' => 'date',
    ];
    public function assignBy():BelongsTo
    {
        return $this->belongsTo(User::class, 'asignby');
    }
    public function interviews()
    {
        return $this->hasMany(Interview::class, 'reqrurmentsid');
    }
}
