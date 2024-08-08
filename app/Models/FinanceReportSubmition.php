<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FinanceReportSubmition extends Model
{
    use HasFactory;
    protected $fillable = [
        'date',
        'report1',
        'report2',
        'report3',
        'report4',
        'report5',
    ];

    protected $casts = [
        'date' => 'date', // Ensures that the 'date' field is treated as a date
    ];
}
