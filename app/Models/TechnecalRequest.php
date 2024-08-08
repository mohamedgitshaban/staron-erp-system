<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TechnecalRequest extends Model
{
    use HasFactory;
    protected $fillable = [
        'sales_crms_id', 'qcstatus', 'qcstartdate', 'qcenddate', 'totalprice', 'qcdata',"reason","asign_for_user"
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'qcstartdate' => 'date',
        'qcenddate' => 'date',
    ];

    /**
     * Get the sales CRM associated with the technical request.
     */
    public function User():BelongsTo
    {
        return $this->belongsTo(User::class, 'asign_for_user')->select('id', 'name', 'profileimage','Supervisor','department');
    }
    public function salesCrm():BelongsTo
    {
        return $this->belongsTo(SalesCrm::class, 'sales_crms_id')->select('id', 'location', 'tasbuilt',"description","clients_id");
    }
    public function qcApplecations():HasMany
    {
        return $this->hasMany(QcApplecation::class, 'technecal_requests_id');
    }
}
