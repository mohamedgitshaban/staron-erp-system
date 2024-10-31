<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChartAccountValidation extends Model
{
    use HasFactory;
    protected $fillable = [
        'name',
        'parent_id',
        'requested_by',
        'hierarchy',
        'request_source',
        'rejection_reason',
        'status',
    ];

    // return the requester
    public function requestedBy () {
        return $this->belongsTo(User::class,'requested_by');
    }

    // return it's parent account
    public function parentAccount() {
        return $this->belongsTo(ChartAccount::class,'parent_id');
    }

    // check if approved
    public function isApproved() {
        return $this->status === "approved";
    }

    // approve the account creation
    public function approve() {
        $this->status = "approved";
        $this->save();
    }

    // reject the account creation
    public function reject($reason) {
        $this->status = "rejected";
        $this->rejection_reason = $reason;
        $this->save();
    }

}
