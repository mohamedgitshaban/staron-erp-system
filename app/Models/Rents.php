<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Rents extends Model
{
    use HasFactory;
    protected $fillable = [
        'factory_id',
        'name',
        'status',
        'amount',
    ];

    /**
     * Get the factory associated with the subscription.
     */
    public function factory()
    {
        return $this->belongsTo(Factory::class);
    }
}
