<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class SalesCrm extends Model
{
    use HasFactory;
    protected $fillable = [
        'clients_id', 'location', 'tasbuilt', 'description', 'status', 'reason', 'grade', 'asignby',
        'overllsalingprice','overllgrossmargen'
    ];

    protected $dates = ['Qutationstartdate', 'Qutationenddate'];


    public function client():BelongsTo
    {
        return $this->belongsTo(Client::class, 'clients_id')->select("id","name","company");
    }
    public function assignedBy():BelongsTo
    {
        return $this->belongsTo(User::class, 'asignby')->select("id","name","profileimage","Supervisor");
    }
    public function technecalRequests():HasMany
    {
        return $this->hasMany(TechnecalRequest::class, 'sales_crms_id');
    }
    public function getLatesttechnecalRequests()
    {
        return $this->technecalRequests()->latest()->first();
    }
    public function qutations():HasMany
    {
        return $this->hasMany(Qutation::class, 'sales_crms_id');
    }
    public function getLatestQutationAttribute()
    {
        return $this->qutations()->latest()->first();
    }
    public function contracts():HasMany
    {
        return $this->hasMany(Contract::class, 'sales_crms_id');
    }
    public function projectPaymentLogs():HasMany
    {
        return $this->hasMany(ProjectPaymentLog::class);
    }
    public function stockLogs():HasMany
    {
        return $this->hasMany(StockLog::class, );
    }
    public function operationAsbuilts():HasMany
    {
        return $this->hasMany(OperationAsbuilt::class);
    }
    public function technecalPackages():HasMany
    {
        return $this->hasMany(TechnecalPackage::class, );
    }
    public function packageData():HasMany
    {
        return $this->hasMany(PackageData::class,);
    }
}
