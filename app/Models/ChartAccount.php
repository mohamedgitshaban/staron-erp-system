<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ChartAccount extends Model
{
    use HasFactory;
    protected $fillable = ['name', 'code', 'parent_id',"debit","credit","balance","brance"];
    protected $hidden=["updated_at"];
    protected $casts=[
        // "created_at"=>"datetime:Y-m-d"
    ];
    public function parent()
    {
        return $this->belongsTo(ChartAccount::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(ChartAccount::class, 'parent_id');
    }
    public function childrenRecursive()
    {
        return $this->children()->with('childrenRecursive');
    }
    public function idRecursive()
    {
        return $this->children()->with('idRecursive')->select("id","name","parent_id");
    }
    public function debitAccount():HasMany
    {
        return $this->HasMany(ChartAccount::class, 'debit_id');
    }

    /**
     * Get the credit account associated with the journal entry.
     */
    public function creditAccount():HasMany
    {
        return $this->HasMany(ChartAccount::class, 'credit_id');
    }
    public function TresurydebitAccount():HasMany
    {
        return $this->HasMany(TresuryAccount::class, 'debit_id');
    }

    /**
     * Get the credit account associated with the journal entry.
     */
    public function TresurycreditAccount():HasMany
    {
        return $this->HasMany(TresuryAccount::class, 'credit_id');
    }
}
