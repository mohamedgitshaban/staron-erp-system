<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MonthRecord extends Model
{
    use HasFactory;
    public function monthInvoices():HasMany
    {
        return $this->hasMany(MonthInvoice::class);
    }

    public function operationMonthlyScPlans():HasMany
    {
        return $this->hasMany(OperationMonthlyScPlan::class);
    }

    public function operationActualInvoiceIns():HasMany
    {
        return $this->hasMany(OperationActualInvoiceIn::class);
    }

    public function financeActualCollections():HasMany
    {
        return $this->hasMany(FinanceActualCollection::class);
    }

    public function payrollSubmissions():HasMany
    {
        return $this->hasMany(PayrollSubmission::class);
    }

    public function incentivePayrollSubmissions():HasMany
    {
        return $this->hasMany(IncentivePayrollSubmission::class);
    }
}
