<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class Client extends Model
{
    use HasFactory;
    protected $fillable = [
        'name',
        'phone',
        'company',
        'Job_role',
        'email',
        'source',
        'type',
        'status',
        'asignby',
    ];

    public function MeetingLog():HasMany
    {
        return $this->hasMany(MeetingLog::class, 'clients_id');
    }
    public function assignBy():BelongsTo
    {
        return $this->belongsTo(User::class, 'asignby')->select('id', 'name', 'profileimage');
    }
    public function salesCrms():HasMany
    {
        return $this->hasMany(SalesCrm::class, 'clients_id');
    }
}
