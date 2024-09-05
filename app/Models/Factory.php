<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Factory extends Model
{
    use HasFactory;
    protected $fillable = [
        'factory_name',
        'factory_type',
        'factory_location',
        'amount',
        'factory_contract_file',
        'start_date',
        'factory_status',
    ];
    public function Miscelleneous()
    {
        return $this->hasMany(Miscelleneous::class);
    }
    public function Rents()
    {
        return $this->hasMany(Rents::class);
    }
    public function Utilites()
    {
        return $this->hasMany(Utilites::class);
    }
    public function Supplies()
    {
        return $this->hasMany(Supplies::class);
    }
    public function Subscliption()
    {
        return $this->hasMany(Subscliption::class);
    }
}
